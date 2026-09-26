<?php
/**
 * Plugin Name: KafiChat Lite
 * Plugin URI: https://chat.kafgram.com
 * Description: افزونه رایگان چت و پشتیبانی زنده وردپرس متصل به پیام‌رسان بله.
 * Version: 1.0.1
 * Author: Kafgram
 * Author URI: https://kafgram.com
 * Text Domain: kafichat-lite
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPLv2 or later
 */

defined( 'ABSPATH' ) || exit;


/*
 * -------------------------------------------------------------------------
 * Constants
 * -------------------------------------------------------------------------
 */

if ( ! defined( 'KAFICHAT_LITE_VERSION' ) ) {
    define( 'KAFICHAT_LITE_VERSION', '1.0.1' );
}

if ( ! defined( 'KAFICHAT_LITE_FILE' ) ) {
    define( 'KAFICHAT_LITE_FILE', __FILE__ );
}

if ( ! defined( 'KAFICHAT_LITE_PATH' ) ) {
    define( 'KAFICHAT_LITE_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'KAFICHAT_LITE_URL' ) ) {
    define( 'KAFICHAT_LITE_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'KAFICHAT_LITE_BASENAME' ) ) {
    define( 'KAFICHAT_LITE_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'KAFICHAT_LITE_PREFIX' ) ) {
    define( 'KAFICHAT_LITE_PREFIX', 'kafichat_' );
}

if ( ! defined( 'KAFICHAT_LITE_EDITION' ) ) {
    define( 'KAFICHAT_LITE_EDITION', 'lite' );
}

if ( ! defined( 'KAFICHAT_LITE_PRO_URL' ) ) {
    define( 'KAFICHAT_LITE_PRO_URL', 'https://chat.kafgram.com' );
}

if ( ! defined( 'KAFICHAT_LITE_MIN_PHP' ) ) {
    define( 'KAFICHAT_LITE_MIN_PHP', '7.4' );
}

if ( ! defined( 'KAFICHAT_LITE_MIN_WP' ) ) {
    define( 'KAFICHAT_LITE_MIN_WP', '6.0' );
}

if ( ! defined( 'KAFICHAT_LITE_TEXT_DOMAIN' ) ) {
    define( 'KAFICHAT_LITE_TEXT_DOMAIN', 'kafichat-lite' );
}


/*
 * -------------------------------------------------------------------------
 * Activation / Deactivation Hooks
 * -------------------------------------------------------------------------
 */

register_activation_hook(
    KAFICHAT_LITE_FILE,
    'kafichat_lite_on_activate'
);

register_deactivation_hook(
    KAFICHAT_LITE_FILE,
    'kafichat_lite_on_deactivate'
);


/**
 * Activation callback.
 *
 * @return void
 */
function kafichat_lite_on_activate(): void {

    if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }

    $errors = array();

    /*
     * PHP version check.
     */
    if ( version_compare( PHP_VERSION, KAFICHAT_LITE_MIN_PHP, '<' ) ) {

        $errors[] = sprintf(
            /* translators: 1: required PHP version, 2: current PHP version. */
            __(
                'KafiChat Lite requires PHP %1$s or higher. You are running PHP %2$s.',
                'kafichat-lite'
            ),
            KAFICHAT_LITE_MIN_PHP,
            PHP_VERSION
        );
    }

    /*
     * WordPress version check.
     */
    global $wp_version;

    if (
        isset( $wp_version ) &&
        version_compare( $wp_version, KAFICHAT_LITE_MIN_WP, '<' )
    ) {

        $errors[] = sprintf(
            /* translators: 1: required WP version, 2: current WP version. */
            __(
                'KafiChat Lite requires WordPress %1$s or higher. You are running WordPress %2$s.',
                'kafichat-lite'
            ),
            KAFICHAT_LITE_MIN_WP,
            $wp_version
        );
    }

    /*
     * Environment is not supported.
     */
    if ( ! empty( $errors ) ) {

        if ( ! function_exists( 'deactivate_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        deactivate_plugins( KAFICHAT_LITE_BASENAME );

        $html = '<p><strong>'
            . esc_html__(
                'KafiChat Lite activation failed.',
                'kafichat-lite'
            )
            . '</strong></p><ul>';

        foreach ( $errors as $error ) {

            $html .= '<li>'
                . esc_html( $error )
                . '</li>';
        }

        $html .= '</ul>';

        wp_die(
            $html,
            esc_html__(
                'Plugin Activation Error',
                'kafichat-lite'
            ),
            array(
                'back_link' => true,
                'response'  => 200,
            )
        );
    }

    /*
     * Environment OK.
     */
    require_once KAFICHAT_LITE_PATH . 'includes/Core/Autoloader.php';

    KafiChatLite\Core\Autoloader::register();

    KafiChatLite\Core\Activator::activate();
}


/**
 * Deactivation callback.
 *
 * @return void
 */
function kafichat_lite_on_deactivate(): void {

    if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }

    $autoloader = KAFICHAT_LITE_PATH . 'includes/Core/Autoloader.php';

    /*
     * Corrupt/missing installation.
     */
    if ( ! is_readable( $autoloader ) ) {
        return;
    }

    require_once $autoloader;

    KafiChatLite\Core\Autoloader::register();

    KafiChatLite\Core\Deactivator::deactivate();
}


/*
 * -------------------------------------------------------------------------
 * Fast Environment Check
 * -------------------------------------------------------------------------
 */

$kafichat_lite_env_ok   = true;
$kafichat_lite_env_msgs = array();


/*
 * PHP check.
 */
if ( version_compare( PHP_VERSION, KAFICHAT_LITE_MIN_PHP, '<' ) ) {

    $kafichat_lite_env_ok = false;

    $kafichat_lite_env_msgs[] = sprintf(
        /* translators: 1: required PHP version, 2: current PHP version. */
        __(
            'KafiChat Lite requires PHP %1$s or higher. You are running PHP %2$s.',
            'kafichat-lite'
        ),
        KAFICHAT_LITE_MIN_PHP,
        PHP_VERSION
    );
}


/*
 * WordPress check.
 */
global $wp_version;

if (
    isset( $wp_version ) &&
    version_compare( $wp_version, KAFICHAT_LITE_MIN_WP, '<' )
) {

    $kafichat_lite_env_ok = false;

    $kafichat_lite_env_msgs[] = sprintf(
        /* translators: 1: required WP version, 2: current WP version. */
        __(
            'KafiChat Lite requires WordPress %1$s or higher. You are running WordPress %2$s.',
            'kafichat-lite'
        ),
        KAFICHAT_LITE_MIN_WP,
        $wp_version
    );
}


/*
 * Unsupported environment.
 */
if ( ! $kafichat_lite_env_ok ) {

    add_action(
        'admin_notices',
        static function () use ( $kafichat_lite_env_msgs ) {

            echo '<div class="notice notice-error"><p><strong>'
                . esc_html__(
                    'KafiChat Lite:',
                    'kafichat-lite'
                )
                . '</strong></p><ul>';

            foreach ( $kafichat_lite_env_msgs as $msg ) {

                echo '<li>'
                    . esc_html( $msg )
                    . '</li>';
            }

            echo '</ul></div>';
        }
    );

    return;
}


/*
 * -------------------------------------------------------------------------
 * Autoloader & Runtime Bootstrap
 * -------------------------------------------------------------------------
 */

require_once KAFICHAT_LITE_PATH . 'includes/Core/Autoloader.php';

KafiChatLite\Core\Autoloader::register();


/*
 * -------------------------------------------------------------------------
 * Bootstrap on plugins_loaded
 * -------------------------------------------------------------------------
 */

add_action(
    'plugins_loaded',
    static function () {

        /*
         * Database migration.
         */
        if (
            class_exists(
                KafiChatLite\Database\Schema::class
            )
        ) {

            KafiChatLite\Database\Schema::migrate();
        }


        /*
         * Cleanup cron.
         */
        if (
            class_exists(
                KafiChatLite\Cron\CleanupTask::class
            )
        ) {

            KafiChatLite\Cron\CleanupTask::register();
        }


        /*
         * Polling cron.
         */
        if (
            class_exists(
                KafiChatLite\Cron\PollingTask::class
            )
        ) {

            KafiChatLite\Cron\PollingTask::register();
        }


        /*
         * Admin panel.
         */
        if (
            is_admin() &&
            class_exists(
                KafiChatLite\Admin\AdminPanel::class
            )
        ) {

            KafiChatLite\Admin\AdminPanel::register();
        }
    },
    1
);


/*
 * -------------------------------------------------------------------------
 * Custom Cron Schedules
 * -------------------------------------------------------------------------
 */

add_filter(
    'cron_schedules',
    static function ( $schedules ) {

        if (
            class_exists(
                KafiChatLite\Cron\PollingTask::class
            )
        ) {

            return KafiChatLite\Cron\PollingTask::add_schedule(
                $schedules
            );
        }

        return $schedules;
    }
);


/*
 * -------------------------------------------------------------------------
 * REST API Routes
 * -------------------------------------------------------------------------
 */

add_action(
    'rest_api_init',
    static function () {

        /*
         * Webhook.
         */
        if (
            class_exists(
                KafiChatLite\Platform\WebhookHandler::class
            )
        ) {

            KafiChatLite\Platform\WebhookHandler::register_routes();
        }


        /*
         * REST API.
         */
        if (
            class_exists(
                KafiChatLite\Api\RestApiController::class
            )
        ) {

            KafiChatLite\Api\RestApiController::register_routes();
        }
    }
);


/*
 * -------------------------------------------------------------------------
 * Frontend Asset Loading
 *
 * IMPORTANT:
 * KafiChat widget is loaded ONLY on the site's front page.
 *
 * This means:
 *
 * Home:
 *   - Widget CSS       ✓
 *   - Widget JS        ✓
 *   - Widget HTML      ✓
 *
 * Other pages:
 *   - Widget CSS       ✗
 *   - Widget JS        ✗
 *   - Widget HTML      ✗
 *   - Extra font       ✗
 *
 * The plugin continues to operate normally in the backend,
 * REST API and cron processes.
 * -------------------------------------------------------------------------
 */

add_action(
    'wp_enqueue_scripts',
    static function () {

        /*
         * -------------------------------------------------------------
         * Only load the frontend widget on the homepage.
         * -------------------------------------------------------------
         *
         * is_front_page() works correctly whether the homepage is:
         * - a static WordPress page
         * - the posts page
         *
         * Other frontend pages return immediately.
         */
        if ( ! is_front_page() ) {
            return;
        }


        /*
         * -------------------------------------------------------------
         * Get plugin settings.
         * -------------------------------------------------------------
         */

        $settings = get_option(
            'kafichat_settings',
            array()
        );


        /*
         * -------------------------------------------------------------
         * Widget disabled.
         * -------------------------------------------------------------
         */

        if ( empty( $settings['enabled'] ) ) {
            return;
        }


        /*
         * -------------------------------------------------------------
         * Widget CSS.
         * -------------------------------------------------------------
         *
         * NOTE:
         * Vazirmatn CDN font has intentionally been removed.
         *
         * DentaShop already uses Dana.
         * Therefore KafiChat does not need to load another font.
         * -------------------------------------------------------------
         */

        wp_enqueue_style(
            'kafichat-widget',
            KAFICHAT_LITE_URL . 'assets/css/kafichat-widget.css',
            array(),
            KAFICHAT_LITE_VERSION
        );


        /*
         * -------------------------------------------------------------
         * Widget JavaScript.
         * -------------------------------------------------------------
         *
         * Loaded in footer to avoid blocking initial rendering.
         * No jQuery dependency.
         * -------------------------------------------------------------
         */

        wp_enqueue_script(
            'kafichat-widget',
            KAFICHAT_LITE_URL . 'assets/js/kafichat-widget.js',
            array(),
            KAFICHAT_LITE_VERSION,
            true
        );


        /*
         * -------------------------------------------------------------
         * Pass settings to JavaScript.
         * -------------------------------------------------------------
         */

        wp_localize_script(
            'kafichat-widget',
            'kafichatWidget',
            array(

                'apiUrl' => esc_url_raw(
                    rest_url( 'kafichat/v1/' )
                ),

                'nonce' => wp_create_nonce(
                    'wp_rest'
                ),

                'isLoggedIn' => is_user_logged_in(),

                'position' => esc_attr(
                    $settings['position'] ?? 'bottom-right'
                ),

                'brandColor' => esc_attr(
                    $settings['primary_color'] ?? '#00B894'
                ),

                'headerTitle' => esc_html(
                    $settings['header_title'] ?? 'پشتیبانی آنلاین'
                ),

                'tooltipText' => esc_html(
                    $settings['tooltip_text'] ?? 'پشتیبانی آنلاین'
                ),

                'welcomeMessage' => esc_html(
                    $settings['welcome_message']
                    ?? 'سلام! چطور می‌تونم کمکتون کنم؟'
                ),

                'proUrl' => esc_url(
                    KAFICHAT_LITE_PRO_URL
                ),

                'guestNameReq' => ! empty(
                    $settings['guest_name_required']
                ),

                'guestPhoneReq' => ! empty(
                    $settings['guest_phone_required']
                ),

                'badgeInterval' => (int) (
                    $settings['badge_polling_interval_sec']
                    ?? 30
                ),

                'fullInterval' => (int) (
                    $settings['full_polling_interval_sec']
                    ?? 5
                ),
            )
        );


        /*
         * -------------------------------------------------------------
         * Widget HTML.
         * -------------------------------------------------------------
         *
         * Added only when the widget is actually enabled and
         * the current page is the homepage.
         * -------------------------------------------------------------
         */

        add_action(
            'wp_footer',
            static function () {

                $template = KAFICHAT_LITE_PATH
                    . 'templates/widget.php';

                if ( file_exists( $template ) ) {
                    include $template;
                }
            },
            99
        );
    }
);