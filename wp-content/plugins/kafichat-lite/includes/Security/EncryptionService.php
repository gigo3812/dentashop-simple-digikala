<?php
/**
 * Encryption service for sensitive data (v1.0.1 - Pro Compatible).
 *
 * Provides symmetric encryption using Libsodium (preferred) or OpenSSL
 * AES-256-GCM as a fallback. Used to encrypt guest PII (name, phone, IP).
 *
 * @package KafiChatLite\Security
 */

namespace KafiChatLite\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Class EncryptionService
 */
final class EncryptionService {

    /**
     * Option key for storing the Base64-encoded encryption key.
     */
    private const OPTION_KEY = 'kafichat_encryption_key';

    /**
     * Cipher method for OpenSSL fallback.
     */
    private const OPENSSL_CIPHER = 'aes-256-gcm';

    /**
     * Encrypt a plaintext string.
     *
     * @param string $plaintext The data to encrypt.
     * @return string Prefixed and Base64-encoded ciphertext, or empty string on failure.
     */
    public static function encrypt( string $plaintext ): string {
        if ( '' === $plaintext ) {
            return '';
        }

        $key = self::get_key();
        if ( '' === $key ) {
            return '';
        }

        if ( self::use_sodium() ) {
            return self::encrypt_sodium( $plaintext, $key );
        }

        return self::encrypt_openssl( $plaintext, $key );
    }

    /**
     * Decrypt a ciphertext string.
     *
     * @param string $ciphertext The encrypted string (with algorithm prefix).
     * @return string|null The decrypted plaintext, or null on failure.
     */
    public static function decrypt( string $ciphertext ): ?string {
        if ( '' === $ciphertext ) {
            return '';
        }

        $key = self::get_key();
        if ( '' === $key ) {
            return null;
        }

        // New format with prefixes (Pro compatible)
        if ( strpos( $ciphertext, 'sodium:' ) === 0 ) {
            return self::decrypt_sodium( substr( $ciphertext, 7 ), $key );
        }
        
        if ( strpos( $ciphertext, 'openssl:' ) === 0 ) {
            return self::decrypt_openssl( substr( $ciphertext, 8 ), $key );
        }

        // Fallback for old v1.0.0 data (no prefix)
        if ( self::use_sodium() ) {
            $result = self::decrypt_sodium( $ciphertext, $key );
            if ( null !== $result ) {
                return $result;
            }
        }
        
        return self::decrypt_openssl( $ciphertext, $key );
    }

    /**
     * Check if Libsodium is available.
     *
     * @return bool
     */
    public static function use_sodium(): bool {
        return extension_loaded( 'sodium' )
            && function_exists( 'sodium_crypto_secretbox' )
            && function_exists( 'sodium_crypto_secretbox_open' );
    }

    /**
     * Get or generate the encryption key.
     * Once generated, it is NEVER overwritten to protect existing data.
     *
     * @return string 32-byte binary key, or empty string on failure.
     */
    private static function get_key(): string {
        $base64_key = get_option( self::OPTION_KEY );

        if ( ! empty( $base64_key ) ) {
            $decoded = base64_decode( $base64_key, true );
            if ( false !== $decoded && 32 === strlen( $decoded ) ) {
                return $decoded;
            }
        }

        // Generate new key if not exists.
        try {
            $key = random_bytes( 32 );
            update_option( self::OPTION_KEY, base64_encode( $key ), false );
            return $key;
        } catch ( \Exception $e ) {
            return '';
        }
    }

    /**
     * Encrypt using Libsodium secretbox (XSalsa20-Poly1305).
     *
     * @param string $plaintext The data to encrypt.
     * @param string $key       32-byte key.
     * @return string "sodium:" prefixed Base64 string.
     */
    private static function encrypt_sodium( string $plaintext, string $key ): string {
        try {
            $nonce      = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
            $ciphertext = sodium_crypto_secretbox( $plaintext, $nonce, $key );

            $encoded = base64_encode( $nonce ) . '.' . base64_encode( $ciphertext );

            // Clear sensitive data from memory
            if ( function_exists( 'sodium_memzero' ) ) {
                try {
                    sodium_memzero( $plaintext );
                    sodium_memzero( $key );
                } catch ( \Exception $e ) {
                    // Ignore: memzero is best-effort.
                }
            }

            return 'sodium:' . $encoded;
        } catch ( \Exception $e ) {
            return '';
        }
    }

    /**
     * Decrypt using Libsodium secretbox.
     *
     * @param string $payload Base64-encoded "nonce.ciphertext".
     * @param string $key     32-byte key.
     * @return string|null Decrypted plaintext, or null on failure.
     */
    private static function decrypt_sodium( string $payload, ?string $key ): ?string {
        $parts = explode( '.', $payload, 2 );
        if ( 2 !== count( $parts ) ) {
            return null;
        }

        try {
            $nonce      = base64_decode( $parts[0], true );
            $cipher_bin = base64_decode( $parts[1], true );

            if ( false === $nonce || false === $cipher_bin ) {
                return null;
            }

            if ( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES !== strlen( $nonce ) ) {
                return null;
            }

            $plaintext = sodium_crypto_secretbox_open( $cipher_bin, $nonce, $key );
            if ( false === $plaintext ) {
                return null;
            }

            return $plaintext;
        } catch ( \Exception $e ) {
            return null;
        }
    }

    /**
     * Encrypt using OpenSSL AES-256-GCM.
     *
     * @param string $plaintext The data to encrypt.
     * @param string $key       32-byte key.
     * @return string "openssl:" prefixed Base64 string.
     */
    private static function encrypt_openssl( string $plaintext, string $key ): string {
        if ( ! extension_loaded( 'openssl' ) ) {
            return '';
        }

        try {
            $nonce      = random_bytes( 12 ); // 96-bit nonce for GCM.
            $tag        = '';
            $ciphertext = openssl_encrypt(
                $plaintext,
                self::OPENSSL_CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $nonce,
                $tag,
                '',
                16
            );

            if ( false === $ciphertext ) {
                return '';
            }

            $encoded = base64_encode( $nonce ) . '.' . base64_encode( $ciphertext ) . '.' . base64_encode( $tag );
            return 'openssl:' . $encoded;
        } catch ( \Exception $e ) {
            return '';
        }
    }

    /**
     * Decrypt using OpenSSL AES-256-GCM.
     *
     * @param string $payload Base64-encoded "nonce.ciphertext.tag".
     * @param string $key     32-byte key.
     * @return string|null Decrypted plaintext, or null on failure.
     */
    private static function decrypt_openssl( string $payload, ?string $key ): ?string {
        if ( ! extension_loaded( 'openssl' ) ) {
            return null;
        }

        $parts = explode( '.', $payload, 3 );
        if ( 3 !== count( $parts ) ) {
            return null;
        }

        try {
            $nonce      = base64_decode( $parts[0], true );
            $cipher_bin = base64_decode( $parts[1], true );
            $tag        = base64_decode( $parts[2], true );

            if ( false === $nonce || false === $cipher_bin || false === $tag ) {
                return null;
            }

            if ( 12 !== strlen( $nonce ) ) {
                return null;
            }

            $plaintext = openssl_decrypt(
                $cipher_bin,
                self::OPENSSL_CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $nonce,
                $tag
            );

            if ( false === $plaintext ) {
                return null;
            }

            return $plaintext;
        } catch ( \Exception $e ) {
            return null;
        }
    }
}