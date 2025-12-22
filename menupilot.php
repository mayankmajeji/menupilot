<?php

/**
 * Plugin Name: MenuPilot
 * Plugin URI: https://mayankmajeji.com/menupilot
 * Description: MenuPilot makes it easy to move, duplicate, and back up WordPress menus between sites without breaking links or structure.
 * Version: 1.0.0
 * Author: Mayank Majeji
 * Author URI: 
 * Requires at least: 5.8
 * Tested up to: 6.5
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
if (! defined('WPINC')) {
	die;
}

// Plugin version
define('MENUPILOT_VERSION', '1.0.0');
define('MENUPILOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MENUPILOT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MENUPILOT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
require_once MENUPILOT_PLUGIN_DIR . 'includes/class-loader.php';

/**
 * Initialize the plugin.
 *
 * @return void
 */
function menupilot_init()
{
	// Load text domain
	load_plugin_textdomain(
		'menupilot',
		false,
		dirname(MENUPILOT_PLUGIN_BASENAME) . '/i18n/languages'
	);

	// Initialize main plugin class as singleton
	\MenuPilot\Init::get_instance()->init();
}

add_action('plugins_loaded', 'menupilot_init');

// Activation hook
register_activation_hook(__FILE__, function () {
	require_once MENUPILOT_PLUGIN_DIR . 'includes/class-init.php';
	\MenuPilot\Init::get_instance()->activate();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function () {
	require_once MENUPILOT_PLUGIN_DIR . 'includes/class-init.php';
	\MenuPilot\Init::get_instance()->deactivate();
});
