<?php
/**
 * Conversation manager.
 *
 * Handles the lifecycle of chat conversations: starting, sending messages,
 * retrieving history, and closing. Works for both guest users (via token)
 * and logged-in WordPress users.
 *
 * Security:
 * - Ownership verification via conversation_ref + access_token or guest cookie
 * - Rate limiting on message send
 * - Input validation and sanitization
 *
 * @package KafiChatLite\Chat
 */

namespace KafiChatLite\Chat;

defined( 'ABSPATH' ) || exit;

use KafiChatLite\Database\ConversationRepository;
use KafiChatLite\Database\MessageRepository;
use KafiChatLite\Database\LogRepository;
use KafiChatLite\Security\GuestAuth;
use KafiChatLite\Security\InputValidator;
use KafiChatLite\Security\RateLimiter;
use KafiChatLite\Platform\BaleApiService;

/**
 * Class ConversationManager
 */
final class ConversationManager {

    /**
     * Start or resume a conversation.
     *
     * For logged-in users: finds or creates a conversation linked to user_id.
     * For guests: finds or creates a conversation linked to guest_token_hash.
     *
     * @param array $data {
     *     @type string $name  Guest name (optional if logged in).
     *     @type string $phone Guest phone (optional if logged in).
     * }
     * @return array {
     *     @type string $conversation_ref Unique conversation reference.
     *     @type string $access_token     Access token (for guests).
     * }
     */
    public static function start_conversation( array $data ): array {
        // Logged-in user path.
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $conv = ConversationRepository::find_by_user_id( $user_id );

            if ( ! $conv || 'closed' === $conv->status ) {
                $conv_id = ConversationRepository::create( array(
                    'user_id'  => $user_id,
                    'platform' => 'bale',
                ) );
                
                // مقاوم‌سازی در برابر خطای دیتابیس
                if ( ! $conv_id ) {
                    return array(
                        'conversation_ref' => '',
                        'access_token'     => '',
                    );
                }
                
                $conv = ConversationRepository::find_by_id( $conv_id );

                // Send welcome message.
                if ( $conv ) {
                    self::send_welcome_message( $conv->id );
                }
            }

            return array(
                'conversation_ref' => $conv ? $conv->conversation_ref : '',
                'access_token'     => '', // Not needed for logged-in users.
            );
        }

        // Guest user path.
        $token = GuestAuth::get_current_token();
        if ( ! $token ) {
            $token = GuestAuth::issue_token();
        }

        $token_hash = GuestAuth::hash_token( $token );
        $conv = ConversationRepository::find_by_guest_token_hash( $token_hash );

        if ( ! $conv || 'closed' === $conv->status ) {
            // Validate and sanitize guest data.
            $name  = ! empty( $data['name'] ) ? InputValidator::sanitize_name( $data['name'] ) : '';
            $phone = ! empty( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '';

            // Validate phone if provided.
            if ( $phone && ! InputValidator::is_valid_iranian_mobile( $phone ) ) {
                $phone = ''; // Invalid phone, ignore.
            }

			$conv_id = ConversationRepository::create( array(
				'guest_token_hash' => $token_hash,
				'guest_name'       => $name,
				'guest_phone'      => $phone,
				'guest_ip'         => RateLimiter::get_client_ip(),
				'platform'         => 'bale',
			) );
			
			// مقاوم‌سازی در برابر خطای دیتابیس
			if ( ! $conv_id ) {
				return array(
					'conversation_ref' => '',
					'access_token'     => '',
				);
			}
			
			$conv = ConversationRepository::find_by_id( $conv_id );

            // Send welcome message.
            if ( $conv ) {
                self::send_welcome_message( $conv->id );
            }
        }

        return array(
            'conversation_ref' => $conv->conversation_ref,
            'access_token'     => $token,
        );
    }

