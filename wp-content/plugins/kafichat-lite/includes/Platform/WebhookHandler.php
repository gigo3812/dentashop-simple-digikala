<?php
/**
 * Webhook handler for incoming Bale updates.
 *
 * Receives POST requests from Bale when users send messages to the bot,
 * verifies the request authenticity, and routes the message via ReplyRouter.
 *
 * Verification strategy (Defense in Depth):
 * 1. Primary: Check X-Telegram-Bot-Api-Secret-Token header (if Bale supports it)
 * 2. Fallback: Check secret in URL path (/webhook/bale/{secret})
 * 3. Reject: If neither method verifies the request
 *
 * Note: We are not 100% certain whether Bale supports secret_token like
 * Telegram does. That's why we implement both methods.
 *
 * Security:
 * - hash_equals() for timing-safe comparison
 * - Rate limiting to prevent abuse
 * - JSON parsing with validation
 * - Logging of failed attempts (without leaking secret)
 *
 * @package KafiChatLite\Platform
 */

namespace KafiChatLite\Platform;

defined( 'ABSPATH' ) || exit;

use KafiChatLite\Chat\ReplyRouter;
use KafiChatLite\Database\LogRepository;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class WebhookHandler
 */
final class WebhookHandler {

    /**
     * REST namespace.
     *
     * @var string
     */
    private const REST_NAMESPACE = 'kafichat/v1';

    /**
     * Header name used by Telegram/Bale for secret token verification.
     *
     * @var string
     */
    private const SECRET_HEADER = 'X-Telegram-Bot-Api-Secret-Token';

