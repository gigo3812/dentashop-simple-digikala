<?php
/**
 * PSR-4-style autoloader for KafiChat Lite.
 *
 * Maps the `KafiChatLite\` namespace to the `includes/` directory.
 * Example:
 *   KafiChatLite\Core\Activator -> includes/Core/Activator.php
 *
 * @package KafiChatLite\Core
 */

namespace KafiChatLite\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Autoloader
 */
final class Autoloader {

    /**
     * Root namespace handled by this autoloader.
     *
     * @var string
     */
    private const ROOT_NAMESPACE = 'KafiChatLite\\';

    /**
     * Base directory for the namespace (without trailing slash).
     *
     * @var string
     */
    private static string $base_dir = '';

    /**
     * Whether the autoloader has already been registered.
     *
     * @var bool
     */
    private static bool $registered = false;

    /**
     * Register the autoloader exactly once.
     *
     * @return void
     */
    public static function register(): void {
        if ( self::$registered ) {
            return;
        }

        self::$base_dir = dirname( __DIR__ );
        spl_autoload_register( array( self::class, 'autoload' ) );
        self::$registered = true;
    }

    /**
     * Autoload callback.
     *
     * @param string $class Fully qualified class name.
     * @return void
     */
    public static function autoload( string $class ): void {
        if ( 0 !== strpos( $class, self::ROOT_NAMESPACE ) ) {
            return;
        }

        $relative = substr( $class, strlen( self::ROOT_NAMESPACE ) );
        $path     = self::$base_dir . DIRECTORY_SEPARATOR
                  . str_replace( '\\', DIRECTORY_SEPARATOR, $relative )
                  . '.php';

        if ( is_readable( $path ) ) {
            /** @noinspection PhpIncludeInspection */
            require_once $path;
        }
    }
}
