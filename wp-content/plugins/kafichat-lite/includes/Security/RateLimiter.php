<?php
/**
 * Rate limiter using WordPress transients.
 *
 * Prevents abuse by limiting the number of requests per identifier (IP or
 * user) within a time window. Uses transients for storage, which are
 * automatically cleaned up by WordPress.
 *
 * Default limits:
 * - Per IP: 30 requests per minute
 * - Per user: 60 requests per minute
 * - Message send: 10 requests per minute (stricter for chat)
 *
 * @package KafiChatLite\Security
 */

namespace KafiChatLite\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Class RateLimiter
 */
final class RateLimiter {

    /**
     * Transient prefix for rate limiter keys.
     *
     * @var string
     */
    private const PREFIX = 'kafichat_rl_';

    /**
     * Default rate limit per IP (requests per minute).
     *
     * @var int
     */
    public const LIMIT_PER_IP = 30;

    /**
     * Default rate limit per user (requests per minute).
     *
     * @var int
     */
    public const LIMIT_PER_USER = 60;

    /**
     * Stricter rate limit for message send (requests per minute).
     *
     * @var int
     */
    public const LIMIT_MESSAGE_SEND = 10;

    /**
     * Time window in seconds (1 minute).
     *
     * @var int
     */
    public const WINDOW_SECONDS = 60;

    /**
     * Check if an action is allowed for the given identifier.
     *
     * Does NOT increment the counter — call increment() separately if the
     * action proceeds.
     *
     * @param string $action     Action name (e.g., 'message_send').
     * @param string $identifier Unique identifier (IP hash or user ID).
     * @param int    $limit      Maximum allowed requests in the window.
     * @param int    $window     Time window in seconds.
     * @return bool True if allowed, false if rate limited.
     */
    public static function check( string $action, string $identifier, int $limit, int $window = self::WINDOW_SECONDS ): bool {
        $key   = self::build_key( $action, $identifier );
        $count = (int) get_transient( $key );
        return $count < $limit;
    }

    /**
     * Increment the counter for an action.
     *
     * Should be called AFTER the action is allowed (not during check).
     *
     * @param string $action     Action name.
     * @param string $identifier Unique identifier.
     * @param int    $window     Time window in seconds.
     * @return void
     */
    public static function increment( string $action, string $identifier, int $window = self::WINDOW_SECONDS ): void {
        $key   = self::build_key( $action, $identifier );
        $count = (int) get_transient( $key );
        set_transient( $key, $count + 1, $window );
    }

    /**
     * Check and increment in one call.
     *
     * Returns true if allowed and increments the counter. Returns false if
     * rate limited (counter is NOT incremented).
     *
     * @param string $action     Action name.
     * @param string $identifier Unique identifier.
     * @param int    $limit      Maximum allowed requests.
     * @param int    $window     Time window in seconds.
     * @return bool True if allowed, false if rate limited.
     */
    public static function hit( string $action, string $identifier, int $limit, int $window = self::WINDOW_SECONDS ): bool {
        if ( ! self::check( $action, $identifier, $limit, $window ) ) {
            return false;
        }
        self::increment( $action, $identifier, $window );
        return true;
    }

    /**
     * Convenience method: check IP-based rate limit for general actions.
     *
     * @param string $action Action name.
     * @return bool
     */
    public static function check_ip( string $action ): bool {
        $ip = self::get_client_ip();
        return self::hit( $action, $ip, self::LIMIT_PER_IP );
    }

    /**
     * Convenience method: check rate limit for message send (stricter).
     *
     * Checks both IP and user (if logged in). Returns true only if both
     * limits are satisfied.
     *
     * @return bool
     */
    public static function check_message_send(): bool {
        $ip = self::get_client_ip();
        if ( ! self::hit( 'message_send_ip', $ip, self::LIMIT_MESSAGE_SEND ) ) {
            return false;
        }

        if ( is_user_logged_in() ) {
            $user_id = (string) get_current_user_id();
            if ( ! self::hit( 'message_send_user', $user_id, self::LIMIT_MESSAGE_SEND ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the client's real IP address (CDN/Proxy Compatible).
     *
     * Uses REMOTE_ADDR by default. If the server is behind a trusted proxy/CDN
     * (Cloudflare, ArvanCloud), it safely checks forwarded headers.
     *
     * @return string IP address (IPv4 or IPv6).
     */
    public static function get_client_ip(): string {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';

        // If REMOTE_ADDR is a private IP, we are likely behind a proxy/CDN.
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
            
            // Check Cloudflare specific header first
            if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
                $cf_ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
                if ( filter_var( $cf_ip, FILTER_VALIDATE_IP ) ) {
                    return $cf_ip;
                }
            }
            
            // Check generic forwarded header (takes the first IP in the chain)
            if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                $forwarded_ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
                if ( ! empty( $forwarded_ips ) ) {
                    $proxy_ip = trim( $forwarded_ips[0] );
                    if ( filter_var( $proxy_ip, FILTER_VALIDATE_IP ) ) {
                        return $proxy_ip;
                    }
                }
            }
            
            // Check X-Real-IP
            if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
                $real_ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
                if ( filter_var( $real_ip, FILTER_VALIDATE_IP ) ) {
                    return $real_ip;
                }
            }
        }

        // Fallback to REMOTE_ADDR if valid
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return '0.0.0.0';
        }

        return $ip;
    }

    /**
     * Calculate exponential backoff duration based on violation count.
     *
     * Returns the number of seconds a user should be blocked after exceeding
     * the rate limit. Duration doubles with each violation:
     * - 1st violation: 60 seconds (1 minute)
     * - 2nd violation: 120 seconds (2 minutes)
     * - 3rd violation: 240 seconds (4 minutes)
     * - 4th violation: 480 seconds (8 minutes)
     * - 5th violation: 960 seconds (16 minutes)
     * - 6th violation: 1920 seconds (32 minutes)
     * - 7+ violations: capped at 3600 seconds (1 hour)
     *
     * @param int $violation_count Number of times the user has exceeded the limit.
     * @return int Backoff duration in seconds.
     */
    public static function get_backoff_seconds( int $violation_count ): int {
        if ( $violation_count < 1 ) {
            return 0;
        }

        // Exponential: 60 * 2^(violations-1), capped at 1 hour.
        $backoff = 60 * ( 1 << min( $violation_count - 1, 6 ) );
        return min( $backoff, 3600 );
    }

    /**
     * Record a rate limit violation for exponential backoff tracking.
     *
     * @param string $identifier Unique identifier (IP or user ID).
     * @return void
     */
    public static function record_violation( string $identifier ): void {
        $key   = self::PREFIX . 'violations_' . substr( md5( $identifier ), 0, 16 );
        $count = (int) get_transient( $key );
        set_transient( $key, $count + 1, 3600 ); // Track for 1 hour.
    }

    /**
     * Get the number of violations for an identifier.
     *
     * @param string $identifier Unique identifier.
     * @return int Number of violations in the last hour.
     */
    public static function get_violation_count( string $identifier ): int {
        $key = self::PREFIX . 'violations_' . substr( md5( $identifier ), 0, 16 );
        return (int) get_transient( $key );
    }

    /**
     * Build a transient key for rate limiting.
     *
     * @param string $action     Action name.
     * @param string $identifier Unique identifier.
     * @return string Transient key (max 172 chars for WordPress compatibility).
     */
    private static function build_key( string $action, string $identifier ): string {
        // Hash the identifier to keep keys short and prevent injection.
        $hash = substr( md5( $identifier ), 0, 16 );
        return self::PREFIX . $action . '_' . $hash;
    }
}
