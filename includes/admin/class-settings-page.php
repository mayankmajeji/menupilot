<?php
declare(strict_types=1);

namespace MenuPilot\Admin;

/**
 * Settings Page Class
 *
 * @package MenuPilot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MenuPilot\Settings;

/**
 * Class Settings_Page
 *
 * Handles the settings admin page for global plugin settings
 */
class Settings_Page
{

	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Whether settings have been registered
	 *
	 * @var bool
	 */
	private static bool $settings_registered = false;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->settings = new Settings();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 *
	 * @return void
	 */
	private function init_hooks(): void
	{
		add_action('admin_init', array($this, 'register_settings'));
	}

	/**
	 * Register settings sections and fields
	 *
	 * @return void
	 */
	public function register_settings(): void
	{
		// Prevent double registration
		if (self::$settings_registered) {
			return;
		}

		// Register the setting
		$this->settings->register_settings();

		// Import Settings Tab
		add_settings_section(
			'menupilot_import_settings',
			__('Import Settings', 'menupilot'),
			array($this, 'render_import_section_description'),
			'menupilot_settings'
		);

		// URL Normalization
		add_settings_field(
			'enable_url_normalization',
			__('URL Normalization', 'menupilot'),
			array($this, 'render_url_normalization_field'),
			'menupilot_settings',
			'menupilot_import_settings'
		);

		// Default Behavior for Unmatched Items
		add_settings_field(
			'unmatched_items_behavior',
			__('Default Behavior for Unmatched Items', 'menupilot'),
			array($this, 'render_unmatched_items_field'),
			'menupilot_settings',
			'menupilot_import_settings'
		);

		// Default Menu Name Pattern
		add_settings_field(
			'default_menu_name_pattern',
			__('Default Menu Name Pattern', 'menupilot'),
			array($this, 'render_menu_name_pattern_field'),
			'menupilot_settings',
			'menupilot_import_settings'
		);

		// Export Settings Tab
		add_settings_section(
			'menupilot_export_settings',
			__('Export Settings', 'menupilot'),
			array($this, 'render_export_section_description'),
			'menupilot_settings'
		);

		// Export Filename Pattern
		add_settings_field(
			'export_filename_pattern',
			__('Export Filename Pattern', 'menupilot'),
			array($this, 'render_export_filename_field'),
			'menupilot_settings',
			'menupilot_export_settings'
		);

		// Backup Section
		add_settings_section(
			'menupilot_backup_settings',
			__('Backup', 'menupilot'),
			array($this, 'render_backup_section_description'),
			'menupilot_settings'
		);

		add_settings_field(
			'backup_limit',
			__('Maximum Backups per Menu', 'menupilot'),
			array($this, 'render_backup_limit_field'),
			'menupilot_settings',
			'menupilot_backup_settings'
		);

		self::$settings_registered = true;
	}

	/**
	 * Render import section description
	 *
	 * @return void
	 */
	public function render_import_section_description(): void
	{
		echo '<p>' . esc_html__('Configure default behaviors for importing menus.', 'menupilot') . '</p>';
	}

	/**
	 * Render export section description
	 *
	 * @return void
	 */
	public function render_export_section_description(): void
	{
		echo '<p>' . esc_html__('Configure default behaviors for exporting menus.', 'menupilot') . '</p>';
	}

	/**
	 * Render backup section description
	 *
	 * @return void
	 */
	public function render_backup_section_description(): void
	{
		echo '<p>' . esc_html__('Configure backup and restore behavior.', 'menupilot') . '</p>';
	}

