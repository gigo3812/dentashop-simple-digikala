<?php
/**
 * Uninstaller facade.
 *
 * The real uninstall logic lives in the top-level `uninstall.php` because
 * WordPress executes that file directly. This class exists so that other
 * components (tests, future admin "reset data" tool, Pro edition) can
 * invoke the same cleanup programmatically without duplicating code.
 *
 * @package KafiChatLite\Core
 */

namespace KafiChatLite\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Uninstaller
 */
final class Uninstaller {

    /**
     * Option keys owned by the Lite edition.
     *
     * @var string[]
     */
    private const OPTIONS = array(
        'kafichat_version',
        'kafichat_schema_version',
        'kafichat_settings',
        'kafichat_webhook_secret',
        'kafichat_has_sodium',
        'kafichat_webhook_health',
    );

    /**
     * Cron hook names owned by the plugin.
     *
     * @var string[]
     */
    private const CRON_HOOKS = array(
        'kafichat_daily_cleanup',
        'kafichat_bale_polling',
    );

    /**
     * Tables owned by the plugin (suffix only; prefixed at runtime).
     *
     * @var string[]
     */
    private const TABLES = array(
        'conversations',
        'messages',
        'attachments',
        'logs',
    );

    /**
     * Run full cleanup for the current site.
     *
     * Intended to be called only from `uninstall.php` or from an admin
     * action that has already verified capability and intent.
     *
     * @return void
     */
    public static function run(): void {
        self::delete_options();
        self::delete_transients();
        self::clear_scheduled_events();
        self::drop_tables();
    }

    /**
     * @return void
     */
    private static function delete_options(): void {
        foreach ( self::OPTIONS as $opt ) {
            delete_option( $opt );
        }
    }

    /**
     * @return void
     */
    private static function delete_transients(): void {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '\\_transient\\_kafichat\\_%'
                OR option_name LIKE '\\_transient\\_timeout\\_kafichat\\_%'"
        );
    }

    /**
     * @return void
     */
    private static function clear_scheduled_events(): void {
        foreach ( self::CRON_HOOKS as $hook ) {
            wp_clear_scheduled_hook( $hook );
        }
    }

    /**
     * @return void
     */
    private static function drop_tables(): void {
        global $wpdb;
        $base = $wpdb->prefix . 'kafichat_';
        foreach ( self::TABLES as $table ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            $wpdb->query( "DROP TABLE IF EXISTS `{$base}{$table}`" );
        }
    }
}