<?php
/**
 * Database schema manager.
 *
 * Responsible for creating and migrating the plugin's custom tables using
 * WordPress' dbDelta() function. All tables use the WordPress table prefix
 * and utf8mb4 charset for full Unicode support (including Persian/Arabic).
 *
 * Schema versioning ensures safe upgrades: when a new version adds or
 * modifies columns, migrate() detects the version mismatch and applies
 * the necessary changes without data loss.
 *
 * @package KafiChatLite\Database
 */

namespace KafiChatLite\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Class Schema
 */
final class Schema {

    /**
     * Current schema version.
     *
     * Increment this when adding/modifying tables or columns. The migrate()
     * method compares this against the stored version in wp_options and
     * runs the necessary migrations.
     *
     * Version history:
     * - 1: Initial schema (4 tables: conversations, messages, attachments, logs)
     *
     * @var string
     */
    public const VERSION = '2';

    /**
     * Option key for storing the installed schema version.
     *
     * @var string
     */
    public const OPTION_VERSION = 'kafichat_schema_version';

    /**
     * Run migrations if needed.
     *
     * Safe to call multiple times: if the schema is already up-to-date,
     * this method returns immediately without doing any work.
     *
     * @return void
     */
    public static function migrate(): void {
        $installed_version = get_option( self::OPTION_VERSION, '0' );

        if ( version_compare( $installed_version, self::VERSION, '>=' ) ) {
            return;
        }

        if ( ! function_exists( 'dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        // Run migrations in order.
        self::create_conversations_table();
        self::create_messages_table();
        self::create_attachments_table();
        self::create_logs_table();

        // Migration v2: Add search_index column for Pro compatibility
        if ( version_compare( $installed_version, '2', '<' ) ) {
            self::add_search_index_column();
        }

        update_option( self::OPTION_VERSION, self::VERSION, false );
    }

    /**
     * Drop all plugin tables.
     *
     * Used by the uninstaller. This is a destructive operation and should
     * only be called when the user explicitly deletes the plugin.
     *
     * @return void
     */
    public static function drop_all(): void {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'kafichat_conversations',
            $wpdb->prefix . 'kafichat_messages',
            $wpdb->prefix . 'kafichat_attachments',
            $wpdb->prefix . 'kafichat_logs',
        );

        foreach ( $tables as $table ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
            $wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
        }

        delete_option( self::OPTION_VERSION );
    }

    /**
     * Create the conversations table.
     *
     * Stores one row per chat session. Supports both logged-in users (via
     * user_id) and guests (via guest_token_hash). Pro-compatible fields
     * (locked_by_admin_id, rating, etc.) are NULL in Lite.
     *
     * @return void
     */
    private static function create_conversations_table(): void {
        global $wpdb;

        $table   = $wpdb->prefix . 'kafichat_conversations';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            conversation_ref VARCHAR(32) NOT NULL,
            user_id BIGINT UNSIGNED DEFAULT NULL,
            guest_token_hash VARCHAR(64) DEFAULT NULL,
            guest_name TEXT DEFAULT NULL,
            guest_phone TEXT DEFAULT NULL,
            guest_ip TEXT DEFAULT NULL,
            search_index TEXT DEFAULT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'open',
            platform VARCHAR(20) NOT NULL DEFAULT 'bale',
            locked_by_admin_id VARCHAR(50) DEFAULT NULL,
            locked_at DATETIME DEFAULT NULL,
            last_activity_at DATETIME DEFAULT NULL,
            rating TINYINT UNSIGNED DEFAULT NULL,
            rating_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY conversation_ref (conversation_ref),
            KEY user_id (user_id),
            KEY guest_token_hash (guest_token_hash),
            KEY status (status),
            KEY last_activity_at (last_activity_at),
            FULLTEXT KEY search_index (search_index)
        ) {$charset};";

        dbDelta( $sql );
    }

    /**
     * Create the messages table.
     *
     * Stores individual chat messages. Each message belongs to a conversation
     * and has a sender_type (user/admin/system). The platform_message_id is
     * used to track messages sent to/from Bale for reply routing.
     *
     * @return void
     */
    private static function create_messages_table(): void {
        global $wpdb;

        $table   = $wpdb->prefix . 'kafichat_messages';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            conversation_id BIGINT UNSIGNED NOT NULL,
            sender_type VARCHAR(10) NOT NULL,
            sender_id VARCHAR(50) DEFAULT NULL,
            platform_message_id VARCHAR(100) DEFAULT NULL,
            platform VARCHAR(20) NOT NULL DEFAULT 'bale',
            content TEXT DEFAULT NULL,
            message_type VARCHAR(20) NOT NULL DEFAULT 'text',
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            is_seen TINYINT(1) NOT NULL DEFAULT 0,
            seen_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY conversation_id (conversation_id),
            KEY platform_message_id (platform_message_id),
            KEY is_seen (is_seen),
            KEY created_at (created_at)
        ) {$charset};";

        dbDelta( $sql );
    }

    /**
     * Create the attachments table.
     *
     * In Lite edition, this table is created for Pro compatibility but no
     * data is written to it. File upload features are Pro-only.
     *
     * @return void
     */
    private static function create_attachments_table(): void {
        global $wpdb;

        $table   = $wpdb->prefix . 'kafichat_attachments';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            message_id BIGINT UNSIGNED NOT NULL,
            conversation_id BIGINT UNSIGNED NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
            mime_type VARCHAR(100) DEFAULT NULL,
            sha256_hash VARCHAR(64) DEFAULT NULL,
            expires_at DATETIME DEFAULT NULL,
            is_deleted TINYINT(1) NOT NULL DEFAULT 0,
            deleted_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY message_id (message_id),
            KEY conversation_id (conversation_id),
            KEY expires_at (expires_at),
            KEY is_deleted (is_deleted)
        ) {$charset};";

        dbDelta( $sql );
    }

    /**
     * Create the logs table.
     *
     * Stores plugin logs (info/warning/error) for debugging and monitoring.
     * Logs are automatically cleaned up by the daily cron job based on the
     * retention setting (default: 30 days).
     *
     * @return void
     */
    private static function create_logs_table(): void {
        global $wpdb;

        $table   = $wpdb->prefix . 'kafichat_logs';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            level VARCHAR(20) NOT NULL,
            context VARCHAR(50) DEFAULT NULL,
            message TEXT NOT NULL,
            data LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY level (level),
            KEY context (context),
            KEY created_at (created_at)
        ) {$charset};";

        dbDelta( $sql );
    }

	    /**
     * Migration v2: Add search_index column to conversations table.
     * Ensures compatibility with Pro edition schema.
     */
    private static function add_search_index_column(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'kafichat_conversations';

        $column_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'search_index'",
                DB_NAME,
                $table
            )
        );

        if ( ! $column_exists ) {
            $wpdb->query( "ALTER TABLE {$table} ADD COLUMN search_index TEXT DEFAULT NULL AFTER guest_ip" );
            $wpdb->query( "ALTER TABLE {$table} ADD FULLTEXT KEY search_index (search_index)" );
        }
    }
}