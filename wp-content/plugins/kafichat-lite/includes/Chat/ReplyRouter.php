<?php
/**
 * Reply router for incoming Bale messages.
 *
 * Routes admin replies from Bale to the correct conversation based on:
 * 1. Reply-to message ID (if admin replied to a specific message)
 * 2. Conversation reference tag [CONV:xxxx] in the message text
 * 3. Fallback: log as unrouted if neither method works
 *
 * @package KafiChatLite\Chat
 */

namespace KafiChatLite\Chat;

defined( 'ABSPATH' ) || exit;

use KafiChatLite\Database\ConversationRepository;
use KafiChatLite\Database\MessageRepository;
use KafiChatLite\Database\LogRepository;

/**
 * Class ReplyRouter
 */
final class ReplyRouter {

    /**
     * Route an incoming message from Bale.
     *
     * @param array $bale_message The message object from Bale webhook/polling.
     * @return bool True if routed successfully, false otherwise.
     */
    public static function route_message( array $bale_message ): bool {
        // Extract message details.
        $message = $bale_message['message'] ?? $bale_message;
        
        $text = $message['text'] ?? '';
        $chat_id = $message['chat']['id'] ?? null;
        $message_id = $message['message_id'] ?? null;
        $reply_to = $message['reply_to_message'] ?? null;

        if ( empty( $text ) || ! $chat_id ) {
            LogRepository::warning( 'ReplyRouter: Invalid message format', 'webhook', $bale_message );
            return false;
        }

        // Strategy 1: Reply-to message.
        if ( $reply_to && isset( $reply_to['message_id'] ) ) {
            $conv = self::find_by_reply_message_id( (string) $reply_to['message_id'] );
            if ( $conv ) {
                return self::save_admin_message( $conv, $text, $message_id );
            }
        }

        // Strategy 2: Conversation reference tag.
        $conv_ref = self::extract_conversation_ref( $text );
        if ( $conv_ref ) {
            $conv = ConversationRepository::find_by_ref( $conv_ref );
            if ( $conv ) {
                // Remove the tag from the message content.
                $clean_text = self::remove_conversation_tag( $text );
                return self::save_admin_message( $conv, $clean_text, $message_id );
            }
        }

        // Fallback: unrouted.
        LogRepository::warning(
            'ReplyRouter: Message could not be routed',
            'webhook',
            array(
                'chat_id'    => $chat_id,
                'message_id' => $message_id,
                'text'       => substr( $text, 0, 100 ),
            )
        );

        return false;
    }

    /**
     * Find conversation by reply-to message ID.
     *
     * @param string $platform_message_id The Bale message ID being replied to.
     * @return object|null Conversation object or null.
     */
    private static function find_by_reply_message_id( string $platform_message_id ): ?object {
        $msg = MessageRepository::find_by_platform_message_id( $platform_message_id );
        if ( ! $msg ) {
            return null;
        }

        return ConversationRepository::find_by_id( (int) $msg->conversation_id );
    }

    /**
     * Extract conversation reference from message text.
     *
     * Looks for pattern: [CONV:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx]
     *
     * @param string $text Message text.
     * @return string|null Conversation reference or null.
     */
    private static function extract_conversation_ref( string $text ): ?string {
        if ( preg_match( '/\[CONV:([a-f0-9]{32})\]/', $text, $matches ) ) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Remove conversation tag from message text.
     *
     * @param string $text Message text.
     * @return string Cleaned text.
     */
    private static function remove_conversation_tag( string $text ): string {
        return trim( preg_replace( '/\[CONV:[a-f0-9]{32}\]/', '', $text ) );
    }

    /**
     * Save an admin message to the database.
     *
     * @param object $conv       Conversation object.
     * @param string $text       Message text.
     * @param int    $message_id Bale message ID.
     * @return bool True on success.
     */
    private static function save_admin_message( object $conv, string $text, ?int $message_id ): bool {
        $msg_id = MessageRepository::create( array(
            'conversation_id'      => $conv->id,
            'sender_type'          => 'admin',
            'content'              => sanitize_textarea_field( $text ),
            'platform_message_id'  => $message_id ? (string) $message_id : null,
            'status'               => 'sent',
            'is_seen'              => 0,
        ) );

        if ( ! $msg_id ) {
            return false;
        }

        // Update last activity.
        ConversationRepository::update_last_activity( $conv->id );

        LogRepository::info(
            'ReplyRouter: Message routed successfully',
            'webhook',
            array(
                'conversation_id' => $conv->id,
                'message_id'      => $msg_id,
            )
        );

        return true;
    }
}
