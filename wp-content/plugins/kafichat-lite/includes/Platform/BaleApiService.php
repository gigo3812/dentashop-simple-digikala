<?php
/**
 * Bale Bot API service.
 *
 * Handles all communication with the Bale messenger bot API.
 * Base URL: https://tapi.bale.ai/bot{TOKEN}/
 *
 * Security:
 * - Bot token is never logged or exposed in error messages
 * - All requests use WordPress HTTP API (wp_remote_post/get)
 * - Errors are logged with context but without sensitive data
 *
 * @package KafiChatLite\Platform
 */
namespace KafiChatLite\Platform;

defined( 'ABSPATH' ) || exit;

use KafiChatLite\Database\LogRepository;

/**
 * Class BaleApiService
 */
final class BaleApiService {

	/**
	 * Bale API base URL.
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://tapi.bale.ai/bot';

	/**
	 * Request timeout in seconds (15s is WordPress recommended best practice).
	 *
	 * @var int
	 */
	private const TIMEOUT = 15;

	/**
	 * Send a text message to a chat.
	 *
	 * @param int    $chat_id Chat ID (user or group).
	 * @param string $text    Message text (max 4096 chars).
	 * @return array|null Response array on success, null on failure.
	 */
	public static function sendMessage( int $chat_id, string $text ): ?array {
		return self::request( 'sendMessage', array(
			'chat_id' => $chat_id,
			'text'    => substr( $text, 0, 4096 ),
		) );
	}

	/**
	 * Get updates (for polling).
	 *
	 * @param int $offset Identifier of the first update to be returned.
	 * @param int $limit  Limits the number of updates (1-100, default: 100).
	 * @return array|null Response array on success, null on failure.
	 */
	public static function getUpdates( int $offset = 0, int $limit = 100 ): ?array {
		return self::request( 'getUpdates', array(
			'offset'  => $offset,
			'limit'   => min( max( $limit, 1 ), 100 ),
			'timeout' => 30, // Override timeout for long-polling if needed
		) );
	}

	/**
	 * Set webhook URL.
	 *
	 * @param string      $url         Webhook URL (must be HTTPS).
	 * @param string|null $secret_token Optional secret token for verification.
	 *                                  Sent in X-Telegram-Bot-Api-Secret-Token header.
	 *                                  Note: Bale may not support this parameter;
	 *                                  if not, it is silently ignored.
	 * @return array|null Response array on success, null on failure.
	 */
	public static function setWebhook( string $url, ?string $secret_token = null ): ?array {
		$params = array( 'url' => $url );
		if ( $secret_token ) {
			$params['secret_token'] = $secret_token;
		}
		return self::request( 'setWebhook', $params );
	}

	/**
	 * Delete webhook.
	 *
	 * @return array|null Response array on success, null on failure.
	 */
	public static function deleteWebhook(): ?array {
		return self::request( 'deleteWebhook' );
	}

	/**
	 * Get webhook info.
	 *
	 * @return array|null Response array on success, null on failure.
	 */
	public static function getWebhookInfo(): ?array {
		return self::request( 'getWebhookInfo' );
	}

	/**
	 * Get bot info (for testing connection).
	 *
	 * @return array|null Response array on success, null on failure.
	 */
	public static function getMe(): ?array {
		return self::request( 'getMe' );
	}

	/**
	 * Test connection to Bale API.
	 *
	 * @return bool True if connection successful, false otherwise.
	 */
	public static function testConnection(): bool {
		$result = self::getMe();
		return null !== $result && isset( $result['ok'] ) && true === $result['ok'];
	}

	/**
	 * Make an API request.
	 *
	 * @param string $method API method name.
	 * @param array  $params Request parameters.
	 * @return array|null Response array on success, null on failure.
	 */
	private static function request( string $method, array $params = array() ): ?array {
		$token = self::get_token();
		if ( ! $token ) {
			LogRepository::error( 'Bale API: Bot token not configured', 'api' );
			return null;
		}

		$url  = self::BASE_URL . $token . '/' . $method;
		$args = array(
			'timeout'   => self::TIMEOUT, // 15 seconds (WordPress Best Practice)
			'sslverify' => true,
			'headers'   => array(
				'Content-Type' => 'application/json',
			),
		);

		// Use GET for methods without parameters, POST for others.
		if ( empty( $params ) ) {
			$response = wp_remote_get( $url, $args );
		} else {
			$args['body'] = wp_json_encode( $params );
			$response     = wp_remote_post( $url, $args );
		}

		// Check for HTTP errors.
		if ( is_wp_error( $response ) ) {
			LogRepository::error(
				'Bale API: HTTP request failed',
				'api',
				array(
					'method' => $method,
					'error'  => $response->get_error_message(),
				)
			);
			return null;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		$data        = json_decode( $body, true );

		// Check for API errors.
		if ( ! is_array( $data ) || ! isset( $data['ok'] ) || false === $data['ok'] ) {
			$error_desc = isset( $data['description'] ) ? $data['description'] : 'Unknown error';
			LogRepository::error(
				'Bale API: Request failed',
				'api',
				array(
					'method'      => $method,
					'status_code' => $status_code,
					'error'       => $error_desc,
				)
			);
			return null;
		}

		return $data;
	}

	/**
	 * Get the bot token from settings.
	 *
	 * @return string|null Bot token or null if not configured.
	 */
	private static function get_token(): ?string {
		$settings = get_option( 'kafichat_settings', array() );
		$token    = $settings['bot_token'] ?? '';
		if ( empty( $token ) ) {
			return null;
		}
		return sanitize_text_field( $token );
	}

	/**
	 * Get the admin chat ID from settings.
	 *
	 * @return int|null Admin chat ID or null if not configured.
	 */
	public static function get_admin_chat_id(): ?int {
		$settings = get_option( 'kafichat_settings', array() );
		$chat_id  = $settings['admin_chat_id'] ?? 0;
		if ( empty( $chat_id ) ) {
			return null;
		}
		return (int) $chat_id;
	}
}