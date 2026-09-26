<?php
/**
 * Guest authentication via secure cookie.
 *
 * Manages guest user identification using a cryptographically random token
 * stored in an HttpOnly, SameSite=Lax cookie. The token itself is never
 * stored in the database — only its HMAC-SHA256 hash is persisted.
 *
 * Security properties:
 * - Token: 64-character hex string from random_bytes(32)
 * - Cookie: HttpOnly (XSS-proof), SameSite=Lax (CSRF mitigation)
 * - Storage: Only HMAC hash in DB, never the raw token
 * - No PHP sessions: stateless, scalable, shared-hosting friendly
 *
 * @package KafiChatLite\Security
 */

namespace KafiChatLite\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Class GuestAuth
 */
final class GuestAuth {

    /**
     * Cookie name for the guest token.
     *
     * @var string
     */
    public const COOKIE_NAME = 'kafichat_guest_token';

    /**
     * Cookie lifetime in seconds (30 days).
     *
     * @var int
     */
    public const COOKIE_LIFETIME = 30 * DAY_IN_SECONDS;

    /**
     * HMAC context (separates this key from other uses of wp_salt).
     *
     * @var string
     */
    private const HMAC_CONTEXT = 'kafichat_guest_hmac_v1';

    /**
     * Issue a new guest token and set the cookie.
     *
     * Should be called when a guest initiates their first conversation.
     * Returns the raw token (for immediate use) — only the hash should
     * be stored in the database.
     *
     * @return string 64-character hex token, or empty string on failure.
     */
    public static function issue_token(): string {
        try {
            $token = bin2hex( random_bytes( 32 ) );
        } catch ( \Exception $e ) {
            return '';
        }

        self::set_cookie( $token );
        return $token;
    }

    /**
     * Get the current guest token from the cookie.
     *
     * @return string|null 64-character hex token, or null if not present.
     */
    public static function get_current_token(): ?string {
        if ( ! isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
            return null;
        }

        $token = sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) );

        if ( ! self::is_valid_token_format( $token ) ) {
            return null;
        }

        return $token;
    }

    /**
     * Compute the HMAC-SHA256 hash of a token for database storage.
     *
     * The raw token should NEVER be stored in the database. Only this
     * hash is persisted, making it impossible to recover the token from
     * a database breach.
     *
     * @param string $token The raw guest token.
     * @return string 64-character hex HMAC hash.
     */
    public static function hash_token( string $token ): string {
        $key = self::get_hmac_key();
        return hash_hmac( 'sha256', $token, $key );
    }

    /**
     * Verify that a token has the correct format (64 hex characters).
     *
     * Does NOT verify ownership or database presence — only format.
     *
     * @param string $token The token to validate.
     * @return bool
     */
    public static function is_valid_token_format( string $token ): bool {
        return 64 === strlen( $token ) && ctype_xdigit( $token );
    }

    /**
     * Clear the guest token cookie (logout).
     *
     * @return void
     */
    public static function clear_token(): void {
        if ( ! headers_sent() ) {
            setcookie(
                self::COOKIE_NAME,
                '',
                array(
                    'expires'  => time() - 3600,
                    'path'     => '/',
                    'domain'   => self::get_cookie_domain(),
                    'secure'   => is_ssl(),
                    'httponly' => true,
                    'samesite' => 'Lax',
                )
            );
        }
        unset( $_COOKIE[ self::COOKIE_NAME ] );
    }

    /**
     * Set the guest token cookie.
     *
     * @param string $token The raw token.
     * @return void
     */
    private static function set_cookie( string $token ): void {
        if ( headers_sent() ) {
            return;
        }

        setcookie(
            self::COOKIE_NAME,
            $token,
            array(
                'expires'  => time() + self::COOKIE_LIFETIME,
                'path'     => '/',
                'domain'   => self::get_cookie_domain(),
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            )
        );
    }

    /**
     * Get the cookie domain (respects WordPress multisite).
     *
     * @return string
     */
    private static function get_cookie_domain(): string {
        if ( defined( 'COOKIE_DOMAIN' ) && COOKIE_DOMAIN ) {
            return COOKIE_DOMAIN;
        }
        return '';
    }

    /**
     * Derive a 32-byte HMAC key from WordPress auth salt.
     *
     * @return string 32-byte binary key.
     */
    private static function get_hmac_key(): string {
        $salt = wp_salt( 'auth' );
        return hash( 'sha256', self::HMAC_CONTEXT . $salt, true );
    }
}
