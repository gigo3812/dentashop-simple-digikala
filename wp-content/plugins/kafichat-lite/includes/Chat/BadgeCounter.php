<?php
/**
 * Badge counter for unread messages.
 *
 * Provides methods to count unseen admin messages for the chat widget badge.
 *
 * @package KafiChatLite\Chat
 */

namespace KafiChatLite\Chat;

defined( 'ABSPATH' ) || exit;

use KafiChatLite\Database\ConversationRepository;
use KafiChatLite\Database\MessageRepository;
use KafiChatLite\Security\GuestAuth;

/**
 * Class BadgeCounter
 */
final class BadgeCounter {

    /**
     * Get unread message count for a conversation.
     *
     * @param string      $conversation_ref Conversation reference.
     * @param string|null $access_token     Access token (for guests).
     * @return int Number of unread messages.
     */
    public static function get_unread_count( string $conversation_ref, ?string $access_token = null ): int {
        $conv = self::verify_ownership( $conversation_ref, $access_token );
        if ( ! $conv ) {
            return 0;
        }

        return MessageRepository::count_unseen( $conv->id, 'admin' );
    }

    /**
     * Mark all admin messages as seen.
     *
     * @param string      $conversation_ref Conversation reference.
     * @param string|null $access_token     Access token (for guests).
     * @return bool True on success.
     */
    public static function mark_all_seen( string $conversation_ref, ?string $access_token = null ): bool {
        $conv = self::verify_ownership( $conversation_ref, $access_token );
        if ( ! $conv ) {
            return false;
        }

        return MessageRepository::mark_admin_messages_as_seen( $conv->id );
    }

    /**
     * Verify conversation ownership.
     *
     * @param string      $conversation_ref Conversation reference.
     * @param string|null $access_token     Access token (for guests).
     * @return object|null Conversation object or null.
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
}