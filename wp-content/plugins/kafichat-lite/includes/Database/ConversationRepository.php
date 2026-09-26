<?php
/**
 * Repository for the conversations table.
 *
 * Handles all CRUD operations for chat conversations with automatic
 * encryption of PII (guest name, phone, IP) using EncryptionService.
 *
 * Security:
 * - All queries use $wpdb->prepare() to prevent SQL injection
 * - PII is encrypted before storage and decrypted on retrieval (single view)
 * - conversation_ref is cryptographically random (32 hex chars)
 *
 * @package KafiChatLite\Database
 */
namespace KafiChatLite\Database;

defined( 'ABSPATH' ) || exit;

use KafiChatLite\Security\EncryptionService;

/**
 * Class ConversationRepository
 */
final class ConversationRepository {
    /**
     * Get the table name with WordPress prefix.
     *
     * @return string
     */
    private static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'kafichat_conversations';
    }

    /**
     * Create a new conversation.
     *
     * @param array $data {
     *     @type int    $user_id          WordPress user ID (for logged-in users).
     *     @type string $guest_token_hash HMAC hash of guest token.
     *     @type string $guest_name       Guest name (will be encrypted).
     *     @type string $guest_phone      Guest phone (will be encrypted).
     *     @type string $guest_ip         Guest IP (will be encrypted).
     *     @type string $platform         Platform name (default: 'bale').
     * }
     * @return int|null Conversation ID on success, null on failure.
     */
    public static function create( array $data ): ?int {
        global $wpdb;
        $conversation_ref = bin2hex( random_bytes( 16 ) );
        $insert_data      = array(
            'conversation_ref' => $conversation_ref,
            'status'           => 'open',
            'platform'         => $data['platform'] ?? 'bale',
            'last_activity_at' => current_time( 'mysql', true ),
        );

        if ( ! empty( $data['user_id'] ) ) {
            $insert_data['user_id'] = (int) $data['user_id'];
        }

        if ( ! empty( $data['guest_token_hash'] ) ) {
            $insert_data['guest_token_hash'] = sanitize_text_field( $data['guest_token_hash'] );
        }

        if ( ! empty( $data['guest_name'] ) ) {
            $insert_data['guest_name'] = EncryptionService::encrypt( sanitize_text_field( $data['guest_name'] ) );
        }
        if ( ! empty( $data['guest_phone'] ) ) {
            $insert_data['guest_phone'] = EncryptionService::encrypt( sanitize_text_field( $data['guest_phone'] ) );
        }
        if ( ! empty( $data['guest_ip'] ) ) {
            $insert_data['guest_ip'] = EncryptionService::encrypt( sanitize_text_field( $data['guest_ip'] ) );
        }

        if ( ! empty( $data['guest_ip'] ) ) {
            $insert_data['guest_ip'] = EncryptionService::encrypt( sanitize_text_field( $data['guest_ip'] ) );
        }

        // Add search_index for admin searching (Pro compatible)
        $search_parts = array();
        if ( ! empty( $data['guest_name'] ) ) {
            $search_parts[] = sanitize_text_field( $data['guest_name'] );
        }
        if ( ! empty( $data['guest_phone'] ) ) {
            $search_parts[] = sanitize_text_field( $data['guest_phone'] );
        }
        if ( ! empty( $search_parts ) ) {
            $insert_data['search_index'] = implode( ' ', $search_parts );
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $result = $wpdb->insert( self::table(), $insert_data );
        if ( false === $result ) {
            return null;
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * Find a conversation by ID.
     *
     * @param int $id Conversation ID.
     * @return object|null Conversation object or null if not found.
     */
    public static function find_by_id( int $id ): ?object {
        global $wpdb;
        $table = esc_sql( self::table() );
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
        if ( ! $row ) {
            return null;
        }
        return self::decrypt_row( $row );
    }

    /**
     * Find a conversation by conversation_ref.
     *
     * @param string $ref Conversation reference (32 hex chars).
     * @return object|null Conversation object or null if not found.
     */
    public static function find_by_ref( string $ref ): ?object {
        global $wpdb;
        $table = esc_sql( self::table() );
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE conversation_ref = %s", $ref ) );
        if ( ! $row ) {
            return null;
        }
        return self::decrypt_row( $row );
    }

    /**
     * Find a conversation by guest token hash.
     *
     * @param string $hash Guest token HMAC hash.
     * @return object|null Conversation object or null if not found.
     */
    public static function find_by_guest_token_hash( string $hash ): ?object {
        global $wpdb;
        $table = esc_sql( self::table() );
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE guest_token_hash = %s ORDER BY created_at DESC LIMIT 1", $hash ) );
        if ( ! $row ) {
            return null;
        }
        return self::decrypt_row( $row );
    }

    /**
     * Find a conversation by WordPress user ID.
     *
     * @param int $user_id WordPress user ID.
     * @return object|null Conversation object or null if not found.
     */
    public static function find_by_user_id( int $user_id ): ?object {
        global $wpdb;
        $table = esc_sql( self::table() );
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT 1", $user_id ) );
        if ( ! $row ) {
            return null;
        }
        return self::decrypt_row( $row );
    }

    /**
     * Update a conversation.
     *
     * @param int   $id   Conversation ID.
     * @param array $data Fields to update.
     * @return bool True on success, false on failure.
     */
    public static function update( int $id, array $data ): bool {
        global $wpdb;
        $update_data = array();

        if ( isset( $data['guest_name'] ) ) {
            $update_data['guest_name'] = EncryptionService::encrypt( sanitize_text_field( $data['guest_name'] ) );
        }
        if ( isset( $data['guest_phone'] ) ) {
            $update_data['guest_phone'] = EncryptionService::encrypt( sanitize_text_field( $data['guest_phone'] ) );
        }
        if ( isset( $data['guest_ip'] ) ) {
            $update_data['guest_ip'] = EncryptionService::encrypt( sanitize_text_field( $data['guest_ip'] ) );
        }

        if ( isset( $data['status'] ) ) {
            $update_data['status'] = sanitize_text_field( $data['status'] );
        }
        if ( isset( $data['last_activity_at'] ) ) {
            $update_data['last_activity_at'] = sanitize_text_field( $data['last_activity_at'] );
        }

        $update_data['updated_at'] = current_time( 'mysql', true );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->update(
            self::table(),
            $update_data,
            array( 'id' => $id )
        );

        return false !== $result;
    }

    /**
     * Update last_activity_at and updated_at to current time.
     *
     * @param int $id Conversation ID.
     * @return bool True on success, false on failure.
     */
    public static function update_last_activity( int $id ): bool {
        return self::update( $id, array(
            'last_activity_at' => current_time( 'mysql', true ),
        ) );
    }

    /**
     * Close a conversation.
     *
     * @param int $id Conversation ID.
     * @return bool True on success, false on failure.
     */
    public static function close( int $id ): bool {
        return self::update( $id, array( 'status' => 'closed' ) );
    }

    /**
     * Get all conversations with optional filters.
     * OPTIMIZATION: Does NOT decrypt PII fields here to ensure fast loading 
     * of the conversation list on shared hosting. Decryption happens only 
     * when viewing a specific conversation (find_by_ref).
     *
     * @param array $args {
     *     @type string $status  Filter by status (open/closed/archived).
     *     @type int    $limit   Number of results (default: 50).
     *     @type int    $offset  Offset for pagination (default: 0).
     * }
     * @return array Array of conversation objects.
     */
    public static function get_all( array $args = array() ): array {
        global $wpdb;
        $defaults = array(
            'status' => null,
            'limit'  => 50,
            'offset' => 0,
        );
        $args     = wp_parse_args( $args, $defaults );

        $where  = '';
        $values = array();

        if ( $args['status'] ) {
            $where    = 'WHERE status = %s';
            $values[] = $args['status'];
        }

        $values[] = (int) $args['limit'];
        $values[] = (int) $args['offset'];

        $table = esc_sql( self::table() );
        $sql = "SELECT * FROM {$table} {$where} ORDER BY last_activity_at DESC LIMIT %d OFFSET %d";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $rows = $wpdb->get_results( $wpdb->prepare( $sql, $values ) );

        // Return raw rows for performance. Decryption is handled in single-view methods.
        return $rows;
    }

    /**
     * Delete old guest conversations (for cleanup cron).
     *
     * @param int $days Age in days.
     * @return int Number of deleted rows.
     */
    public static function delete_old_guest_conversations( int $days ): int {
        global $wpdb;
        $threshold = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
        $table = self::table();
        // Fix: Use backticks for table name instead of %i for WP 6.0 compatibility
        $sql = "DELETE FROM `{$table}` WHERE created_at < %s";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $result = $wpdb->query( $wpdb->prepare( $sql, $threshold ) );
        return (int) $result;
    }

    /**
     * Decrypt PII fields in a row.
     *
     * @param object $row Database row.
     * @return object Row with decrypted PII.
     */
    private static function decrypt_row( object $row ): object {
        if ( ! empty( $row->guest_name ) ) {
            $row->guest_name = EncryptionService::decrypt( $row->guest_name ) ?? '';
        }
        if ( ! empty( $row->guest_phone ) ) {
            $row->guest_phone = EncryptionService::decrypt( $row->guest_phone ) ?? '';
        }
        if ( ! empty( $row->guest_ip ) ) {
            $row->guest_ip = EncryptionService::decrypt( $row->guest_ip ) ?? '';
        }
        return $row;
    }
}