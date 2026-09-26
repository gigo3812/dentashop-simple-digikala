<?php
/**
 * Polling cron task (webhook fallback).
 *
 * Polls the Bale Bot API for new updates using getUpdates(). This task is
 * only used when webhook is disabled or unhealthy. When webhook is working
 * properly, polling is disabled to reduce API calls.
 *
 * Security & performance:
 * - Only fetches 10 updates per run (lightweight)
 * - Stores last offset in options for continuity
 * - Skips execution if webhook is healthy
 * - Logs errors without exposing sensitive data
 *
 * @package KafiChatLite\Cron
 */

namespace KafiChatLite\Cron;

defined( 'ABSPATH' ) || exit;

use KafiChatLite\Chat\ReplyRouter;
use KafiChatLite\Database\LogRepository;
use KafiChatLite\Platform\BaleApiService;

/**
 * Class PollingTask
 */
final class PollingTask {

    /**
     * Cron hook name.
     *
     * @var string
     */
    public const HOOK = 'kafichat_bale_polling';

    /**
     * Option key for storing the last processed offset.
     *
     * @var string
     */
    private const OPTION_LAST_OFFSET = 'kafichat_last_poll_offset';

    /**
     * Number of updates to fetch per polling run.
     *
     * @var int
     */
    private const BATCH_SIZE = 10;

    /**
     * Register the cron hook callback.
     *
     * @return void
     */
    public static function register(): void {
        add_action( self::HOOK, array( self::class, 'run' ) );
    }

    /**
     * Register custom cron schedule (every 5 minutes).
     *
     * Should be hooked to `cron_schedules` filter.
     *
     * @param array $schedules Existing schedules.
     * @return array
     */
    public static function add_schedule( array $schedules ): array {
        if ( ! isset( $schedules['kafichat_every_5_minutes'] ) ) {
            $schedules['kafichat_every_5_minutes'] = array(
                'interval' => 5 * MINUTE_IN_SECONDS,
                'display'  => __( 'Every 5 minutes (KafiChat)', 'kafichat-lite' ),
            );
        }
        return $schedules;
    }

    /**
     * Schedule the polling task (if needed and not already scheduled).
     *
     * @return void
     */
    public static function schedule(): void {
        if ( ! self::should_poll() ) {
            self::unschedule();
            return;
        }

        if ( ! wp_next_scheduled( self::HOOK ) ) {
            wp_schedule_event( time(), 'kafichat_every_5_minutes', self::HOOK );
        }
    }

    /**
     * Unschedule the polling task.
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
     * Determine whether polling should be active.
     *
     * Polling is only needed when:
     * 1. Bot token is configured
     * 2. Polling is enabled in settings
     * 3. Webhook is disabled or unhealthy
     *
     * @return bool
     */
    public static function should_poll(): bool {
        $settings = get_option( 'kafichat_settings', array() );

        // No bot token = no polling.
        if ( empty( $settings['bot_token'] ) ) {
            return false;
        }

        // Polling explicitly disabled.
        if ( empty( $settings['polling_enabled'] ) ) {
            return false;
        }

        // If webhook is enabled and healthy, skip polling.
        if ( ! empty( $settings['webhook_enabled'] ) ) {
            $health = self::quick_webhook_check();
            if ( $health ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Run the polling task.
     *
     * @return void
     */
    public static function run(): void {
        // Re-check before running (schedule may be stale).
        if ( ! self::should_poll() ) {
            self::unschedule();
            return;
        }

        $last_offset = (int) get_option( self::OPTION_LAST_OFFSET, 0 );

        $result = BaleApiService::getUpdates( $last_offset, self::BATCH_SIZE );

        if ( ! $result || ! isset( $result['ok'] ) || ! $result['ok'] ) {
            LogRepository::warning(
                'Polling: Failed to fetch updates',
                'cron',
                array( 'last_offset' => $last_offset )
            );
            return;
        }

        $updates = $result['result'] ?? array();
        if ( empty( $updates ) ) {
            return;
        }

        $processed = 0;
        foreach ( $updates as $update ) {
            $update_id = (int) ( $update['update_id'] ?? 0 );

            // Route message if present.
            if ( isset( $update['message'] ) ) {
                ReplyRouter::route_message( $update );
                $processed++;
            }

            // Always advance the offset, even for non-message updates.
            if ( $update_id > $last_offset ) {
                $last_offset = $update_id;
            }
        }

        // Save the new offset (+1 to acknowledge processed updates).
        // Only save if we actually processed updates.
        if ( ! empty( $updates ) ) {
            update_option( self::OPTION_LAST_OFFSET, $last_offset + 1, false );
        }

        if ( $processed > 0 ) {
            LogRepository::info(
                'Polling: Processed updates',
                'cron',
                array(
                    'processed' => $processed,
                    'total'     => count( $updates ),
                )
            );
        }
    }

    /**
     * Quick webhook health check (cached for 5 minutes).
     *
     * Avoids calling the full health check on every polling run by using
     * a transient cache. Returns true if webhook is healthy.
     *
     * @return bool
     */
    private static function quick_webhook_check(): bool {
        $cached = get_transient( 'kafichat_webhook_healthy' );
        if ( false !== $cached ) {
            return (bool) $cached;
        }

        $info = BaleApiService::getWebhookInfo();
        $healthy = false;

        if ( $info && isset( $info['ok'] ) && $info['ok'] ) {
            $result  = $info['result'] ?? array();
            $healthy = ! empty( $result['url'] ) && empty( $result['last_error_message'] );
        }

        // Cache result for 5 minutes.
        set_transient( 'kafichat_webhook_healthy', $healthy ? 1 : 0, 5 * MINUTE_IN_SECONDS );

        return $healthy;
    }
}
