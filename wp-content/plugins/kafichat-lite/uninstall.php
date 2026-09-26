<?php
/**
 * Uninstall handler for KafiChat Lite.
 * Safely removes all tables, options, cron events, and transients.
 * Respects Pro edition installation to prevent accidental data loss.
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// ==========================================================================
// PROTECTION: If Pro is installed, DO NOT delete anything!
// ==========================================================================
if ( get_option( 'kafichat_pro_installed' ) ) {
	return; 
}

global $wpdb;

// ==========================================================================
// 1. Drop all Lite database tables
// ==========================================================================
$kafichat_tables = array(
	$wpdb->prefix . 'kafichat_conversations',
	$wpdb->prefix . 'kafichat_messages',
	$wpdb->prefix . 'kafichat_logs',
	$wpdb->prefix . 'kafichat_attachments', // Lite creates this for Pro compatibility
);

foreach ( $kafichat_tables as $table ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
}

// ==========================================================================
// 2. Remove ALL plugin options (Complete List)
// ==========================================================================
$kafichat_options = array(
	'kafichat_settings',
	'kafichat_version',
	'kafichat_db_version',        // Legacy fallback
	'kafichat_schema_version',    // <-- FIX: Explicitly added to ensure clean removal
	'kafichat_has_sodium',
	'kafichat_webhook_secret',
	'kafichat_pro_installed',     // Safety clear if Pro was somehow removed first
);

foreach ( $kafichat_options as $opt ) {
	delete_option( $opt );
}

// ==========================================================================
// 3. Clear scheduled cron events
// ==========================================================================
wp_clear_scheduled_hook( 'kafichat_daily_cleanup' );
wp_clear_scheduled_hook( 'kafichat_bale_polling' );

// ==========================================================================
// 4. Remove transients
// ==========================================================================
delete_transient( 'kafichat_bale_me' );
delete_transient( 'kafichat_webhook_health' );