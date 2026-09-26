<?php
/**
 * Plugin deactivation routine.
 *
 * Deactivation must NEVER destroy user data. Only transient runtime state
 * (scheduled cron events, rewrite rules, transient caches) is cleared here.
 * Full cleanup belongs to the Uninstaller.
 *
 * @package KafiChatLite\Core
 */
namespace KafiChatLite\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Deactivator
 */
final class Deactivator {

	/**
	 * Cron hook names registered by the plugin.
	 *
	 * Defined up-front so deactivation can clean them up even if the
	 * implementing classes are not loaded yet.
	 *
	 * @var string[]
	 */
	private const CRON_HOOKS = array(
		'kafichat_daily_cleanup',
		'kafichat_bale_polling',
	);

	/**
	 * Deactivation entry point.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		self::clear_scheduled_events();
		self::flush_rewrite_rules_if_needed();
	}

	/**
	 * Unschedule every cron hook owned by the plugin.
	 *
	 * Uses the Cron task classes as the single source of truth for hook names.
	 * Falls back to the local CRON_HOOKS constant if the classes are not
	 * available (e.g., during partial uninstall).
	 *
	 * @return void
	 */
	private static function clear_scheduled_events(): void {
		// روش اول: استفاده از متدهای اختصاصی کلاس‌ها (ترجیحی)
		if ( class_exists( \KafiChatLite\Cron\CleanupTask::class ) ) {
			\KafiChatLite\Cron\CleanupTask::unschedule();
		}
		if ( class_exists( \KafiChatLite\Cron\PollingTask::class ) ) {
			\KafiChatLite\Cron\PollingTask::unschedule();
		}

		// روش دوم (Fallback): پاک‌سازی مستقیم هوک‌ها از لیست ثابت
		foreach ( self::CRON_HOOKS as $hook ) {
			wp_clear_scheduled_hook( $hook );
		}
	}

	/**
	 * Flush rewrite rules so any custom endpoints registered by the plugin
	 * are removed cleanly. Guarded to avoid a fatal if the function is not
	 * yet available (edge case in some activation flows).
	 *
	 * @return void
	 */
	private static function flush_rewrite_rules_if_needed(): void {
		if ( function_exists( 'flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
		}
	}
}