    /**
     * Send a message from the user.
     *
     * @param string      $conversation_ref Conversation reference.
     * @param string      $content          Message content.
     * @param string|null $access_token     Access token (for guests).
     * @return array {
     *     @type bool   $success True if sent successfully.
     *     @type string $message Error message if failed.
     *     @type int    $message_id Message ID if successful.
     * }
     */
    public static function send_message( string $conversation_ref, string $content, ?string $access_token = null ): array {
        // Validate conversation ref.
        if ( ! InputValidator::is_valid_conversation_ref( $conversation_ref ) ) {
            return array( 'success' => false, 'message' => 'Invalid conversation reference' );
        }

        // Rate limiting.
        if ( ! RateLimiter::check_message_send() ) {
            return array( 'success' => false, 'message' => 'Rate limit exceeded. Please wait a moment.' );
        }

        // Sanitize content.
        $content = InputValidator::sanitize_message( $content );
        if ( empty( $content ) ) {
            return array( 'success' => false, 'message' => 'Message cannot be empty' );
        }

        // Find and verify ownership.
        $conv = self::verify_ownership( $conversation_ref, $access_token );
        if ( ! $conv ) {
            return array( 'success' => false, 'message' => 'Conversation not found or access denied' );
        }

        if ( 'closed' === $conv->status ) {
            return array( 'success' => false, 'message' => 'Conversation is closed' );
        }

        // Save message to database.
        $message_id = MessageRepository::create( array(
            'conversation_id' => $conv->id,
            'sender_type'     => 'user',
            'content'         => $content,
            'status'          => 'pending',
        ) );

        if ( ! $message_id ) {
            return array( 'success' => false, 'message' => 'Failed to save message' );
        }

        // Send to Bale.
        $admin_chat_id = BaleApiService::get_admin_chat_id();
        if ( $admin_chat_id ) {
            $bale_message = self::format_message_for_bale( $conv, $content );
            $result = BaleApiService::sendMessage( $admin_chat_id, $bale_message );
            
            // Log Bale response for debugging
            LogRepository::info(
                'SendMessage to Bale - Response',
                'api',
                array(
                    'admin_chat_id' => $admin_chat_id,
                    'response_ok'   => $result['ok'] ?? null,
                    'message_id'    => $result['result']['message_id'] ?? null,
                    'description'   => $result['description'] ?? null,
                )
            );
            
            if ( $result && isset( $result['ok'] ) && $result['ok'] && isset( $result['result']['message_id'] ) ) {
                // Update message with platform message ID.
                global $wpdb;
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->update(
                    $wpdb->prefix . 'kafichat_messages',
                    array(
                        'platform_message_id' => (string) $result['result']['message_id'],
                        'status'              => 'sent',
                    ),
                    array( 'id' => $message_id )
                );
            } else {
                MessageRepository::update_status( $message_id, 'failed' );
                LogRepository::warning(
                    'SendMessage to Bale failed or no message_id returned',
                    'api',
                    array(
                        'message_id_db' => $message_id,
                        'response'      => $result,
                    )
                );
            }
        } else {
            LogRepository::warning(
                'SendMessage: admin_chat_id not configured',
                'api'
            );
        }

        // Update last activity.
        ConversationRepository::update_last_activity( $conv->id );

        return array(
            'success'    => true,
            'message_id' => $message_id,
        );
    }

    /**
     * Get messages for a conversation.
     *
     * @param string      $conversation_ref Conversation reference.
     * @param string|null $access_token     Access token (for guests).
     * @param int         $limit            Number of messages.
     * @param int         $offset           Offset for pagination.
     * @return array Array of messages.
     */
    public static function get_messages( string $conversation_ref, ?string $access_token = null, int $limit = 50, int $offset = 0 ): array {
        $conv = self::verify_ownership( $conversation_ref, $access_token );
        if ( ! $conv ) {
            return array();
        }

        return MessageRepository::get_by_conversation( $conv->id, $limit, $offset );
    }

    /**
     * Mark all messages as seen.
     *
     * @param string      $conversation_ref Conversation reference.
     * @param string|null $access_token     Access token (for guests).
     * @return bool True on success.
     */
    public static function mark_as_seen( string $conversation_ref, ?string $access_token = null ): bool {
        $conv = self::verify_ownership( $conversation_ref, $access_token );
        if ( ! $conv ) {
            return false;
        }

        return MessageRepository::mark_admin_messages_as_seen( $conv->id );
    }

    /**
     * Close a conversation (admin action).
     *
     * @param int $conversation_id Conversation ID.
     * @return bool True on success.
     */
    public static function close_conversation( int $conversation_id ): bool {
        return ConversationRepository::close( $conversation_id );
    }

    /**
     * Verify conversation ownership.
     *
     * @param string      $conversation_ref Conversation reference.
     * @param string|null $access_token     Access token (for guests).
     * @return object|null Conversation object or null if not authorized.
     */
    private static function verify_ownership( string $conversation_ref, ?string $access_token = null ): ?object {
        $conv = ConversationRepository::find_by_ref( $conversation_ref );
        if ( ! $conv ) {
            return null;
        }

        // Logged-in user.
        if ( is_user_logged_in() ) {
            if ( $conv->user_id && (int) $conv->user_id === get_current_user_id() ) {
                return $conv;
            }
            return null;
        }

        // Guest user.
        $token = $access_token ?: GuestAuth::get_current_token();
        if ( ! $token || ! GuestAuth::is_valid_token_format( $token ) ) {
            return null;
        }

        $token_hash = GuestAuth::hash_token( $token );
        if ( $conv->guest_token_hash === $token_hash ) {
            return $conv;
        }

        return null;
    }

    /**
     * Send a welcome message to a new conversation.
     *
     * @param int $conversation_id Conversation ID.
     * @return void
     */
    private static function send_welcome_message( int $conversation_id ): void {
        $settings = get_option( 'kafichat_settings', array() );
        $welcome = $settings['welcome_message'] ?? 'سلام! چطور می‌تونم کمکتون کنم؟';

        MessageRepository::create( array(
            'conversation_id' => $conversation_id,
            'sender_type'     => 'system',
            'content'         => $welcome,
            'status'          => 'sent',
        ) );
    }

    /**
     * Format a message for sending to Bale admin.
     *
     * @param object $conv    Conversation object.
     * @param string $content Message content.
     * @return string Formatted message.
     */
    private static function format_message_for_bale( object $conv, string $content ): string {
        $sender = 'کاربر ناشناس';
        if ( $conv->guest_name ) {
            $sender = $conv->guest_name;
        } elseif ( $conv->user_id ) {
            $user = get_user_by( 'id', $conv->user_id );
            if ( $user ) {
                $sender = $user->display_name;
            }
        }

        $ref_tag = '[CONV:' . $conv->conversation_ref . ']';

        return sprintf(
            "👤 %s\n%s\n\n%s\n\n%s",
            $sender,
            $conv->guest_phone ? '📱 ' . $conv->guest_phone : '',
            $content,
            $ref_tag
        );
    }
}