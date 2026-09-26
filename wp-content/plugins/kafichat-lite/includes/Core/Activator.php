<?php
/**
 * Plugin activation routine.
 *
 * Runs once when the plugin is activated. Responsible for environment
 * validation, default options, schema version tracking and the initial
 * webhook secret. Heavy lifting (table creation) happens in later phases.
 *
 * @package KafiChatLite\Core
 */

namespace KafiChatLite\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Activator
 */
final class Activator {

    /**
     * Option key: current schema version (filled by the Database\Schema phase).
     */
    public const OPTION_SCHEMA_VERSION = 'kafichat_schema_version';

    /**
     * Option key: plugin version (installed).
     */
    public const OPTION_PLUGIN_VERSION = 'kafichat_version';

    /**
     * Option key: plugin settings.
     */
    public const OPTION_SETTINGS = 'kafichat_settings';

    /**
     * Option key: webhook secret (64 hex chars).
     */
    public const OPTION_WEBHOOK_SECRET = 'kafichat_webhook_secret';

    /**
     * Option key: whether libsodium is available (detected on activation).
     */
    public const OPTION_HAS_SODIUM = 'kafichat_has_sodium';

    /**
     * Activation entry point.
     *
     * @return void
     */
    public static function activate(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        // Defense-in-depth: re-check environment even though the main file
        // already guards loading. This prevents accidental manual triggers.
        if ( version_compare( PHP_VERSION, KAFICHAT_LITE_MIN_PHP, '<' ) ) {
            self::abort(
                sprintf(
                    'KafiChat Lite requires PHP %1$s or higher (current: %2$s).',
                    KAFICHAT_LITE_MIN_PHP,
                    PHP_VERSION
                )
            );
        }

        global $wp_version;
        if ( isset( $wp_version ) && version_compare( $wp_version, KAFICHAT_LITE_MIN_WP, '<' ) ) {
            self::abort(
                sprintf(
                    'KafiChat Lite requires WordPress %1$s or higher (current: %2$s).',
                    KAFICHAT_LITE_MIN_WP,
                    $wp_version
                )
            );
        }

        self::detect_crypto_extensions();
        self::ensure_defaults();
        self::ensure_webhook_secret();
        self::ensure_schema_version();

        // Run database migrations (creates tables if needed).
        \KafiChatLite\Database\Schema::migrate();

        // Schedule cron tasks.
        \KafiChatLite\Cron\CleanupTask::schedule();
        \KafiChatLite\Cron\PollingTask::schedule();

        update_option( self::OPTION_PLUGIN_VERSION, KAFICHAT_LITE_VERSION, false );
    }

    /**
     * Detect and persist availability of libsodium (preferred) or OpenSSL.
     *
     * Result is stored in an option so later phases and the admin panel can
     * display the status without re-checking on every request.
     *
     * @return void
     */
    private static function detect_crypto_extensions(): void {
        $has_sodium = extension_loaded( 'sodium' );
        update_option( self::OPTION_HAS_SODIUM, $has_sodium ? 1 : 0, false );
    }

    /**
     * Seed default plugin settings if they do not exist yet.
     *
     * Existing settings are preserved on re-activation / upgrade so that
     * users never lose their configuration.
     *
     * @return void
     */
    private static function ensure_defaults(): void {
        $existing = get_option( self::OPTION_SETTINGS );
        if ( is_array( $existing ) && ! empty( $existing ) ) {
            return;
        }

        $defaults = array(
            'enabled'                    => false,
            'position'                   => 'bottom-right',
            'bot_token'                  => '',
            'admin_chat_id'              => '',
            'webhook_enabled'            => false,
            'polling_enabled'            => true,
            'retention_guest_days'       => 7,
            'retention_logs_days'        => 30,
            'primary_color'              => '#5B6CE7',
            'header_title'               => 'پشتیبانی آنلاین',
            'welcome_message'            => 'سلام! چطور می‌تونم کمکتون کنم؟',
            'tooltip_text'               => 'پشتیبانی آنلاین',
            'guest_name_required'        => true,
            'guest_phone_required'       => true,
            'badge_polling_interval_sec' => 30,
            'full_polling_interval_sec'  => 5,
            'debug_mode'                 => false,
        );

        update_option( self::OPTION_SETTINGS, $defaults, false );
    }

    /**
     * Generate and persist a cryptographically random webhook secret.
     *
     * Generated only once. The secret survives upgrades and re-activation.
     *
     * @return void
     */
    private static function ensure_webhook_secret(): void {
        $existing = get_option( self::OPTION_WEBHOOK_SECRET );
        if ( is_string( $existing ) && 64 === strlen( $existing ) ) {
            return;
        }

        $secret = bin2hex( random_bytes( 32 ) );
        update_option( self::OPTION_WEBHOOK_SECRET, $secret, false );
    }

    /**
     * Seed schema version if not present. The actual tables are created by
     * the Database\Schema phase; this just reserves the option key.
     *
     * @return void
     */
    private static function ensure_schema_version(): void {
        if ( false === get_option( self::OPTION_SCHEMA_VERSION ) ) {
            update_option( self::OPTION_SCHEMA_VERSION, '0', false );
        }
    }

    /**
     * Abort activation with a human-readable message.
     *
     * Uses deactivate_plugins() so the plugin ends up in the "inactive" list
     * instead of causing a broken install.
     *
     * @param string $message Plain text error message.
     * @return void
     */
    private static function abort( string $message ): void {
        if ( ! function_exists( 'deactivate_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        deactivate_plugins( KAFICHAT_LITE_BASENAME );
        wp_die(
            esc_html( $message ),
            esc_html__( 'KafiChat Lite activation failed', 'kafichat-lite' ),
            array(
                'back_link' => true,
                'response'  => 200,
            )
        );
    }
}
