<?php
/**
 * Plugin Name: MenuPilot – Preview-First Menu Import & Export
 * Plugin URI: https://github.com/mayankmajeji/menupilot
 * Description: Safely import and export WordPress navigation menus with a preview-first workflow. Review and map menus before importing.
 * Version: 1.0.13
 * Author: Mayank Majeji
 * Author URI:
 * Requires at least: 5.8
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: menupilot
 * Domain Path: /i18n/languages
 *
 * @package MenuPilot
 */

declare(strict_types=1);

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Plugin version.
define( 'MENUPILOT_VERSION', '1.0.13' );
define( 'MENUPILOT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MENUPILOT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MENUPILOT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Autoloader.
require_once MENUPILOT_PLUGIN_DIR . 'includes/class-loader.php';

/**
 * Initialize the plugin.
 *
 * @return void
 */
function menupilot_init() {
	/*
	 * Text domain is automatically loaded by WordPress.org for hosted plugins.
	 * For non-WordPress.org installations or custom language paths, use the filter:
	 * add_filter('override_load_textdomain', function($override, $domain) {
	 * if ($domain === 'menupilot') {
	 * load_plugin_textdomain('menupilot', false, dirname(MENUPILOT_PLUGIN_BASENAME) . '/i18n/languages');
	 * return true;
	 * }
	 * return $override;
	 * }, 10, 2);
	 */

	// Initialize main plugin class as singleton.
	\MenuPilot\Init::get_instance()->init();
}

add_action( 'plugins_loaded', 'menupilot_init' );

// Activation hook.
register_activation_hook(
	__FILE__,
	function () {
		require_once MENUPILOT_PLUGIN_DIR . 'includes/class-init.php';
		\MenuPilot\Init::get_instance()->activate();
	}
);

// Deactivation hook.
register_deactivation_hook(
	__FILE__,
	function () {
		require_once MENUPILOT_PLUGIN_DIR . 'includes/class-init.php';
		\MenuPilot\Init::get_instance()->deactivate();
	}
);
