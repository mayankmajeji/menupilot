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
		// Import Settings.
		'enable_url_normalization'         => true,
		'unmatched_items_behavior'         => 'convert_to_custom_link',
		'default_menu_name_pattern'        => '{original_name}',
		'default_menu_name_pattern_custom' => '',
		// Export Settings.
		'export_filename_pattern'          => 'menu-{slug}-{date}-{time}',
		'export_filename_pattern_custom'   => '',
		// Backup.
		'backup_limit'                     => 5,
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
		$settings = get_option( self::OPTION_NAME, array() );
		return wp_parse_args( $settings, $this->defaults );
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
		$settings         = $this->get_settings();
		$settings[ $key ] = $value;
		return update_option( self::OPTION_NAME, $settings );
	}

	/**
	 * Add default options
	 *
	 * @return void
	 */
	public function add_default_options(): void {
		if ( ! get_option( self::OPTION_NAME ) ) {
			add_option( self::OPTION_NAME, $this->defaults );
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
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'show_in_rest'      => false,
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
		if ( ! is_array( $input ) ) {
			$input = array();
		}

		// Handle custom menu name pattern.
		if ( isset( $input['default_menu_name_pattern'] ) && 'custom' === $input['default_menu_name_pattern'] ) {
			if ( isset( $input['default_menu_name_pattern_custom'] ) && ! empty( $input['default_menu_name_pattern_custom'] ) ) {
				$input['default_menu_name_pattern'] = sanitize_text_field( $input['default_menu_name_pattern_custom'] );
			} else {
				// If custom is selected but no value, fall back to default.
				$input['default_menu_name_pattern'] = '{original_name}';
			}
		}

		// Handle custom export filename pattern.
		if ( isset( $input['export_filename_pattern'] ) && 'custom' === $input['export_filename_pattern'] ) {
			if ( isset( $input['export_filename_pattern_custom'] ) && ! empty( $input['export_filename_pattern_custom'] ) ) {
				$input['export_filename_pattern'] = sanitize_text_field( $input['export_filename_pattern_custom'] );
			} else {
				// If custom is selected but no value, fall back to default.
				$input['export_filename_pattern'] = 'menu-{slug}-{date}-{time}';
			}
		}

		$sanitized = array();

		// Get existing settings to preserve values not in the form.
		$existing_settings = $this->get_settings();

		// Define known settings fields and their types.
		$known_fields = array(
			'enable_url_normalization'         => 'checkbox',
			'unmatched_items_behavior'         => 'select',
			'default_menu_name_pattern'        => 'text',
			'default_menu_name_pattern_custom' => 'text',
			'export_filename_pattern'          => 'text',
			'export_filename_pattern_custom'   => 'text',
			'backup_limit'                     => 'number',
		);

		// Sanitize each known field.
		foreach ( $known_fields as $field_id => $field_type ) {
			if ( array_key_exists( $field_id, $input ) ) {
				$value = $input[ $field_id ];

				// Default sanitization based on field type.
				switch ( $field_type ) {
					case 'text':
					case 'select':
						$sanitized[ $field_id ] = sanitize_text_field( $value );
						break;
					case 'multiselect':
						$sanitized[ $field_id ] = array_map( 'sanitize_text_field', is_array( $value ) ? $value : array() );
						break;
					case 'checkbox':
						$sanitized[ $field_id ] = ( '1' === $value || 1 === $value || true === $value ) ? 1 : 0;
						break;
					case 'textarea':
						$sanitized[ $field_id ] = sanitize_textarea_field( $value );
						break;
					case 'number':
						$sanitized[ $field_id ] = intval( $value );
						if ( 'backup_limit' === $field_id ) {
							$sanitized[ $field_id ] = max( 1, min( 20, $sanitized[ $field_id ] ) );
						}
						break;
					case 'email':
						$sanitized[ $field_id ] = sanitize_email( $value );
						break;
					case 'url':
						$sanitized[ $field_id ] = esc_url_raw( $value );
						break;
					default:
						$sanitized[ $field_id ] = sanitize_text_field( $value );
				}
			} elseif ( array_key_exists( $field_id, $existing_settings ) ) {
				// Preserve existing value if not in input.
				$sanitized[ $field_id ] = $existing_settings[ $field_id ];
			}
		}

		// Also check for filter-based fields (for extensibility).
		$fields_structure = $this->get_fields_structure();
		if ( ! empty( $fields_structure ) ) {
			foreach ( $fields_structure as $tab_sections ) {
				foreach ( $tab_sections as $section_fields ) {
					foreach ( $section_fields as $field ) {
						$id   = $field['field_id'] ?? '';
						$type = $field['type'] ?? 'text';

						if ( ! $id || array_key_exists( $id, $sanitized ) ) {
							continue; // Already handled or no ID.
						}

						if ( ! array_key_exists( $id, $input ) ) {
							// Preserve existing value.
							if ( array_key_exists( $id, $existing_settings ) ) {
								$sanitized[ $id ] = $existing_settings[ $id ];
							}
							continue;
						}

						$value = $input[ $id ];

						// Use custom sanitize callback if provided.
						if ( ! empty( $field['sanitize_callback'] ) && is_callable( $field['sanitize_callback'] ) ) {
							$sanitized[ $id ] = call_user_func( $field['sanitize_callback'], $value );
							continue;
						}

						// Default sanitization based on field type.
						switch ( $type ) {
							case 'text':
							case 'select':
								$sanitized[ $id ] = sanitize_text_field( $value );
								break;
							case 'multiselect':
								$sanitized[ $id ] = array_map( 'sanitize_text_field', is_array( $value ) ? $value : array() );
								break;
							case 'checkbox':
								$sanitized[ $id ] = ( '1' === $value || 1 === $value || true === $value ) ? 1 : 0;
								break;
							case 'textarea':
								$sanitized[ $id ] = sanitize_textarea_field( $value );
								break;
							case 'number':
								$sanitized[ $id ] = intval( $value );
								break;
							case 'email':
								$sanitized[ $id ] = sanitize_email( $value );
								break;
							case 'url':
								$sanitized[ $id ] = esc_url_raw( $value );
								break;
							default:
								$sanitized[ $id ] = sanitize_text_field( $value );
						}
					}
				}
			}
		}

		// Merge with existing settings to preserve any other values.
		$sanitized = wp_parse_args( $sanitized, $existing_settings );

		add_settings_error(
			'menupilot_settings',
			'settings_updated',
			__( 'Settings saved successfully.', 'menupilot' ),
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
			__( 'MenuPilot', 'menupilot' ),
			__( 'MenuPilot', 'menupilot' ),
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
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors( 'menupilot_settings' ); ?>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'menupilot_settings' );
				do_settings_sections( 'menupilot_settings' );
				submit_button( __( 'Save Settings', 'menupilot' ) );
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
		$fields = apply_filters( 'menupilot_settings_fields', $fields );

		$this->fields = $this->organize_fields( $fields );
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
			$tab      = $field['tab'] ?? 'general';
			$section  = $field['section'] ?? 'default';
			$priority = $field['priority'] ?? 10;
			$field_id = $field['field_id'] ?? '';

			if ( ! $field_id ) {
				continue;
			}

			$organized[ $tab ][ $section ][ $priority . '_' . $field_id ] = $field;
		}

		// Sort by tab, section, then priority.
		foreach ( $organized as $tab => &$sections ) {
			foreach ( $sections as $section => &$fields ) {
				ksort( $fields, SORT_NATURAL );
			}
			unset( $fields );
		}
		unset( $sections );

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
