<?php
/**
 * AJAX Handler Class
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot\Admin;

use MenuPilot\Menu_Exporter;
use MenuPilot\Menu_Importer;

/**
 * Class Ajax_Handler
 *
 * Handles all AJAX requests for the plugin
 */
class Ajax_Handler {

	/**
	 * Menu exporter instance
	 *
	 * @var Menu_Exporter
	 */
	private Menu_Exporter $exporter;

	/**
	 * Menu importer instance
	 *
	 * @var Menu_Importer
	 */
	private Menu_Importer $importer;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->exporter = new Menu_Exporter();
		$this->importer = new Menu_Importer();
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		add_action('wp_ajax_menupilot_export_menu', array( $this, 'export_menu' ));
		add_action('wp_ajax_menupilot_preview_import', array( $this, 'preview_import' ));
		add_action('wp_ajax_menupilot_import_menu', array( $this, 'import_menu' ));
		add_action('wp_ajax_menupilot_export_settings', array( $this, 'export_settings' ));
	}

	/**
	 * Handle menu export AJAX request
	 *
	 * @return void
	 */
	public function export_menu(): void {
		// Verify nonce
		if ( ! check_ajax_referer('menupilot_admin', 'nonce', false) ) {
			wp_send_json_error(array(
				'message' => __('Security check failed. Please refresh the page and try again.', 'menupilot'),
			), 403);
			return;
		}

		// Check capabilities
		if ( ! current_user_can('manage_options') ) {
			wp_send_json_error(array(
				'message' => __('You do not have permission to export menus.', 'menupilot'),
			), 403);
			return;
		}

		// Get menu ID
		$menu_id = isset($_POST['menu_id']) ? (int) $_POST['menu_id'] : 0;
		if ( $menu_id <= 0 ) {
			wp_send_json_error(array(
				'message' => __('Invalid menu ID provided.', 'menupilot'),
			), 400);
			return;
		}

		// Verify menu exists
		$menu = wp_get_nav_menu_object($menu_id);
		if ( ! $menu ) {
			wp_send_json_error(array(
				'message' => __('Menu not found.', 'menupilot'),
			), 404);
			return;
		}

		// Export menu
		$json = $this->exporter->export_to_json($menu_id);
		if ( ! $json ) {
			wp_send_json_error(array(
				'message' => __('Failed to export menu. The menu may be empty or corrupted.', 'menupilot'),
			), 500);
			return;
		}

		// Generate filename
		$filename = $this->exporter->generate_filename($menu_id);

		// Send success response with JSON data
		wp_send_json_success(array(
			'message' => sprintf(
				/* translators: %s: menu name */
				__('Menu "%s" exported successfully!', 'menupilot'),
				$menu->name
			),
			'json'     => $json,
			'filename' => $filename,
			'menu_name' => $menu->name,
		));
	}

	/**
	 * Handle settings export AJAX request
	 *
	 * @return void
	 */
	public function export_settings(): void {
		// Verify nonce
		if ( ! isset($_GET['_wpnonce']) || ! wp_verify_nonce((string) $_GET['_wpnonce'], 'menupilot_tools_export') ) {
			wp_die(esc_html__('Security check failed.', 'menupilot'), 403);
		}

		// Check capabilities
		if ( ! current_user_can('manage_options') ) {
			wp_die(esc_html__('You do not have permission to export settings.', 'menupilot'), 403);
		}

		// Get all plugin settings
		$settings = get_option('menupilot_settings', array());

		// Build export data
		$export_data = array(
			'schema_version' => '1.0',
			'plugin'         => array(
				'name'    => 'MenuPilot',
				'version' => MENUPILOT_VERSION,
			),
			'exported_at'    => current_time('c'),
			'settings'       => $settings,
		);

		// Generate JSON
		$json = wp_json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if ( ! $json ) {
			wp_die(esc_html__('Failed to export settings.', 'menupilot'), 500);
		}

		// Send download headers
		$filename = 'menupilot-settings-' . gmdate('Y-m-d-His') . '.json';
		header('Content-Type: application/json; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');

		echo $json;
		exit;
	}

	/**
	 * Handle import preview AJAX request
	 *
	 * @return void
	 */
	public function preview_import(): void {
		// Verify nonce
		if ( ! check_ajax_referer('menupilot_admin', 'nonce', false) ) {
			wp_send_json_error(array(
				'message' => __('Security check failed. Please refresh the page and try again.', 'menupilot'),
			), 403);
			return;
		}

		// Check capabilities
		if ( ! current_user_can('manage_options') ) {
			wp_send_json_error(array(
				'message' => __('You do not have permission to import menus.', 'menupilot'),
			), 403);
			return;
		}

		// Get JSON data
		$json_data = isset($_POST['json_data']) ? wp_unslash((string) $_POST['json_data']) : '';
		if ( empty($json_data) ) {
			wp_send_json_error(array(
				'message' => __('No import data received.', 'menupilot'),
			), 400);
			return;
		}

		// Parse JSON
		$import_data = json_decode($json_data, true);
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error(array(
				'message' => __('Invalid JSON file. Please check the file format.', 'menupilot'),
			), 400);
			return;
		}

		// Validate schema
		if ( ! isset($import_data['menu']) || ! is_array($import_data['menu']) ) {
			wp_send_json_error(array(
				'message' => __('Invalid menu data structure.', 'menupilot'),
			), 400);
			return;
		}

		// Generate preview HTML
		$html = $this->generate_preview_html($import_data);

		wp_send_json_success(array(
			'message' => __('Preview generated successfully.', 'menupilot'),
			'html'    => $html,
			'data'    => $import_data,
		));
	}

	/**
	 * Generate preview HTML for import
	 *
	 * @param array<string,mixed> $import_data Import data array.
	 * @return string
	 */
	private function generate_preview_html( array $import_data ): string {
		$menu = $import_data['menu'];
		$context = isset($import_data['export_context']) ? $import_data['export_context'] : array();
		
		$menu_name = isset($menu['name']) ? $menu['name'] : 'Untitled Menu';
		$menu_slug = isset($menu['slug']) ? $menu['slug'] : '';
		$items = isset($menu['items']) && is_array($menu['items']) ? $menu['items'] : array();
		$locations = isset($menu['locations']) && is_array($menu['locations']) ? $menu['locations'] : array();
		
		$source_url = isset($context['site_url']) ? $context['site_url'] : '';
		$exported_at = isset($context['exported_at']) ? $context['exported_at'] : '';
		
		ob_start();
		?>
			<div class="mp-card">
				<h3><?php esc_html_e('Import Preview', 'menupilot'); ?></h3>
				
				<table class="widefat">
					<tbody>
						<tr>
							<th><?php esc_html_e('Menu Name:', 'menupilot'); ?></th>
							<td><strong><?php echo esc_html($menu_name); ?></strong></td>
						</tr>
						<tr>
							<th><?php esc_html_e('Menu Slug:', 'menupilot'); ?></th>
							<td><code><?php echo esc_html($menu_slug); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e('Total Items:', 'menupilot'); ?></th>
							<td><?php echo esc_html(count($items)); ?></td>
						</tr>
						<?php if ( ! empty($source_url) ) : ?>
						<tr>
							<th><?php esc_html_e('Source Site:', 'menupilot'); ?></th>
							<td><code><?php echo esc_html($source_url); ?></code></td>
						</tr>
						<?php endif; ?>
						<tr>
							<th><?php esc_html_e('Destination Site:', 'menupilot'); ?></th>
							<td><code><?php echo esc_html(get_site_url()); ?></code></td>
						</tr>
						<?php if ( ! empty($exported_at) ) : ?>
						<tr>
							<th><?php esc_html_e('Exported At:', 'menupilot'); ?></th>
							<td><?php echo esc_html(gmdate('F j, Y g:i a', strtotime($exported_at))); ?></td>
						</tr>
						<?php endif; ?>
						<?php if ( ! empty($locations) ) : ?>
						<tr>
							<th><?php esc_html_e('Source Locations:', 'menupilot'); ?></th>
							<td><code><?php echo esc_html(implode(', ', $locations)); ?></code></td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<div class="mp-card" style="margin-top:20px;">
				<h3><?php esc_html_e('Import Configuration', 'menupilot'); ?></h3>
				<p><?php esc_html_e('Configure how this menu will be imported:', 'menupilot'); ?></p>
				
				<div id="mp-import-execute-form">
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="mp-import-menu-name"><?php esc_html_e('Menu Name:', 'menupilot'); ?></label>
							</th>
							<td>
								<input type="text" 
									id="mp-import-menu-name" 
									name="menu_name" 
									value="<?php echo esc_attr($menu_name); ?>" 
									class="regular-text" 
									required />
								<p class="description"><?php esc_html_e('Enter a name for the imported menu.', 'menupilot'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="mp-import-location"><?php esc_html_e('Assign to Location:', 'menupilot'); ?></label>
							</th>
							<td>
								<select id="mp-import-location" name="location">
									<option value=""><?php esc_html_e('— Do not assign —', 'menupilot'); ?></option>
									<?php
									$registered_locations = get_registered_nav_menus();
									foreach ( $registered_locations as $location_id => $location_name ) :
									?>
										<option value="<?php echo esc_attr($location_id); ?>">
											<?php echo esc_html($location_name); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e('Optionally assign this menu to a theme location.', 'menupilot'); ?></p>
							</td>
						</tr>
					</table>

				<input type="hidden" id="mp-import-data" name="import_data" value="" />
			</div>
		</div>

			<div class="mp-card" style="margin-top:20px;">
				<h3><?php esc_html_e('Menu Items Preview', 'menupilot'); ?></h3>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e('Title', 'menupilot'); ?></th>
							<th><?php esc_html_e('Type', 'menupilot'); ?></th>
							<th><?php esc_html_e('Object', 'menupilot'); ?></th>
							<th><?php esc_html_e('URL', 'menupilot'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $items as $item ) : ?>
							<?php
							$indent = isset($item['parent_id']) && $item['parent_id'] > 0 ? '— ' : '';
							$title = isset($item['title']) ? $item['title'] : '';
							$type = isset($item['type']) ? $item['type'] : '';
							$object = isset($item['object']) ? $item['object'] : '';
							$url = isset($item['url']) ? $item['url'] : '';
							?>
							<tr>
								<td><?php echo esc_html($indent . $title); ?></td>
								<td><code><?php echo esc_html($type); ?></code></td>
								<td><code><?php echo esc_html($object); ?></code></td>
								<td><small><?php echo esc_html($url); ?></small></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle menu import AJAX request
	 *
	 * @return void
	 */
	public function import_menu(): void {
		// Verify nonce
		if ( ! check_ajax_referer('menupilot_admin', 'nonce', false) ) {
			wp_send_json_error(array(
				'message' => __('Security check failed. Please refresh the page and try again.', 'menupilot'),
			), 403);
			return;
		}

		// Check capabilities
		if ( ! current_user_can('manage_options') ) {
			wp_send_json_error(array(
				'message' => __('You do not have permission to import menus.', 'menupilot'),
			), 403);
			return;
		}

		// Get parameters
		$menu_name = isset($_POST['menu_name']) ? sanitize_text_field(wp_unslash((string) $_POST['menu_name'])) : '';
		$location = isset($_POST['location']) ? sanitize_text_field(wp_unslash((string) $_POST['location'])) : '';
		$json_data = isset($_POST['json_data']) ? wp_unslash((string) $_POST['json_data']) : '';

		// Validate menu name
		if ( empty($menu_name) ) {
			wp_send_json_error(array(
				'message' => __('Menu name is required.', 'menupilot'),
			), 400);
			return;
		}

		// Validate JSON data
		if ( empty($json_data) ) {
			wp_send_json_error(array(
				'message' => __('No import data received.', 'menupilot'),
			), 400);
			return;
		}

		// Parse JSON
		$import_data = json_decode($json_data, true);
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error(array(
				'message' => __('Invalid import data format.', 'menupilot'),
			), 400);
			return;
		}

		// Check if menu with same name already exists
		$existing_menu = wp_get_nav_menu_object($menu_name);
		if ( $existing_menu ) {
			wp_send_json_error(array(
				'message' => sprintf(
					/* translators: %s: menu name */
					__('A menu with the name "%s" already exists. Please choose a different name.', 'menupilot'),
					$menu_name
				),
			), 409);
			return;
		}

		// Import menu
		$menu_id = $this->importer->import($import_data, $menu_name, $location);

		if ( ! $menu_id ) {
			wp_send_json_error(array(
				'message' => __('Failed to import menu. Please check the import data and try again.', 'menupilot'),
			), 500);
			return;
		}

		// Build success message
		$message = sprintf(
			/* translators: %s: menu name */
			__('Menu "%s" imported successfully!', 'menupilot'),
			$menu_name
		);

		if ( ! empty($location) ) {
			$registered_locations = get_registered_nav_menus();
			$location_name = isset($registered_locations[ $location ]) ? $registered_locations[ $location ] : $location;
			$message .= ' ' . sprintf(
				/* translators: %s: theme location name */
				__('It has been assigned to the "%s" location.', 'menupilot'),
				$location_name
			);
		}

		wp_send_json_success(array(
			'message' => $message,
			'menu_id' => $menu_id,
			'edit_url' => admin_url('nav-menus.php?action=edit&menu=' . $menu_id),
		));
	}
}

