<?php
/**
 * Column Manager
 *
 * Manages preview columns for extensibility
 *
 * @package MenuPilot
 * @since 1.0.0
 */

declare(strict_types=1);

namespace MenuPilot;

/**
 * Column Manager class
 */
class Column_Manager {
	/**
	 * Registered columns
	 *
	 * @var array<string,array<string,mixed>>
	 */
	private static array $columns = array();

	/**
	 * Register a custom column
	 *
	 * @param string $id Column ID (unique identifier).
	 * @param array<string,mixed> $args Column arguments.
	 * @return bool True on success, false if ID already exists.
	 */
	public static function register_column( string $id, array $args ): bool {
		if ( isset(self::$columns[ $id ]) ) {
			return false;
		}

		$defaults = array(
			'label'             => '',
			'order'             => 100,
			'render_callback'   => null,
			'export_callback'   => null,
			'import_callback'   => null,
			'visible'           => true,
		);

		self::$columns[ $id ] = wp_parse_args($args, $defaults);
		return true;
	}

	/**
	 * Unregister a column
	 *
	 * @param string $id Column ID.
	 * @return bool True on success, false if ID doesn't exist.
	 */
	public static function unregister_column( string $id ): bool {
		if ( ! isset(self::$columns[ $id ]) ) {
			return false;
		}

		unset(self::$columns[ $id ]);
		return true;
	}

	/**
	 * Get all registered columns
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_columns(): array {
		// Sort by order
		$columns = self::$columns;
		uasort($columns, function ( $a, $b ) {
			$order_a = isset($a['order']) ? (int) $a['order'] : 100;
			$order_b = isset($b['order']) ? (int) $b['order'] : 100;
			return $order_a - $order_b;
		});

		return apply_filters('menupilot_preview_columns', $columns);
	}

	/**
	 * Get column configuration
	 *
	 * @param string $id Column ID.
	 * @return array<string,mixed>|null Column config or null if not found.
	 */
	public static function get_column( string $id ): ?array {
		return self::$columns[ $id ] ?? null;
	}

	/**
	 * Initialize default columns
	 *
	 * @return void
	 */
	public static function init_default_columns(): void {
		// Title column
		self::register_column('title', array(
			'label'   => __('Title', 'menupilot'),
			'order'   => 10,
			'visible' => true,
		));

		// Type column
		self::register_column('type', array(
			'label'   => __('Type', 'menupilot'),
			'order'   => 20,
			'visible' => true,
		));

		// Auto Status column
		self::register_column('auto_status', array(
			'label'   => __('Auto Status', 'menupilot'),
			'order'   => 30,
			'visible' => true,
		));

		// Map To column
		self::register_column('map_to', array(
			'label'   => __('Map To', 'menupilot'),
			'order'   => 40,
			'visible' => true,
		));

		// Remove column
		self::register_column('remove', array(
			'label'   => __('Remove', 'menupilot'),
			'order'   => 50,
			'visible' => true,
		));

		// Allow plugins to register custom columns
		do_action('menupilot_register_columns');
	}

	/**
	 * Get columns for JavaScript
	 *
	 * @return array<string,string> Column ID => Label mapping.
	 */
	public static function get_columns_for_js(): array {
		$columns = self::get_columns();
		$js_columns = array();

		foreach ( $columns as $id => $config ) {
			if ( isset($config['visible']) && $config['visible'] ) {
				$js_columns[ $id ] = $config['label'];
			}
		}

		return $js_columns;
	}
}

