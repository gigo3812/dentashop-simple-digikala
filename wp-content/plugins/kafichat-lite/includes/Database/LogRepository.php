<?php
/**
 * Repository for the logs table.
 *
 * Handles logging and log retrieval. Used for debugging and monitoring.
 *
 * Security:
 * - All queries use $wpdb->prepare() to prevent SQL injection
 * - Sensitive data should not be logged (admin responsibility)
 *
 * @package KafiChatLite\Database
 */

namespace KafiChatLite\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Class LogRepository
 */
final class LogRepository {

    /**
     * Get the table name with WordPress prefix.
     *
     * @return string
     */
    private static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'kafichat_logs';
    }

    /**
     * Write a log entry.
     *
     * @param string      $level   Log level (info/warning/error).
     * @param string      $message Log message.
     * @param string|null $context Context (webhook/api/cron/security).
     * @param array|null  $data    Additional data (will be JSON-encoded).
     * @return bool True on success, false on failure.
     */
    public static function log( string $level, string $message, ?string $context = null, ?array $data = null ): bool {
        global $wpdb;

        $insert_data = array(
            'level'   => sanitize_text_field( $level ),
            'message' => sanitize_textarea_field( $message ),
        );

        if ( $context ) {
            $insert_data['context'] = sanitize_text_field( $context );
        }

        if ( $data ) {
            $insert_data['data'] = wp_json_encode( $data );
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $result = $wpdb->insert( self::table(), $insert_data );

        return false !== $result;
    }

    /**
     * Log an info message.
     *
     * @param string      $message Log message.
     * @param string|null $context Context.
     * @param array|null  $data    Additional data.
     * @return bool
     */
    public static function info( string $message, ?string $context = null, ?array $data = null ): bool {
        return self::log( 'info', $message, $context, $data );
    }

    /**
     * Log a warning message.
     *
     * @param string      $message Log message.
     * @param string|null $context Context.
     * @param array|null  $data    Additional data.
     * @return bool
     */
    public static function warning( string $message, ?string $context = null, ?array $data = null ): bool {
        return self::log( 'warning', $message, $context, $data );
    }

    /**
     * Log an error message.
     *
     * @param string      $message Log message.
     * @param string|null $context Context.
     * @param array|null  $data    Additional data.
     * @return bool
     */
    public static function error( string $message, ?string $context = null, ?array $data = null ): bool {
        return self::log( 'error', $message, $context, $data );
    }

    /**
     * Get all logs with optional filters.
     *
     * @param array $args {
     *     @type string $level   Filter by level (info/warning/error).
     *     @type string $context Filter by context.
     *     @type int    $limit   Number of results (default: 100).
     *     @type int    $offset  Offset for pagination (default: 0).
     * }
     * @return array Array of log objects.
     */
    public static function get_all( array $args = array() ): array {
        global $wpdb;

        $defaults = array(
            'level'   => null,
            'context' => null,
            'limit'   => 100,
            'offset'  => 0,
        );
        $args = wp_parse_args( $args, $defaults );

        $where_clauses = array();
        $values = array();

        if ( $args['level'] ) {
            $where_clauses[] = 'level = %s';
            $values[] = $args['level'];
        }

        if ( $args['context'] ) {
            $where_clauses[] = 'context = %s';
            $values[] = $args['context'];
        }

        $where = '';
        if ( ! empty( $where_clauses ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_clauses );
        }

        $values[] = (int) $args['limit'];
        $values[] = (int) $args['offset'];

        $table = self::table();
        // Fix: Use backticks for table name instead of %i for WP 6.0 compatibility
        $sql = "SELECT * FROM `{$table}` {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_results( $wpdb->prepare( $sql, $values ) );
    }

    /**
     * Delete old logs (for cleanup cron).
     *
     * @param int $days Age in days.
     * @return int Number of deleted rows.
     */
    public static function cleanup( int $days ): int {
        global $wpdb;

        $threshold = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
        $table = self::table();
        $sql = "DELETE FROM %i WHERE created_at < %s";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $result = $wpdb->query( $wpdb->prepare( $sql, $table, $threshold ) );

        return (int) $result;
    }
}