<?php
/**
 * Input validation and sanitization.
 *
 * Centralizes all validation rules and sanitization logic for user input.
 * Prevents XSS, SQL injection, and invalid data from entering the system.
 *
 * @package KafiChatLite\Security
 */

namespace KafiChatLite\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Class InputValidator
 */
final class InputValidator {

    /**
     * Maximum length for guest name.
     *
     * @var int
     */
    public const MAX_NAME_LENGTH = 100;

    /**
     * Maximum length for chat message.
     *
     * @var int
     */
    public const MAX_MESSAGE_LENGTH = 2000;

    /**
     * Validate an Iranian mobile number.
     *
     * Accepts numbers starting with 09 followed by 9 digits (total 11 digits).
     * Examples: 09123456789, 09351234567
     *
     * @param string $mobile The mobile number to validate.
     * @return bool
     */
    public static function is_valid_iranian_mobile( string $mobile ): bool {
        $mobile = trim( $mobile );
        return (bool) preg_match( '/^09[0-9]{9}$/', $mobile );
    }

    /**
     * Sanitize a guest name.
     *
     * Removes HTML tags, extra whitespace, and dangerous characters.
     * Truncates to MAX_NAME_LENGTH.
     *
     * @param string $name The raw name input.
     * @return string Sanitized name.
     */
    public static function sanitize_name( string $name ): string {
        $name = sanitize_text_field( $name );
        $name = trim( $name );

        if ( strlen( $name ) > self::MAX_NAME_LENGTH ) {
            $name = substr( $name, 0, self::MAX_NAME_LENGTH );
        }

        return $name;
    }

    /**
     * Sanitize a chat message.
     *
     * Removes HTML tags, normalizes line breaks, and truncates to
     * MAX_MESSAGE_LENGTH. Preserves basic formatting (newlines).
     *
     * @param string $message The raw message input.
     * @return string Sanitized message.
     */
    public static function sanitize_message( string $message ): string {
        $message = sanitize_textarea_field( $message );
        $message = trim( $message );

        if ( strlen( $message ) > self::MAX_MESSAGE_LENGTH ) {
            $message = substr( $message, 0, self::MAX_MESSAGE_LENGTH );
        }

        return $message;
    }

    /**
     * Validate that a string is not empty after trimming.
     *
     * @param string $value The value to check.
     * @return bool
     */
    public static function is_not_empty( string $value ): bool {
        return '' !== trim( $value );
    }

    /**
     * Sanitize an email address.
     *
     * @param string $email The raw email input.
     * @return string Sanitized email (empty string if invalid).
     */
    public static function sanitize_email( string $email ): string {
        return sanitize_email( $email );
    }

    /**
     * Validate a conversation reference (32-character hex string).
     *
     * @param string $ref The conversation reference.
     * @return bool
     */
    public static function is_valid_conversation_ref( string $ref ): bool {
        return 32 === strlen( $ref ) && ctype_xdigit( $ref );
    }

    /**
     * Validate an access token (64-character hex string).
     *
     * @param string $token The access token.
     * @return bool
     */
    public static function is_valid_access_token( string $token ): bool {
        return 64 === strlen( $token ) && ctype_xdigit( $token );
    }
}
