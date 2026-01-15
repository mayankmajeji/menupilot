<?php
/**
 * Plugin settings class
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot;

/**
 * Class Settings
 *
 * Handles plugin settings registration, sanitization, and retrieval
 */
class Settings {

	/**
	 * Option name in WordPress options table
	 */
	private const OPTION_NAME = 'menupilot_settings';

	/**
	 * Default settings
	 *
	 * @var array<string, mixed>
	 */
	private array $defaults = array(
		// Import Settings
		'enable_url_normalization' => true,
		'unmatched_items_behavior' => 'convert_to_custom_link',
		'default_menu_name_pattern' => '{original_name}',
		'default_menu_name_pattern_custom' => '',
		// Export Settings
		'export_filename_pattern' => 'menu-{slug}-{date}-{time}',
		'export_filename_pattern_custom' => '',
	);

	/**
	 * Centralized settings fields array
	 *
	 * @var array
	 */
	private array $fields = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->add_default_options();
		$this->register_centralized_fields();
	}

	/**
	 * Get plugin settings
	 *
	 * @return array<string, mixed>
	 */
	public function get_settings(): array {
		$settings = get_option(self::OPTION_NAME, array());
		return wp_parse_args($settings, $this->defaults);
	}

	/**
	 * Get a specific option
	 *
	 * @param string $key Option key.
	 * @param mixed  $default Default value if option not found.
	 * @return mixed
	 */
	public function get_option( string $key, $default = null ) {
		$settings = $this->get_settings();
		return $settings[ $key ] ?? $default;
	}

	/**
	 * Update a specific option
	 *
	 * @param string $key Option key.
	 * @param mixed  $value Option value.
	 * @return bool
	 */
	public function update_option( string $key, $value ): bool {
		$settings = $this->get_settings();
		$settings[ $key ] = $value;
		return update_option(self::OPTION_NAME, $settings);
	}

	/**
	 * Add default options
	 *
	 * @return void
	 */
	public function add_default_options(): void {
		if ( ! get_option(self::OPTION_NAME) ) {
			add_option(self::OPTION_NAME, $this->defaults);
		}
	}

	/**
	 * Register settings
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'menupilot_settings',
			self::OPTION_NAME,
			array(
				'type' => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'show_in_rest' => false,
			)
		);
	}

	/**
	 * Sanitize settings
	 *
	 * @param mixed $input Settings input.
	 * @return array
	 */
	public function sanitize_settings( $input ): array {
		if ( ! is_array($input) ) {
			$input = array();
		}

		// Handle custom menu name pattern
		if ( isset($input['default_menu_name_pattern']) && $input['default_menu_name_pattern'] === 'custom' ) {
			if ( isset($input['default_menu_name_pattern_custom']) && ! empty($input['default_menu_name_pattern_custom']) ) {
				$input['default_menu_name_pattern'] = sanitize_text_field($input['default_menu_name_pattern_custom']);
			} else {
				// If custom is selected but no value, fall back to default
				$input['default_menu_name_pattern'] = '{original_name}';
			}
		}

		// Handle custom export filename pattern
		if ( isset($input['export_filename_pattern']) && $input['export_filename_pattern'] === 'custom' ) {
			if ( isset($input['export_filename_pattern_custom']) && ! empty($input['export_filename_pattern_custom']) ) {
				$input['export_filename_pattern'] = sanitize_text_field($input['export_filename_pattern_custom']);
			} else {
				// If custom is selected but no value, fall back to default
				$input['export_filename_pattern'] = 'menu-{slug}-{date}-{time}';
			}
		}

		$sanitized = array();
		$fields_structure = $this->get_fields_structure();

		foreach ( $fields_structure as $tab_sections ) {
			foreach ( $tab_sections as $section_fields ) {
				foreach ( $section_fields as $field ) {
					$id = $field['field_id'] ?? '';
					$type = $field['type'] ?? 'text';

					if ( ! $id || ! array_key_exists($id, $input) ) {
						continue;
					}

					$value = $input[ $id ];

					// Use custom sanitize callback if provided
					if ( ! empty($field['sanitize_callback']) && is_callable($field['sanitize_callback']) ) {
						$sanitized[ $id ] = call_user_func($field['sanitize_callback'], $value);
						continue;
					}

					// Default sanitization based on field type
					switch ( $type ) {
						case 'text':
						case 'select':
							$sanitized[ $id ] = sanitize_text_field($value);
							break;
						case 'multiselect':
							$sanitized[ $id ] = array_map('sanitize_text_field', is_array($value) ? $value : array());
							break;
						case 'checkbox':
							$sanitized[ $id ] = ( $value === '1' || $value === 1 || $value === true ) ? 1 : 0;
							break;
						case 'textarea':
							$sanitized[ $id ] = sanitize_textarea_field($value);
							break;
						case 'number':
							$sanitized[ $id ] = intval($value);
							break;
						case 'email':
							$sanitized[ $id ] = sanitize_email($value);
							break;
						case 'url':
							$sanitized[ $id ] = esc_url_raw($value);
							break;
						default:
							$sanitized[ $id ] = sanitize_text_field($value);
					}
				}
			}
		}

		// Save custom pattern values separately for display purposes
		if ( isset($input['default_menu_name_pattern_custom']) ) {
			$sanitized['default_menu_name_pattern_custom'] = sanitize_text_field($input['default_menu_name_pattern_custom']);
		}
		if ( isset($input['export_filename_pattern_custom']) ) {
			$sanitized['export_filename_pattern_custom'] = sanitize_text_field($input['export_filename_pattern_custom']);
		}

		add_settings_error(
			'menupilot_settings',
			'settings_updated',
			__('Settings saved successfully.', 'menupilot'),
			'updated'
		);

		return $sanitized;
	}

	/**
	 * Add admin menu
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_menu_page(
			__('MenuPilot', 'menupilot'),
			__('MenuPilot', 'menupilot'),
			'manage_options',
			'menupilot-settings',
			array( $this, 'render_settings_page' ),
			'dashicons-admin-generic',
			65
		);
	}

	/**
	 * Render settings page
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can('manage_options') ) {
			return;
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			<?php settings_errors('menupilot_settings'); ?>
			<form action="options.php" method="post">
				<?php
				settings_fields('menupilot_settings');
				do_settings_sections('menupilot_settings');
				submit_button(__('Save Settings', 'menupilot'));
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Collect and organize all settings fields via filter
	 *
	 * @return void
	 */
	public function register_centralized_fields(): void {
		$fields = array();
		
		/**
		 * Filter to add settings fields
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields Settings fields array
		 */
		$fields = apply_filters('menupilot_settings_fields', $fields);
		
		$this->fields = $this->organize_fields($fields);
	}

	/**
	 * Organize fields by tab, section, and priority
	 *
	 * @param array $fields Fields array.
	 * @return array
	 */
	private function organize_fields( array $fields ): array {
		$organized = array();
		
		foreach ( $fields as $field ) {
			$tab = $field['tab'] ?? 'general';
			$section = $field['section'] ?? 'default';
			$priority = $field['priority'] ?? 10;
			$field_id = $field['field_id'] ?? '';
			
			if ( ! $field_id ) {
				continue;
			}
			
			$organized[ $tab ][ $section ][ $priority . '_' . $field_id ] = $field;
		}
		
		// Sort by tab, section, then priority
		foreach ( $organized as $tab => &$sections ) {
			foreach ( $sections as $section => &$fields ) {
				ksort($fields, SORT_NATURAL);
			}
			unset($fields);
		}
		unset($sections);
		
		return $organized;
	}

	/**
	 * Get the full, organized fields structure for rendering
	 *
	 * @return array
	 */
	public function get_fields_structure(): array {
		return $this->fields;
	}
}
