<?php
/**
 * Cleanup cron task.
 *
 * Runs daily to remove old guest conversations and logs based on the
 * retention settings configured in the admin panel.
 *
 * This task is lightweight and safe for shared hosting:
 * - Uses indexed queries for fast cleanup
 * - Only deletes data past the retention threshold
 * - Logs the number of deleted rows for audit
 *
 * @package KafiChatLite\Cron
 */

namespace KafiChatLite\Cron;

defined( 'ABSPATH' ) || exit;

use KafiChatLite\Database\ConversationRepository;
use KafiChatLite\Database\LogRepository;

/**
 * Class CleanupTask
 */
final class CleanupTask {

    /**
     * Cron hook name.
     *
     * @var string
     */
    public const HOOK = 'kafichat_daily_cleanup';

    /**
     * Register the cron hook callback.
     *
     * @return void
     */
    public static function register(): void {
        add_action( self::HOOK, array( self::class, 'run' ) );
    }

    /**
     * Schedule the daily cleanup task (if not already scheduled).
     *
     * @return void
     */
    public static function schedule(): void {
        if ( ! wp_next_scheduled( self::HOOK ) ) {
            wp_schedule_event( time(), 'daily', self::HOOK );
        }
    }

    /**
     * Unschedule the cleanup task.
     *
     * @return void
     */
    public static function unschedule(): void {
        $timestamp = wp_next_scheduled( self::HOOK );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, self::HOOK );
        }
    }

    /**
     * Run the cleanup task.
     *
     * @return void
     */
    public static function run(): void {
        $settings = get_option( 'kafichat_settings', array() );

        $guest_days = max( 1, (int) ( $settings['retention_guest_days'] ?? 7 ) );
        $logs_days  = max( 1, (int) ( $settings['retention_logs_days'] ?? 30 ) );

        // Delete orphan messages first (messages from old guest conversations).
        $deleted_messages = self::delete_orphan_messages( $guest_days );

        // Then delete old guest conversations.
        $deleted_conversations = ConversationRepository::delete_old_guest_conversations( $guest_days );

        // Delete old logs.
        $deleted_logs = LogRepository::cleanup( $logs_days );

        LogRepository::info(
            'Daily cleanup completed',
            'cron',
            array(
                'deleted_conversations' => $deleted_conversations,
                'deleted_messages'      => $deleted_messages,
                'deleted_logs'          => $deleted_logs,
                'retention_guest_days'  => $guest_days,
                'retention_logs_days'   => $logs_days,
            )
        );
    }

    /**
     * Delete messages from old guest conversations (orphan prevention).
     *
     * @param int $days Age threshold in days.
     * @return int Number of deleted messages.
     */
    private static function delete_orphan_messages( int $days ): int {
        global $wpdb;

        $threshold = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->query(
            $wpdb->prepare(
                "DELETE m FROM {$wpdb->prefix}kafichat_messages m
                 INNER JOIN {$wpdb->prefix}kafichat_conversations c ON m.conversation_id = c.id
                 WHERE c.user_id IS NULL AND c.created_at < %s",
                $threshold
            )
        );

        return (int) $result;
    }
}