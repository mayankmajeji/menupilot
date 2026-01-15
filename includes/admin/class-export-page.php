<?php
/**
 * Export Page Class
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot\Admin;

/**
 * Class Export_Page
 *
 * Handles the export menu admin page
 */
class Export_Page {

	/**
	 * Render the export page
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can('manage_options') ) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'menupilot'));
		}

		// Get all registered menus
		$menus = wp_get_nav_menus();

		?>
		<div class="menupilot-page menupilot-page--export">
			<?php require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/header.php'; ?>
			<div class="menupilot-body">
				<?php
				$mp_title = __('Export Menu', 'menupilot');
				$mp_desc  = __('Select a menu to export as JSON. The export file will include all menu items, structure, and metadata.', 'menupilot');
				require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/body-header.php';
				?>

				<?php settings_errors('menupilot_settings'); ?>

				<div class="mp-2col mp-2col--no-sidebar">
					<section class="mp-2col-content">
						<div class="mp-toolbar">
							<div></div>
							<div></div>
						</div>

						<div class="mp-content-inner">
							<div class="mp-card">
							<h2><?php esc_html_e('Export Menu', 'menupilot'); ?></h2>
							<p><?php esc_html_e('Select a menu to export as JSON. The export file will include all menu items, structure, and metadata.', 'menupilot'); ?></p>
							
							<?php if ( empty($menus) ) : ?>
								<div class="notice notice-warning inline">
									<p><?php esc_html_e('No menus found. Create a menu first in Appearance → Menus.', 'menupilot'); ?></p>
								</div>
								<p>
									<a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>" class="button button-primary">
										<?php esc_html_e('Create a Menu', 'menupilot'); ?>
									</a>
								</p>
							<?php else : ?>
								<form id="mp-export-form" method="post">
									<?php wp_nonce_field('menupilot_export', 'menupilot_export_nonce'); ?>
									
									<table class="widefat fixed striped">
										<thead>
											<tr>
												<th class="check-column"><input type="radio" disabled /></th>
												<th><?php esc_html_e('Menu Name', 'menupilot'); ?></th>
												<th><?php esc_html_e('Slug', 'menupilot'); ?></th>
												<th><?php esc_html_e('Items', 'menupilot'); ?></th>
												<th><?php esc_html_e('Locations', 'menupilot'); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ( $menus as $menu ) : ?>
												<?php
												$menu_items = wp_get_nav_menu_items($menu->term_id);
												$item_count = is_array($menu_items) ? count($menu_items) : 0;
												$locations = get_nav_menu_locations();
												$assigned_locations = array();
												foreach ( $locations as $location => $menu_id ) {
													if ( $menu_id === $menu->term_id ) {
														$assigned_locations[] = $location;
													}
												}
												?>
												<tr>
													<th class="check-column">
														<input type="radio" name="menu_id" value="<?php echo esc_attr($menu->term_id); ?>" id="menu-<?php echo esc_attr($menu->term_id); ?>" />
													</th>
													<td>
														<label for="menu-<?php echo esc_attr($menu->term_id); ?>">
															<strong><?php echo esc_html($menu->name); ?></strong>
														</label>
													</td>
													<td><?php echo esc_html($menu->slug); ?></td>
													<td><?php echo esc_html($item_count); ?></td>
													<td>
														<?php if ( ! empty($assigned_locations) ) : ?>
															<code><?php echo esc_html(implode(', ', $assigned_locations)); ?></code>
														<?php else : ?>
															<span class="description"><?php esc_html_e('Not assigned', 'menupilot'); ?></span>
														<?php endif; ?>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>

									<p class="submit">
										<button type="submit" class="button button-primary button-hero" id="mp-export-btn" disabled>
											<?php esc_html_e('Export Selected Menu', 'menupilot'); ?>
										</button>
										<span class="spinner" id="mp-export-spinner" style="float:none;margin:0 10px;"></span>
									</p>
								</form>

								<div id="mp-export-result" style="margin-top:20px;"></div>
							<?php endif; ?>
							</div>
						</div>
					</section>
				</div>
			</div>
		</div>
		<?php
	}
}
