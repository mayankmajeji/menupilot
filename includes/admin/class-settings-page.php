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
 * Handles the settings admin page (Export Menu & Import Menu tabs)
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

		// Define tabs
		$tabs = array(
			'export' => __('Export Menu', 'menupilot'),
			'import' => __('Import Menu', 'menupilot'),
		);

		// Get current tab
		$current_tab = isset($_GET['settings_tab']) ? (string) $_GET['settings_tab'] : 'export';
		if ( ! array_key_exists($current_tab, $tabs) ) {
			$current_tab = 'export';
		}

		// Get all registered menus for export tab
		$menus = wp_get_nav_menus();

		?>
		<div class="menupilot-page menupilot-page--settings">
			<?php require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/header.php'; ?>
			<div class="menupilot-body">
				<?php
				$mp_title = __('Settings', 'menupilot');
				$mp_desc  = __('Export and import WordPress navigation menus.', 'menupilot');
				require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/body-header.php';
				?>

				<?php settings_errors('menupilot_settings'); ?>

				<div class="mp-2col">
					<aside class="mp-vtabs">
						<?php foreach ( $tabs as $tab_id => $tab_label ) : ?>
							<?php
							$icon_partial = $tab_id === 'export' ? 'export-icon.php' : 'import-icon.php';
							$icon_path = MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/icons/' . $icon_partial;
							?>
							<a class="mp-vtab <?php echo $current_tab === $tab_id ? 'is-active' : ''; ?>"
								href="<?php echo esc_url(admin_url('admin.php?page=menupilot-settings&settings_tab=' . urlencode($tab_id))); ?>">
								<span class="mp-vtab-icon">
									<?php
									if ( file_exists($icon_path) ) {
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
							<?php if ( $current_tab === 'export' ) : ?>
								<!-- Export Tab Content -->
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

							<?php elseif ( $current_tab === 'import' ) : ?>
								<!-- Import Tab Content -->
								<div class="mp-card">
									<h2><?php esc_html_e('Import Menu', 'menupilot'); ?></h2>
									<p><?php esc_html_e('Upload a JSON file to import a menu. You will be able to preview and map items before importing.', 'menupilot'); ?></p>
									
									<form id="mp-import-form" method="post" enctype="multipart/form-data">
										<?php wp_nonce_field('menupilot_import', 'menupilot_import_nonce'); ?>
										
										<div class="mp-upload-area">
											<input type="file" name="menu_file" id="mp-menu-file" accept=".json,application/json" required />
											<label for="mp-menu-file" class="mp-upload-label">
												<span class="dashicons dashicons-upload" style="font-size:48px;width:48px;height:48px;"></span>
												<span class="mp-upload-text">
													<strong><?php esc_html_e('Choose a JSON file', 'menupilot'); ?></strong>
													<br>
													<span class="description"><?php esc_html_e('or drag and drop here', 'menupilot'); ?></span>
												</span>
											</label>
											<div id="mp-file-info" style="display:none;margin-top:15px;">
												<strong><?php esc_html_e('Selected file:', 'menupilot'); ?></strong>
												<span id="mp-file-name"></span>
											</div>
										</div>

										<p class="submit">
											<button type="submit" class="button button-primary button-hero" id="mp-import-btn" disabled>
												<?php esc_html_e('Upload & Preview', 'menupilot'); ?>
											</button>
											<span class="spinner" id="mp-import-spinner" style="float:none;margin:0 10px;"></span>
										</p>
									</form>

									<div id="mp-import-preview" style="display:none;margin-top:30px;">
										<!-- Preview will be loaded here via AJAX -->
									</div>

									<div id="mp-import-result" style="margin-top:20px;"></div>
								</div>
								
								<div class="mp-card" style="margin-top:30px;">
									<h3><?php esc_html_e('Import Instructions', 'menupilot'); ?></h3>
									<ol>
										<li><?php esc_html_e('Upload a menu JSON file exported from MenuPilot', 'menupilot'); ?></li>
										<li><?php esc_html_e('Review the preview and mapping suggestions', 'menupilot'); ?></li>
										<li><?php esc_html_e('Adjust mappings if needed for unmatched items', 'menupilot'); ?></li>
										<li><?php esc_html_e('Enter a name for the imported menu', 'menupilot'); ?></li>
										<li><?php esc_html_e('Optionally assign to a theme location', 'menupilot'); ?></li>
										<li><?php esc_html_e('Click "Import Menu" to complete the process', 'menupilot'); ?></li>
									</ol>
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