	/**
	 * Render backup limit field
	 *
	 * @return void
	 */
	public function render_backup_limit_field(): void
	{
		$value = (int) $this->settings->get_option('backup_limit', 5);
		$value = max(1, min(20, $value));
		$field_id = 'backup_limit';
	?>
		<div class="mp-field mp-field-type-number">
			<div class="mp-label">
				<label for="<?php echo esc_attr($field_id); ?>">
					<strong><?php esc_html_e('Maximum Backups per Menu', 'menupilot'); ?></strong>
				</label>
			</div>
			<div class="mp-option">
				<div class="mp-input">
					<input type="number" id="<?php echo esc_attr($field_id); ?>" name="menupilot_settings[<?php echo esc_attr($field_id); ?>]" value="<?php echo esc_attr((string) $value); ?>" min="1" max="20" class="small-text" />
				</div>
				<div class="mp-description">
					<p><?php esc_html_e('Maximum number of backups to keep per menu. Oldest backups are removed when the limit is exceeded. (1–20)', 'menupilot'); ?></p>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Render URL normalization field
	 *
	 * @return void
	 */
	public function render_url_normalization_field(): void
	{
		$value = $this->settings->get_option('enable_url_normalization', true);
		$field_id = 'enable_url_normalization';
?>
		<div class="mp-field mp-field-type-checkbox">
			<div class="mp-label">
				<label for="<?php echo esc_attr($field_id); ?>">
					<strong><?php esc_html_e('URL Normalization', 'menupilot'); ?></strong>
				</label>
			</div>
			<div class="mp-option">
				<div class="mp-input">
					<input type="hidden" name="menupilot_settings[<?php echo esc_attr($field_id); ?>]" value="0" />
					<label class="mp-toggle">
						<input type="checkbox" id="<?php echo esc_attr($field_id); ?>" name="menupilot_settings[<?php echo esc_attr($field_id); ?>]" value="1" <?php checked($value, true); ?> />
						<span class="mp-toggle-slider"></span>
					</label>
				</div>
				<div class="mp-description">
					<p><?php esc_html_e('When enabled, URLs from the source site will be automatically updated to match the destination site URL.', 'menupilot'); ?></p>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Render unmatched items behavior field
	 *
	 * @return void
	 */
	public function render_unmatched_items_field(): void
	{
		$value = $this->settings->get_option('unmatched_items_behavior', 'convert_to_custom_link');
		$field_id = 'unmatched_items_behavior';
	?>
		<div class="mp-field mp-field-type-select">
			<div class="mp-label">
				<label for="<?php echo esc_attr($field_id); ?>">
					<strong><?php esc_html_e('Default Behavior for Unmatched Items', 'menupilot'); ?></strong>
				</label>
			</div>
			<div class="mp-option">
				<div class="mp-input">
					<select id="<?php echo esc_attr($field_id); ?>" name="menupilot_settings[<?php echo esc_attr($field_id); ?>]">
						<option value="convert_to_custom_link" <?php selected($value, 'convert_to_custom_link'); ?>>
							<?php esc_html_e('Convert to Custom Link', 'menupilot'); ?>
						</option>
						<option value="skip_item" <?php selected($value, 'skip_item'); ?>>
							<?php esc_html_e('Skip Item', 'menupilot'); ?>
						</option>
						<option value="show_warning" <?php selected($value, 'show_warning'); ?>>
							<?php esc_html_e('Show Warning Only', 'menupilot'); ?>
						</option>
					</select>
				</div>
				<div class="mp-description">
					<p><?php esc_html_e('Choose what happens to menu items that cannot be matched to existing content during import.', 'menupilot'); ?></p>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Render menu name pattern field
	 *
	 * @return void
	 */
	public function render_menu_name_pattern_field(): void
	{
		$value = $this->settings->get_option('default_menu_name_pattern', '{original_name}');
		$field_id = 'default_menu_name_pattern';
		$custom_field_id = $field_id . '_custom';
		$custom_value = $this->settings->get_option($custom_field_id, '');
		
		// Check if current value is one of the predefined patterns
		$predefined_patterns = array(
			'{original_name}',
			'{original_name} (Imported)',
			'{original_name} - {date}',
		);
		$is_custom = ! in_array($value, $predefined_patterns, true);
		if ($is_custom) {
			// If it's a custom pattern, use it as the custom value
			if (empty($custom_value)) {
				$custom_value = $value;
			}
		}
	?>
		<div class="mp-field mp-field-type-radio">
			<div class="mp-label">
				<label for="<?php echo esc_attr($field_id); ?>">
					<strong><?php esc_html_e('Default Menu Name Pattern', 'menupilot'); ?></strong>
				</label>
			</div>
			<div class="mp-option">
				<div class="mp-input">
					<fieldset>
						<legend class="screen-reader-text"><?php esc_html_e('Menu Name Pattern', 'menupilot'); ?></legend>
						<label style="display: block; margin-bottom: 10px;">
							<input type="radio" name="menupilot_settings[<?php echo esc_attr($field_id); ?>]" value="{original_name}" <?php checked($value, '{original_name}'); ?> />
							<?php esc_html_e('Original Name', 'menupilot'); ?> <code>(e.g., "Main Menu")</code>
						</label>
						<label style="display: block; margin-bottom: 10px;">
							<input type="radio" name="menupilot_settings[<?php echo esc_attr($field_id); ?>]" value="{original_name} (Imported)" <?php checked($value, '{original_name} (Imported)'); ?> />
							<?php esc_html_e('Original Name (Imported)', 'menupilot'); ?> <code>(e.g., "Main Menu (Imported)")</code>
						</label>
						<label style="display: block; margin-bottom: 10px;">
							<input type="radio" name="menupilot_settings[<?php echo esc_attr($field_id); ?>]" value="{original_name} - {date}" <?php checked($value, '{original_name} - {date}'); ?> />
							<?php esc_html_e('Original Name - Date', 'menupilot'); ?> <code>(e.g., "Main Menu - 2026-01-14")</code>
						</label>
						<label style="display: block; margin-bottom: 10px;">
							<input type="radio" name="menupilot_settings[<?php echo esc_attr($field_id); ?>]" value="custom" id="<?php echo esc_attr($field_id); ?>_custom_radio" <?php checked($is_custom, true); ?> />
							<?php esc_html_e('Custom Pattern', 'menupilot'); ?>
							<input type="text" id="<?php echo esc_attr($custom_field_id); ?>" name="menupilot_settings[<?php echo esc_attr($custom_field_id); ?>]" value="<?php echo esc_attr($custom_value); ?>" class="regular-text" style="margin-left: 10px; width: 300px;" placeholder="<?php esc_attr_e('Enter custom pattern...', 'menupilot'); ?>" />
						</label>
					</fieldset>
				</div>
				<div class="mp-description">
					<p><?php esc_html_e('Default pattern for imported menu names. This pattern is automatically applied when you import a menu and leave the menu name field unchanged (or empty). Available placeholders: {original_name}, {date}, {time}.', 'menupilot'); ?></p>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Render export filename pattern field
	 *
	 * @return void
	 */
	public function render_export_filename_field(): void
	{
		$value = $this->settings->get_option('export_filename_pattern', 'menu-{slug}-{date}-{time}');
		$field_id = 'export_filename_pattern';
		$custom_field_id = $field_id . '_custom';
		$custom_value = $this->settings->get_option($custom_field_id, '');
		
		// Check if current value is one of the predefined patterns
		$predefined_patterns = array(
			'menu-{slug}-{date}-{time}',
			'{name}-{date}-{time}',
			'{slug}-{date}',
		);
		$is_custom = ! in_array($value, $predefined_patterns, true);
		if ($is_custom) {
			// If it's a custom pattern, use it as the custom value
			if (empty($custom_value)) {
				$custom_value = $value;
			}
		}
	?>
		<div class="mp-field mp-field-type-radio">
			<div class="mp-label">
				<label for="<?php echo esc_attr($field_id); ?>">
					<strong><?php esc_html_e('Export Filename Pattern', 'menupilot'); ?></strong>
				</label>
			</div>
			<div class="mp-option">
				<div class="mp-input">
					<fieldset>
						<legend class="screen-reader-text"><?php esc_html_e('Export Filename Pattern', 'menupilot'); ?></legend>
						<label style="display: block; margin-bottom: 10px;">
							<input type="radio" name="menupilot_settings[<?php echo esc_attr($field_id); ?>]" value="menu-{slug}-{date}-{time}" <?php checked($value, 'menu-{slug}-{date}-{time}'); ?> />
							<code>menu-{slug}-{date}-{time}</code> <?php esc_html_e('(e.g., "menu-main-menu-2026-01-14-100942")', 'menupilot'); ?>
						</label>
						<label style="display: block; margin-bottom: 10px;">
							<input type="radio" name="menupilot_settings[<?php echo esc_attr($field_id); ?>]" value="{name}-{date}-{time}" <?php checked($value, '{name}-{date}-{time}'); ?> />
							<code>{name}-{date}-{time}</code> <?php esc_html_e('(e.g., "Main Menu-2026-01-14-100942")', 'menupilot'); ?>
						</label>
						<label style="display: block; margin-bottom: 10px;">
							<input type="radio" name="menupilot_settings[<?php echo esc_attr($field_id); ?>]" value="{slug}-{date}" <?php checked($value, '{slug}-{date}'); ?> />
							<code>{slug}-{date}</code> <?php esc_html_e('(e.g., "main-menu-2026-01-14")', 'menupilot'); ?>
						</label>
						<label style="display: block; margin-bottom: 10px;">
							<input type="radio" name="menupilot_settings[<?php echo esc_attr($field_id); ?>]" value="custom" id="<?php echo esc_attr($field_id); ?>_custom_radio" <?php checked($is_custom, true); ?> />
							<?php esc_html_e('Custom Pattern', 'menupilot'); ?>
							<input type="text" id="<?php echo esc_attr($custom_field_id); ?>" name="menupilot_settings[<?php echo esc_attr($custom_field_id); ?>]" value="<?php echo esc_attr($custom_value); ?>" class="regular-text" style="margin-left: 10px; width: 300px;" placeholder="<?php esc_attr_e('Enter custom pattern...', 'menupilot'); ?>" />
						</label>
					</fieldset>
				</div>
				<div class="mp-description">
					<p><?php esc_html_e('Pattern for exported menu filenames. Available placeholders: {name}, {slug}, {date}, {time}, {site-name}.', 'menupilot'); ?></p>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Render settings fields grouped
	 *
	 * @param string $section_id Section ID.
	 * @param string $group_title Group title.
	 * @return void
	 */
	private function render_settings_group(string $section_id, string $group_title = ''): void
	{
		global $wp_settings_fields;

		if (! isset($wp_settings_fields['menupilot_settings'][$section_id])) {
			return;
		}

		$fields = $wp_settings_fields['menupilot_settings'][$section_id];

		if (empty($fields)) {
			return;
		}

		// Start field group (matching TurnstileWP structure)
		echo '<div class="mp-field-group">';
		if (! empty($group_title)) {
			echo '<div class="mp-group-title"><h2>' . esc_html($group_title) . '</h2></div>';
		}

		// Render each field (fields are already wrapped in .mp-field by their render methods)
		foreach ($fields as $field) {
			if (isset($field['callback']) && is_callable($field['callback'])) {
				call_user_func($field['callback']);
			}
		}

		echo '</div>'; // .mp-field-group
	}

	/**
	 * Render the settings page
	 *
	 * @return void
	 */
	public function render(): void
	{
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'menupilot'));
		}

		// Ensure settings are registered (in case admin_init already fired)
		if (! did_action('admin_init')) {
			add_action('admin_init', array($this, 'register_settings'));
		} else {
			$this->register_settings();
		}

		// Define tabs
		$tabs = array(
			'general' => __('General', 'menupilot'),
			'backup'  => __('Backup', 'menupilot'),
		);

		// Get current tab (default to general)
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation parameter, not a form submission
		$current_tab = isset($_GET['settings_tab']) ? sanitize_text_field(wp_unslash((string) $_GET['settings_tab'])) : 'general';
		if (! array_key_exists($current_tab, $tabs)) {
			$current_tab = 'general';
		}

	?>
		<div class="menupilot-page menupilot-page--settings">
			<?php require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/header.php'; ?>
			<?php settings_errors('menupilot_settings'); ?>
			<div class="menupilot-body">
				<?php
				$mp_title = __('Settings', 'menupilot');
				$mp_desc  = __('Configure global MenuPilot settings.', 'menupilot');
				require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/body-header.php';
				?>

				<div class="mp-2col">
					<aside class="mp-vtabs">
						<?php foreach ($tabs as $tab_id => $tab_label) : ?>
							<?php
							$icon_partial = 'settings-icon.php';
							$icon_path = MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/icons/' . $icon_partial;
							?>
							<a class="mp-vtab <?php echo $current_tab === $tab_id ? 'is-active' : ''; ?>"
								href="<?php echo esc_url(admin_url('admin.php?page=menupilot-settings&settings_tab=' . urlencode($tab_id))); ?>">
								<span class="mp-vtab-icon">
									<?php
									if (file_exists($icon_path)) {
										require $icon_path;
									}
									?>
								</span>
								<span class="mp-vtab-text"><?php echo esc_html($tab_label); ?></span>
							</a>
						<?php endforeach; ?>
					</aside>

					<section class="mp-2col-content">
						<div class="mp-toolbar">
							<button type="button" class="mp-collapse-btn" data-mp-toggle="vtabs">
								<span class="mp-collapse-icon icon-open" aria-hidden="true">
									<?php require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/icons/panel-close-icon.php'; ?>
								</span>
								<span class="mp-collapse-icon icon-close" aria-hidden="true" style="display:none;">
									<?php require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/icons/panel-open-icon.php'; ?>
								</span>
							</button>
							<div></div>
						</div>

						<div class="mp-content-inner">
							<?php if ($current_tab === 'general') : ?>
								<div class="mp-section" id="section-menupilot_settings">
									<div class="mp-sub-section">
										<form method="post" action="options.php">
											<?php settings_fields('menupilot_settings'); ?>

											<?php
											// Import Settings Group
											$this->render_settings_group('menupilot_import_settings', __('Import Settings', 'menupilot'));

											// Export Settings Group
											$this->render_settings_group('menupilot_export_settings', __('Export Settings', 'menupilot'));
											?>

											<div class="mp-actions" style="margin-top:16px;">
												<?php submit_button(__('Save Settings', 'menupilot')); ?>
											</div>
										</form>
									</div>
								</div>
							<?php elseif ($current_tab === 'backup') : ?>
								<div class="mp-section" id="section-menupilot_backup">
									<div class="mp-sub-section">
										<form method="post" action="options.php">
											<?php settings_fields('menupilot_settings'); ?>

											<?php
											$this->render_settings_group('menupilot_backup_settings', __('Backup Settings', 'menupilot'));
											?>

											<div class="mp-actions" style="margin-top:16px;">
												<?php submit_button(__('Save Settings', 'menupilot')); ?>
											</div>
										</form>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</section>
				</div>
			</div>
		</div>
<?php
	}
}
