<?php
/**
 * Settings Page Class
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot\Admin;

/**
 * Class Settings_Page
 *
 * Handles the settings admin page for global plugin settings
 */
class Settings_Page {

	/**
	 * Render the settings page
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can('manage_options') ) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'menupilot'));
		}

		?>
		<div class="menupilot-page menupilot-page--settings">
			<?php require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/header.php'; ?>
			<div class="menupilot-body">
				<?php
				$mp_title = __('Settings', 'menupilot');
				$mp_desc  = __('Configure global MenuPilot settings.', 'menupilot');
				require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/body-header.php';
				?>

				<?php settings_errors('menupilot_settings'); ?>

				<div class="mp-content">
					<div class="mp-content-inner">
						<div class="mp-card">
							<h2><?php esc_html_e('Global Settings', 'menupilot'); ?></h2>
							<p><?php esc_html_e('Configure global settings for MenuPilot. More settings will be available in future updates.', 'menupilot'); ?></p>
							
							<form method="post" action="options.php">
								<?php
								settings_fields('menupilot_settings');
								do_settings_sections('menupilot_settings');
								submit_button(__('Save Settings', 'menupilot'));
								?>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

