<?php
/**
 * Menu Exporter Class
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot;

/**
 * Class Menu_Exporter
 *
 * Handles exporting WordPress menus to JSON format
 */
class Menu_Exporter {

	/**
	 * Schema version
	 *
	 * @var string
	 */
	private const SCHEMA_VERSION = '1.0';

	/**
	 * Export a menu to JSON
	 *
	 * @param int $menu_id Menu term ID.
	 * @return array<string,mixed>|false Export data array or false on failure.
	 */
	public function export( int $menu_id ) {
		// Get menu object
		$menu = wp_get_nav_menu_object($menu_id);
		if ( ! $menu ) {
			return false;
		}

		// Get menu items
		$menu_items = wp_get_nav_menu_items($menu_id);
		if ( ! $menu_items || ! is_array($menu_items) ) {
			return false;
		}

		// Build export data
		$export_data = array(
			'schema_version' => self::SCHEMA_VERSION,
			'plugin'         => $this->get_plugin_info(),
			'export_context' => $this->get_export_context(),
			'menu'           => $this->build_menu_data($menu, $menu_items),
		);

		return $export_data;
	}

	/**
	 * Get plugin information
	 *
	 * @return array<string,string>
	 */
	private function get_plugin_info(): array {
		return array(
			'name'    => 'MenuPilot',
			'version' => MENUPILOT_VERSION,
		);
	}

	/**
	 * Get export context information
	 *
	 * @return array<string,mixed>
	 */
	private function get_export_context(): array {
		global $wp_version;

		$theme = wp_get_theme();
		$current_user = wp_get_current_user();

		return array(
			'site_url'    => get_site_url(),
			'wp_version'  => $wp_version,
			'theme'       => $theme->get_stylesheet(),
			'exported_at' => current_time('c'),
			'exported_by' => $current_user->user_login,
			'exported_by_id' => $current_user->ID,
		);
	}

	/**
	 * Build menu data array
	 *
	 * @param \WP_Term $menu Menu object.
	 * @param array<\WP_Post> $menu_items Array of menu items.
	 * @return array<string,mixed>
	 */
	private function build_menu_data( \WP_Term $menu, array $menu_items ): array {
		// Get theme locations assigned to this menu
		$locations = $this->get_menu_locations($menu->term_id);

		// Build items array
		$items = array();
		foreach ( $menu_items as $item ) {
			$items[] = $this->build_menu_item_data($item);
		}

		return array(
			'id'        => $menu->term_id,
			'name'      => $menu->name,
			'slug'      => $menu->slug,
			'locations' => $locations,
			'items'     => $items,
		);
	}

	/**
	 * Get theme locations for a menu
	 *
	 * @param int $menu_id Menu term ID.
	 * @return array<string>
	 */
	private function get_menu_locations( int $menu_id ): array {
		$locations = get_nav_menu_locations();
		$assigned = array();

		if ( ! is_array($locations) ) {
			return $assigned;
		}

		foreach ( $locations as $location => $assigned_menu_id ) {
			if ( $assigned_menu_id === $menu_id ) {
				$assigned[] = $location;
			}
		}

		return $assigned;
	}

	/**
	 * Build menu item data array
	 *
	 * @param \WP_Post $item Menu item post object.
	 * @return array<string,mixed>
	 */
	private function build_menu_item_data( \WP_Post $item ): array {
		// Get item meta
		$object_id = (int) get_post_meta($item->ID, '_menu_item_object_id', true);
		$object = get_post_meta($item->ID, '_menu_item_object', true);
		$type = get_post_meta($item->ID, '_menu_item_type', true);
		$url = get_post_meta($item->ID, '_menu_item_url', true);
		$target = get_post_meta($item->ID, '_menu_item_target', true);
		$xfn = get_post_meta($item->ID, '_menu_item_xfn', true);
		$classes = get_post_meta($item->ID, '_menu_item_classes', true);
		$description = get_post_meta($item->ID, '_menu_item_description', true);

		// Get slug for the referenced object
		$slug = $this->get_object_slug($type, $object, $object_id);

		// Get post status and object metadata if applicable
		$status = '';
		$object_meta = array();
		
		if ( in_array($type, array( 'post_type', 'post' ), true) && $object_id > 0 ) {
			$post = get_post($object_id);
			if ( $post ) {
				$status = $post->post_status;
				$object_meta = array(
					'post_type'  => $post->post_type,
					'post_name'  => $post->post_name,
					'post_title' => $post->post_title,
					'post_date'  => $post->post_date,
				);
			}
		} elseif ( $type === 'taxonomy' && $object_id > 0 ) {
			$term = get_term($object_id, $object);
			if ( $term && ! is_wp_error($term) ) {
				$status = 'publish';
				$object_meta = array(
					'taxonomy' => $term->taxonomy,
					'term_id'  => $term->term_id,
					'name'     => $term->name,
					'count'    => $term->count,
				);
			}
		}

		$item_data = array(
			'id'        => $item->ID,
			'parent_id' => (int) $item->menu_item_parent,
			'order'     => (int) $item->menu_order,
			'type'      => $type,
			'object'    => $object,
			'object_id' => $object_id,
			'slug'      => $slug,
			'title'     => $item->title,
			'url'       => $url ? $url : $item->url,
			'status'    => $status,
			'meta'      => array(
				'css_classes' => is_array($classes) ? $classes : array(),
				'target'      => $target,
				'rel'         => $xfn,
				'description' => $description,
			),
		);

		// Add object metadata if available
		if ( ! empty($object_meta) ) {
			$item_data['object_meta'] = $object_meta;
		}

		return $item_data;
	}

	/**
	 * Get slug for menu item object
	 *
	 * @param string $type Menu item type.
	 * @param string $object Menu item object type.
	 * @param int $object_id Menu item object ID.
	 * @return string
	 */
	private function get_object_slug( string $type, string $object, int $object_id ): string {
		if ( $type === 'custom' ) {
			return '';
		}

		if ( $type === 'post_type' && $object_id > 0 ) {
			$post = get_post($object_id);
			return $post ? $post->post_name : '';
		}

		if ( $type === 'taxonomy' && $object_id > 0 ) {
			$term = get_term($object_id, $object);
			return $term && ! is_wp_error($term) ? $term->slug : '';
		}

		return '';
	}

	/**
	 * Export menu to JSON file
	 *
	 * @param int $menu_id Menu term ID.
	 * @return string|false JSON string or false on failure.
	 */
	public function export_to_json( int $menu_id ) {
		$data = $this->export($menu_id);
		if ( ! $data ) {
			return false;
		}

		$json = wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if ( ! $json ) {
			return false;
		}

		return $json;
	}

	/**
	 * Generate filename for menu export
	 *
	 * @param int $menu_id Menu term ID.
	 * @return string
	 */
	public function generate_filename( int $menu_id ): string {
		$menu = wp_get_nav_menu_object($menu_id);
		if ( ! $menu ) {
			return 'menu-export-' . gmdate('Y-m-d-His') . '.json';
		}

		$slug = sanitize_file_name($menu->slug);
		return sprintf('menu-%s-%s.json', $slug, gmdate('Y-m-d-His'));
	}

	/**
	 * Send JSON download headers
	 *
	 * @param string $filename Filename for download.
	 * @return void
	 */
	public function send_download_headers( string $filename ): void {
		header('Content-Type: application/json; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');
	}
}

