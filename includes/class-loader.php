<?php
/**
 * Autoloader class
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot;

/**
 * Class Loader
 *
 * Handles PSR-4 autoloading for plugin classes
 */
class Loader {

	/**
	 * Register the autoloader
	 *
	 * @return void
	 */
	public static function register(): void {
		spl_autoload_register(array( self::class, 'autoload' ));

		// Load all integration files automatically
		self::load_integrations();
	}

	/**
	 * Autoload classes
	 *
	 * @param string $class The class name to load.
	 * @return void
	 */
	public static function autoload( string $class ): void {
		// Only handle classes in our namespace
		if ( strpos($class, 'MenuPilot\\') !== 0 ) {
			return;
		}

		// Remove namespace from class name
		$class = str_replace('MenuPilot\\', '', $class);

		// Convert class name to file path (PascalCase -> kebab-case)
		$file = MENUPILOT_PLUGIN_DIR . 'includes/class-' .
			strtolower(str_replace('_', '-', $class)) . '.php';

		// Load the file if it exists
		if ( file_exists($file) ) {
			require_once $file;
			return;
		}

		// Check integrations directory
		$file = MENUPILOT_PLUGIN_DIR . 'includes/integrations/class-' .
			strtolower(str_replace('_', '-', $class)) . '.php';

		if ( file_exists($file) ) {
			require_once $file;
			return;
		}

		// Check categorized integration subdirectories
		$categories = array( 'core', 'ecommerce', 'forms', 'others', 'community', 'membership' );
		foreach ( $categories as $category ) {
			$file = MENUPILOT_PLUGIN_DIR . 'includes/integrations/' . $category . '/class-' .
				strtolower(str_replace('_', '-', $class)) . '.php';
			if ( file_exists($file) ) {
				require_once $file;
				return;
			}
		}
	}

	/**
	 * Load all integration files
	 *
	 * @return void
	 */
	public static function load_integrations(): void {
		$integrations_dir = MENUPILOT_PLUGIN_DIR . 'includes/integrations/';
		$categories = array( '.', 'core', 'ecommerce', 'forms', 'others', 'community', 'membership' );

		if ( ! is_dir($integrations_dir) ) {
			return;
		}

		$files = array();
		foreach ( $categories as $category ) {
			$dir = rtrim($integrations_dir . ( $category === '.' ? '' : $category . '/' ), '/');
			if ( is_dir($dir) ) {
				$found = glob($dir . '/class-*.php') ?: array();
				if ( $found ) {
					$files = array_merge($files, $found);
				}
			}
		}

		if ( empty($files) ) {
			return;
		}

		foreach ( $files as $file ) {
			require_once $file;
		}
	}
}

// Register the autoloader
Loader::register();