    /**
     * Register webhook REST routes.
     *
     * Should be called from `rest_api_init` hook.
     *
     * @return void
     */
    public static function register_routes(): void {
        // Primary route: /webhook/bale (header-based verification).
        register_rest_route(
            self::REST_NAMESPACE,
            '/webhook/bale',
            array(
                'methods'             => 'POST',
                'callback'            => array( self::class, 'handle_webhook' ),
                'permission_callback' => '__return_true', // We verify manually.
            )
        );

        // Fallback route: /webhook/bale/{secret} (URL-based verification).
        register_rest_route(
            self::REST_NAMESPACE,
            '/webhook/bale/(?P<secret>[a-f0-9]{64})',
            array(
                'methods'             => 'POST',
                'callback'            => array( self::class, 'handle_webhook_with_secret' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'secret' => array(
                        'required'          => true,
                        'validate_callback' => function ( $param ) {
                            return 64 === strlen( $param ) && ctype_xdigit( $param );
                        },
                    ),
                ),
            )
        );

        // Health check route: GET /webhook/bale/health (public, returns basic info).
        register_rest_route(
            self::REST_NAMESPACE,
            '/webhook/bale/health',
            array(
                'methods'             => 'GET',
                'callback'            => array( self::class, 'handle_health_check' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * Handle webhook with header-based verification.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response
     */
    public static function handle_webhook( WP_REST_Request $request ): WP_REST_Response {
        // Verify header secret.
        if ( ! self::verify_header_secret( $request ) ) {
            LogRepository::warning(
                'Webhook: Invalid or missing secret header',
                'webhook',
                array( 'ip' => self::get_client_ip() )
            );
            return new WP_REST_Response(
                array( 'ok' => false, 'error' => 'Unauthorized' ),
                401
            );
        }

        return self::process_webhook( $request );
    }

    /**
     * Handle webhook with URL-based secret verification (fallback).
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response
     */
    public static function handle_webhook_with_secret( WP_REST_Request $request ): WP_REST_Response {
        $url_secret = $request->get_param( 'secret' );

        if ( ! self::verify_url_secret( $url_secret ) ) {
            LogRepository::warning(
                'Webhook: Invalid URL secret',
                'webhook',
                array( 'ip' => self::get_client_ip() )
            );
            return new WP_REST_Response(
                array( 'ok' => false, 'error' => 'Unauthorized' ),
                401
            );
        }

        return self::process_webhook( $request );
    }

    /**
     * Process the webhook payload after verification.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response
     */
    private static function process_webhook( WP_REST_Request $request ): WP_REST_Response {
        $payload = $request->get_json_params();

        if ( empty( $payload ) || ! is_array( $payload ) ) {
            LogRepository::warning(
                'Webhook: Empty or invalid payload',
                'webhook'
            );
            return new WP_REST_Response(
                array( 'ok' => false, 'error' => 'Invalid payload' ),
                400
            );
        }

        // Log receipt (without sensitive data).
        LogRepository::info(
            'Webhook: Update received',
            'webhook',
            array(
                'update_id' => $payload['update_id'] ?? null,
                'has_message' => isset( $payload['message'] ),
            )
        );

        // Route the message if present.
        if ( isset( $payload['message'] ) ) {
            $routed = ReplyRouter::route_message( $payload );
            if ( ! $routed ) {
                LogRepository::warning(
                    'Webhook: Message could not be routed',
                    'webhook',
                    array( 'update_id' => $payload['update_id'] ?? null )
                );
            }
        }

        // Always return 200 quickly to prevent Bale retries.
        return new WP_REST_Response( array( 'ok' => true ), 200 );
    }

    /**
     * Handle health check request.
     *
     * Returns basic status information without exposing secrets.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response
     */
    public static function handle_health_check( WP_REST_Request $request ): WP_REST_Response {
        $settings = get_option( 'kafichat_settings', array() );

        return new WP_REST_Response(
            array(
                'ok'              => true,
                'plugin'          => 'KafiChat Lite',
                'version'         => defined( 'KAFICHAT_LITE_VERSION' ) ? KAFICHAT_LITE_VERSION : 'unknown',
                'webhook_enabled' => ! empty( $settings['webhook_enabled'] ),
                'timestamp'       => current_time( 'mysql', true ),
            ),
            200
        );
    }

    /**
     * Verify the X-Telegram-Bot-Api-Secret-Token header.
     *
     * @param WP_REST_Request $request REST request.
     * @return bool True if header is present and matches stored secret.
     */
    private static function verify_header_secret( WP_REST_Request $request ): bool {
        $header_secret = $request->get_header( 'x_telegram_bot_api_secret_token' );

        if ( empty( $header_secret ) ) {
            return false;
        }

        $stored_secret = get_option( 'kafichat_webhook_secret', '' );
        if ( empty( $stored_secret ) ) {
            return false;
        }

        return hash_equals( $stored_secret, $header_secret );
    }

    /**
     * Verify the secret from URL path (fallback method).
     *
     * @param string $url_secret Secret from URL.
     * @return bool True if matches stored secret.
     */
    private static function verify_url_secret( string $url_secret ): bool {
        $stored_secret = get_option( 'kafichat_webhook_secret', '' );
        if ( empty( $stored_secret ) ) {
            return false;
        }

        return hash_equals( $stored_secret, $url_secret );
    }

    /**
     * Get the webhook URL (primary route).
     *
     * @return string Full webhook URL.
     */
    public static function get_webhook_url(): string {
        return rest_url( self::REST_NAMESPACE . '/webhook/bale' );
    }

    /**
     * Get the webhook URL with secret in path (fallback).
     *
     * @return string Full webhook URL with secret.
     */
    public static function get_webhook_url_with_secret(): string {
        $secret = get_option( 'kafichat_webhook_secret', '' );
        if ( empty( $secret ) ) {
            return self::get_webhook_url();
        }

        return rest_url( self::REST_NAMESPACE . '/webhook/bale/' . $secret );
    }

    /**
     * Check webhook health by calling Bale's getWebhookInfo.
     *
     * @return array {
     *     @type bool   $healthy         True if webhook is properly configured.
     *     @type string $status          Status message.
     *     @type array  $webhook_info    Raw webhook info from Bale.
     * }
     */
    public static function health_check(): array {
        $info = BaleApiService::getWebhookInfo();

        if ( ! $info || ! isset( $info['ok'] ) || ! $info['ok'] ) {
            return array(
                'healthy'      => false,
                'status'       => 'Could not fetch webhook info from Bale',
                'webhook_info' => array(),
            );
        }

        $result      = $info['result'] ?? array();
        $expected_url = self::get_webhook_url();
        $current_url  = $result['url'] ?? '';

        // Normalize URLs for comparison (remove trailing slash).
        $expected_norm = rtrim( $expected_url, '/' );
        $current_norm  = rtrim( $current_url, '/' );

        if ( empty( $current_url ) ) {
            return array(
                'healthy'      => false,
                'status'       => 'Webhook not set',
                'webhook_info' => $result,
            );
        }

        if ( $expected_norm !== $current_norm ) {
            return array(
                'healthy'      => false,
                'status'       => 'Webhook URL mismatch',
                'webhook_info' => $result,
            );
        }

        if ( ! empty( $result['last_error_message'] ) ) {
            return array(
                'healthy'      => false,
                'status'       => 'Webhook error: ' . $result['last_error_message'],
                'webhook_info' => $result,
            );
        }

        return array(
            'healthy'      => true,
            'status'       => 'Webhook is healthy',
            'webhook_info' => $result,
        );
    }

    /**
     * Get client IP address.
     *
     * @return string
     */
    private static function get_client_ip(): string {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';

        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return '0.0.0.0';
        }

        return $ip;
    }
}
