<?php
/**
 * Plugin uninstall handler
 *
 * @package MenuPilot
 */

// If uninstall not called from WordPress, exit
if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
	exit;
}

/**
 * Delete plugin options
 */
function menupilot_uninstall() {
	global $wpdb;

	// Drop history table
	$table = $wpdb->prefix . 'menupilot_history';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Uninstall: $table from $wpdb->prefix + constant; no API exists.
	$wpdb->query("DROP TABLE IF EXISTS {$table}");

	// Delete plugin options
	delete_option('menupilot_settings');
	delete_option('menupilot_backups');
	
	// Delete any transients
	delete_transient('menupilot_cache');
	
	// Delete any custom user meta
	// delete_metadata('user', 0, 'menupilot_user_meta', '', true);
	
	/**
	 * Fires during plugin uninstallation
	 *
	 * Use this hook to clean up custom tables, files, or other data
	 *
	 * @since 1.0.0
	 */
	do_action('menupilot_uninstall');
}

menupilot_uninstall();
