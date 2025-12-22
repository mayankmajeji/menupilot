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
	// Delete plugin options
	delete_option('menupilot_settings');
	
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
