<?php
/**
 * AJAX Handler Class
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot\Admin;

use MenuPilot\Menu_Exporter;

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
	 * Constructor
	 */
	public function __construct() {
		$this->exporter = new Menu_Exporter();
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		add_action('wp_ajax_menupilot_export_menu', array( $this, 'export_menu' ));
		add_action('wp_ajax_menupilot_import_preview', array( $this, 'import_preview' ));
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
	public function import_preview(): void {
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

		// TODO: Implement import preview
		wp_send_json_error(array(
			'message' => __('Import preview is not yet implemented.', 'menupilot'),
		), 501);
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

		// TODO: Implement menu import
		wp_send_json_error(array(
			'message' => __('Menu import is not yet implemented.', 'menupilot'),
		), 501);
	}
}

