<?php
/**
 * Repository for the messages table.
 *
 * Handles all CRUD operations for chat messages.
 *
 * Security:
 * - All queries use $wpdb->prepare() to prevent SQL injection
 * - Message content is sanitized but not encrypted (stored as plain text)
 *
 * @package KafiChatLite\Database
 */
namespace KafiChatLite\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Class MessageRepository
 */
final class MessageRepository {

    /**
     * Get the table name with WordPress prefix.
     *
     * @return string
     */
    private static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'kafichat_messages';
    }

    /**
     * Create a new message.
     *
     * @param array $data {
     *     @type int    $conversation_id      Conversation ID.
     *     @type string $sender_type          Sender type (user/admin/system).
     *     @type string $sender_id            Sender ID (optional).
     *     @type string $platform_message_id  Platform message ID (optional).
     *     @type string $platform             Platform name (default: 'bale').
     *     @type string $content              Message content.
     *     @type string $message_type         Message type (default: 'text').
     *     @type string $status               Message status (default: 'pending').
     * }
     * @return int|null Message ID on success, null on failure.
     */
    public static function create( array $data ): ?int {
        global $wpdb;

        $insert_data = array(
            'conversation_id' => (int) $data['conversation_id'],
            'sender_type'     => sanitize_text_field( $data['sender_type'] ),
            'platform'        => $data['platform'] ?? 'bale',
            'content'         => sanitize_textarea_field( $data['content'] ?? '' ),
            'message_type'    => $data['message_type'] ?? 'text',
            'status'          => $data['status'] ?? 'pending',
        );

        if ( ! empty( $data['sender_id'] ) ) {
            $insert_data['sender_id'] = sanitize_text_field( $data['sender_id'] );
        }

        if ( ! empty( $data['platform_message_id'] ) ) {
            $insert_data['platform_message_id'] = sanitize_text_field( $data['platform_message_id'] );
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $result = $wpdb->insert( self::table(), $insert_data );

        if ( false === $result ) {
            return null;
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * Find a message by ID.
     *
     * @param int $id Message ID.
     * @return object|null Message object or null if not found.
     */
    public static function find_by_id( int $id ): ?object {
        global $wpdb;

        $table = esc_sql( self::table() );
        $sql = "SELECT * FROM {$table} WHERE id = %d";
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return $wpdb->get_row( $wpdb->prepare( $sql, $id ) );
    }

    /**
     * Find a message by platform message ID.
     *
     * Used for reply routing when receiving messages from Bale.
     *
     * @param string $platform_id Platform message ID.
     * @return object|null Message object or null if not found.
     */
    public static function find_by_platform_message_id( string $platform_id ): ?object {
        global $wpdb;

        $table = esc_sql( self::table() );
        $sql = "SELECT * FROM {$table} WHERE platform_message_id = %s";
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return $wpdb->get_row( $wpdb->prepare( $sql, $platform_id ) );
    }

    /**
     * Get messages for a conversation.
     *
     * @param int $conversation_id Conversation ID.
     * @param int $limit           Number of messages (default: 50).
     * @param int $offset          Offset for pagination (default: 0).
     * @return array Array of message objects.
     */
    public static function get_by_conversation( int $conversation_id, int $limit = 50, int $offset = 0 ): array {
        global $wpdb;

        $table = esc_sql( self::table() );
        $sql = "SELECT * FROM {$table} WHERE conversation_id = %d ORDER BY created_at ASC LIMIT %d OFFSET %d";
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return $wpdb->get_results( $wpdb->prepare( $sql, $conversation_id, $limit, $offset ) );
    }

    /**
     * Update message status.
     *
     * @param int    $id     Message ID.
     * @param string $status New status (pending/sent/failed).
     * @return bool True on success, false on failure.
     */
    public static function update_status( int $id, string $status ): bool {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->update(
            self::table(),
            array( 'status' => sanitize_text_field( $status ) ),
            array( 'id' => $id )
        );

        return false !== $result;
    }

    /**
     * Mark a message as seen.
     *
     * @param int $id Message ID.
     * @return bool True on success, false on failure.
     */
    public static function mark_as_seen( int $id ): bool {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->update(
            self::table(),
            array(
                'is_seen' => 1,
                'seen_at' => current_time( 'mysql', true ),
            ),
            array( 'id' => $id )
        );

        return false !== $result;
    }

    /**
     * Count unseen messages in a conversation.
     *
     * @param int    $conversation_id Conversation ID.
     * @param string $sender_type     Filter by sender type (default: 'admin').
     * @return int Number of unseen messages.
     */
    public static function count_unseen( int $conversation_id, string $sender_type = 'admin' ): int {
        global $wpdb;

        $table = esc_sql( self::table() );
        $sql = "SELECT COUNT(*) FROM {$table} WHERE conversation_id = %d AND sender_type = %s AND is_seen = 0";
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $count = $wpdb->get_var( $wpdb->prepare( $sql, $conversation_id, $sender_type ) );

        return (int) $count;
    }

    /**
     * Delete all messages for a conversation.
     *
     * @param int $conversation_id Conversation ID.
     * @return bool True on success, false on failure.
     */
    public static function delete_by_conversation( int $conversation_id ): bool {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->delete(
            self::table(),
            array( 'conversation_id' => $conversation_id )
        );

        return false !== $result;
    }

	/**
     * Mark all admin messages in a conversation as seen.
     *
     * @param int $conversation_id Conversation ID.
     * @return bool True on success, false on failure.
     */
    public static function mark_admin_messages_as_seen( int $conversation_id ): bool {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}kafichat_messages 
                 SET is_seen = 1, seen_at = %s 
                 WHERE conversation_id = %d AND sender_type = 'admin' AND is_seen = 0",
                current_time( 'mysql', true ),
                $conversation_id
            )
        );

        return false !== $result;
    }

    public static function update_platform_message_id( int $id, string $platform_id ): bool {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->update(
            self::table(),
            array( 'platform_message_id' => sanitize_text_field( $platform_id ) ),
            array( 'id' => $id )
        );
        return false !== $result;
    }
}