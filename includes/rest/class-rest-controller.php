<?php
/**
 * REST API Controller
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot\Rest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MenuPilot\History;
use MenuPilot\Menu_Exporter;
use MenuPilot\Menu_Importer;
use MenuPilot\Settings;
use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

/**
 * Class REST_Controller
 *
 * Handles REST API endpoints for menu import/export
 */
class REST_Controller extends WP_REST_Controller {


	/**
	 * Namespace
	 *
	 * @var string
	 */
	protected $namespace = 'menupilot/v1';

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

		// Register REST routes.
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// Block namespace index for non-admins.
		add_filter( 'rest_endpoints', array( $this, 'restrict_namespace_index' ) );
	}

	/**
	 * Register REST API routes
	 *
	 * @return void
	 */
	public function register_routes(): void {
		// Export menu endpoint.
		register_rest_route(
			$this->namespace,
			'/menus/export',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'export_menu' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
				'args'                => array(
					'menu_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && $param > 0;
						},
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// Import menu endpoint.
		register_rest_route(
			$this->namespace,
			'/menus/import',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'import_menu' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
				'args'                => array(
					'menu_name' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'menu_data' => array(
						'required' => true,
						'type'     => 'object',
					),
					'location'  => array(
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => '',
					),
				),
			)
		);

		// Restore menu (replace existing with import data).
		register_rest_route(
			$this->namespace,
			'/menus/restore',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'restore_menu' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
				'args'                => array(
					'menu_id'   => array(
						'required'          => true,
						'type'              => 'integer',
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && $param > 0;
						},
						'sanitize_callback' => 'absint',
					),
					'menu_data' => array(
						'required' => true,
						'type'     => 'object',
					),
				),
			)
		);

		// Mapping options endpoint.
		register_rest_route(
			$this->namespace,
			'/menus/mapping-options',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_mapping_options' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
			)
		);

		// History list endpoint.
		register_rest_route(
			$this->namespace,
			'/history',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_history' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
				'args'                => array(
					'page'        => array(
						'type'    => 'integer',
						'default' => 1,
						'minimum' => 1,
					),
					'per_page'    => array(
						'type'    => 'integer',
						'default' => 50,
						'minimum' => 1,
						'maximum' => 100,
					),
					'action_type' => array(
						'type' => 'string',
						'enum' => array( 'import', 'export' ),
					),
					'menu_id'     => array(
						'type' => 'integer',
					),
					'user_id'     => array(
						'type' => 'integer',
					),
				),
			)
		);

		// History download endpoint.
		register_rest_route(
			$this->namespace,
			'/history/download',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'download_history' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
				'args'                => array(
					'format' => array(
						'type'    => 'string',
						'default' => 'json',
						'enum'    => array( 'json', 'text' ),
					),
				),
			)
		);
	}

	/**
	 * Restrict namespace index to admins only
	 *
	 * @param array<string, mixed> $endpoints Registered endpoints.
	 * @return array<string, mixed>
	 */
	public function restrict_namespace_index( $endpoints ) {
		// Remove namespace index for non-admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			if ( isset( $endpoints['/menupilot/v1'] ) ) {
				unset( $endpoints['/menupilot/v1'] );
			}
		}
		return $endpoints;
	}

	/**
	 * Check if user has admin permissions
	 *
	 * @return bool|WP_Error
	 */
	public function check_admin_permissions() {
		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_unauthorized',
				__( 'Authentication required. Please log in to access this endpoint.', 'menupilot' ),
				array( 'status' => 401 )
			);
		}

		// Check if user has admin capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Access denied. Administrator privileges required.', 'menupilot' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Export menu endpoint handler
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|WP_Error
	 */
	public function export_menu( \WP_REST_Request $request ) {
		$menu_id = $request->get_param( 'menu_id' );

		// Verify menu exists.
		$menu = wp_get_nav_menu_object( $menu_id );
		if ( ! $menu ) {
			return new WP_Error(
				'menu_not_found',
				__( 'Menu not found.', 'menupilot' ),
				array( 'status' => 404 )
			);
		}

		// Export menu.
		$export_data = $this->exporter->export( $menu_id );
		if ( ! $export_data ) {
			return new WP_Error(
				'export_failed',
				__( 'Failed to export menu. The menu may be empty or corrupted.', 'menupilot' ),
				array( 'status' => 500 )
			);
		}

		// Log export to history.
		History::log(
			'export',
			$menu_id,
			$menu->name,
			'success'
		);

		// Generate filename.
		$filename = $this->exporter->generate_filename( $menu_id );

		return rest_ensure_response(
			array(
				'success'  => true,
				'data'     => $export_data,
				'filename' => $filename,
				'message'  => sprintf(
					/* translators: %s: menu name */
					__( 'Menu "%s" exported successfully.', 'menupilot' ),
					$menu->name
				),
			)
		);
	}

	/**
	 * Import menu endpoint handler
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|WP_Error
	 */
	public function import_menu( \WP_REST_Request $request ) {
		$menu_name = $request->get_param( 'menu_name' );
		$menu_data = $request->get_param( 'menu_data' );
		$location  = $request->get_param( 'location' );

		// Validate menu data.
		if ( ! is_array( $menu_data ) || ! isset( $menu_data['menu'] ) ) {
			return new WP_Error(
				'invalid_data',
				__( 'Invalid menu data format.', 'menupilot' ),
				array( 'status' => 400 )
			);
		}

		// Apply default menu name pattern if menu_name is empty or uses default.
		if ( empty( $menu_name ) || ( isset( $menu_data['menu']['name'] ) ? $menu_data['menu']['name'] : '' ) === $menu_name ) {
			$settings      = new Settings();
			$pattern       = $settings->get_option( 'default_menu_name_pattern', '{original_name}' );
			$original_name = isset( $menu_data['menu']['name'] ) ? $menu_data['menu']['name'] : __( 'Imported Menu', 'menupilot' );

			$menu_name = $this->apply_menu_name_pattern( $pattern, $original_name );
		}

		// Check if menu with same name already exists.
		$existing_menu = wp_get_nav_menu_object( $menu_name );
		if ( $existing_menu ) {
			return new WP_Error(
				'menu_exists',
				sprintf(
					/* translators: %s: menu name */
					__( 'A menu with the name "%s" already exists. Please choose a different name.', 'menupilot' ),
					$menu_name
				),
				array( 'status' => 409 )
			);
		}

		// Import menu.
		$menu_id = $this->importer->import( $menu_data, $menu_name, $location );

		if ( ! $menu_id ) {
			do_action( 'menupilot_import_failed', $menu_data, __( 'Failed to import menu. Please check the menu data and try again.', 'menupilot' ) );
			return new WP_Error(
				'import_failed',
				__( 'Failed to import menu. Please check the menu data and try again.', 'menupilot' ),
				array( 'status' => 500 )
			);
		}

		// Build success message.
		$message = sprintf(
			/* translators: %s: menu name */
			__( 'Menu "%s" imported successfully!', 'menupilot' ),
			$menu_name
		);

		if ( ! empty( $location ) ) {
			$registered_locations = get_registered_nav_menus();
			$location_name        = isset( $registered_locations[ $location ] ) ? $registered_locations[ $location ] : $location;
			$message             .= ' ' . sprintf(
				/* translators: %s: theme location name */
				__( 'It has been assigned to the "%s" location.', 'menupilot' ),
				$location_name
			);
		}

		return rest_ensure_response(
			array(
				'success'  => true,
				'menu_id'  => $menu_id,
				'message'  => $message,
				'edit_url' => admin_url( 'nav-menus.php?action=edit&menu=' . $menu_id ),
			)
		);
	}

	/**
	 * Restore menu (replace existing with import data)
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|WP_Error
	 */
	public function restore_menu( \WP_REST_Request $request ) {
		$menu_id   = $request->get_param( 'menu_id' );
		$menu_data = $request->get_param( 'menu_data' );

		if ( ! is_array( $menu_data ) || ! isset( $menu_data['menu'] ) ) {
			return new WP_Error(
				'invalid_data',
				__( 'Invalid menu data format.', 'menupilot' ),
				array( 'status' => 400 )
			);
		}

		$menu = wp_get_nav_menu_object( $menu_id );
		if ( ! $menu ) {
			return new WP_Error(
				'menu_not_found',
				__( 'Menu not found.', 'menupilot' ),
				array( 'status' => 404 )
			);
		}

		$ok = $this->importer->restore( $menu_id, $menu_data );
		if ( ! $ok ) {
			do_action( 'menupilot_import_failed', $menu_data, __( 'Failed to restore menu.', 'menupilot' ) );
			return new WP_Error(
				'restore_failed',
				__( 'Failed to restore menu. Please check the menu data and try again.', 'menupilot' ),
				array( 'status' => 500 )
			);
		}

		do_action( 'menupilot_after_import', $menu_id, $menu_data, array() );

		return rest_ensure_response(
			array(
				'success'  => true,
				'menu_id'  => $menu_id,
				'message'  => sprintf(
					/* translators: %s: menu name */
					__( 'Menu "%s" has been replaced successfully.', 'menupilot' ),
					$menu->name
				),
				'edit_url' => admin_url( 'nav-menus.php?action=edit&menu=' . $menu_id ),
			)
		);
	}

	/**
	 * Get available content for mapping
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_mapping_options( \WP_REST_Request $request ) {
		$options = array(
			'posts'      => array(),
			'pages'      => array(),
			'taxonomies' => array(),
		);

		// Get all posts.
		$posts = get_posts(
			array(
				'post_type'   => 'post',
				'numberposts' => -1,
				'post_status' => 'publish',
				'orderby'     => 'title',
				'order'       => 'ASC',
			)
		);

		foreach ( $posts as $post ) {
			$options['posts'][] = array(
				'id'    => $post->ID,
				'title' => $post->post_title,
				'slug'  => $post->post_name,
			);
		}

		// Get all pages.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'numberposts' => -1,
				'post_status' => 'publish',
				'orderby'     => 'title',
				'order'       => 'ASC',
			)
		);

		foreach ( $pages as $page ) {
			$options['pages'][] = array(
				'id'    => $page->ID,
				'title' => $page->post_title,
				'slug'  => $page->post_name,
			);
		}

		// Get all categories.
		$categories = get_categories(
			array(
				'hide_empty' => false,
			)
		);

		foreach ( $categories as $cat ) {
			$options['taxonomies'][] = array(
				'id'       => $cat->term_id,
				'title'    => $cat->name,
				'slug'     => $cat->slug,
				'taxonomy' => 'category',
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'options' => $options,
			)
		);
	}

	/**
	 * Get history entries
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_history( \WP_REST_Request $request ): \WP_REST_Response {
		$page        = (int) $request->get_param( 'page' );
		$per_page    = (int) $request->get_param( 'per_page' );
		$action_type = $request->get_param( 'action_type' );
		$menu_id     = $request->get_param( 'menu_id' );
		$user_id     = $request->get_param( 'user_id' );

		$result = History::get_entries(
			$page,
			$per_page,
			$action_type ? $action_type : null,
			null !== $menu_id ? (int) $menu_id : null,
			null !== $user_id ? (int) $user_id : null
		);

		return rest_ensure_response( $result );
	}

	/**
	 * Download history
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function download_history( \WP_REST_Request $request ): \WP_REST_Response {
		$format_raw = $request->get_param( 'format' );
		$format     = $format_raw ? $format_raw : 'json';
		$data       = History::get_all_for_download( $format );

		if ( 'text' === $format ) {
			return new \WP_REST_Response(
				$data,
				200,
				array(
					'Content-Type'        => 'text/plain; charset=utf-8',
					'Content-Disposition' => 'attachment; filename="menupilot-history-' . gmdate( 'Y-m-d' ) . '.txt"',
				)
			);
		}

		return new \WP_REST_Response(
			$data,
			200,
			array(
				'Content-Type'        => 'application/json; charset=utf-8',
				'Content-Disposition' => 'attachment; filename="menupilot-history-' . gmdate( 'Y-m-d' ) . '.json"',
			)
		);
	}

	/**
	 * Apply menu name pattern
	 *
	 * @param string $pattern Pattern string.
	 * @param string $original_name Original menu name.
	 * @return string Processed menu name.
	 */
	private function apply_menu_name_pattern( string $pattern, string $original_name ): string {
		$date = gmdate( 'Y-m-d' );
		$time = gmdate( 'His' );

		$menu_name = str_replace(
			array( '{original_name}', '{date}', '{time}' ),
			array( $original_name, $date, $time ),
			$pattern
		);

		return sanitize_text_field( $menu_name );
	}
}
