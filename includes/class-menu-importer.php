<?php
/**
 * Menu Importer Class
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot;

/**
 * Class Menu_Importer
 *
 * Handles importing WordPress menus from JSON format
 */
class Menu_Importer {

	/**
	 * Source site URL from import data
	 *
	 * @var string
	 */
	private string $source_url = '';

	/**
	 * Destination site URL
	 *
	 * @var string
	 */
	private string $destination_url = '';

	/**
	 * Import a menu from JSON data
	 *
	 * @param array<string,mixed> $import_data Import data array.
	 * @param string $menu_name New menu name.
	 * @param string $location Optional theme location to assign.
	 * @return int|false Menu term ID on success, false on failure.
	 */
	public function import( array $import_data, string $menu_name, string $location = '' ) {
		// Allow plugins to modify or block import
		$pre_import = apply_filters('menupilot_pre_import', null, $import_data, $menu_name, $location);
		if ( $pre_import !== null ) {
			return $pre_import;
		}

		// Validate import data
		if ( ! isset($import_data['menu']) || ! is_array($import_data['menu']) ) {
			return false;
		}

		$menu_data = $import_data['menu'];
		$items = isset($menu_data['items']) && is_array($menu_data['items']) ? $menu_data['items'] : array();

		if ( empty($menu_name) ) {
			return false;
		}

		// Get source and destination URLs for normalization
		$this->source_url = isset($import_data['export_context']['site_url']) 
			? trailingslashit($import_data['export_context']['site_url']) 
			: '';
		$this->destination_url = trailingslashit(get_site_url());

		// Allow plugins to modify import data before processing
		$import_data = apply_filters('menupilot_import_data', $import_data);
		$menu_name = apply_filters('menupilot_import_menu_name', $menu_name, $import_data);

		// Fire action before import starts
		do_action('menupilot_before_import', $import_data, $menu_name, $location);

		// Create new menu
		$menu_id = wp_create_nav_menu($menu_name);
		if ( is_wp_error($menu_id) ) {
			return false;
		}

		// Import menu items
		$item_map = array(); // Old ID => New ID mapping
		$items_by_parent = $this->group_items_by_parent($items);

		// Import items level by level (parents first, then children)
		$this->import_items_recursive($menu_id, $items_by_parent, 0, 0, $item_map);

		// Assign to theme location if specified
		if ( ! empty($location) ) {
			$locations = get_theme_mod('nav_menu_locations', array());
			$locations[ $location ] = $menu_id;
			set_theme_mod('nav_menu_locations', $locations);
		}

		// Fire action after import completes
		do_action('menupilot_after_import', $menu_id, $import_data, $item_map);

		return $menu_id;
	}

	/**
	 * Group items by parent ID
	 *
	 * @param array<array<string,mixed>> $items Menu items array.
	 * @return array<int,array<array<string,mixed>>>
	 */
	private function group_items_by_parent( array $items ): array {
		$grouped = array();
		
		foreach ( $items as $item ) {
			$parent_id = isset($item['parent_id']) ? (int) $item['parent_id'] : 0;
			
			if ( ! isset($grouped[ $parent_id ]) ) {
				$grouped[ $parent_id ] = array();
			}
			
			$grouped[ $parent_id ][] = $item;
		}

		// Sort each group by order
		foreach ( $grouped as $parent_id => $group_items ) {
			usort($grouped[ $parent_id ], function ( $a, $b ) {
				$order_a = isset($a['order']) ? (int) $a['order'] : 0;
				$order_b = isset($b['order']) ? (int) $b['order'] : 0;
				return $order_a - $order_b;
			});
		}

		return $grouped;
	}

	/**
	 * Import items recursively
	 *
	 * @param int $menu_id Menu term ID.
	 * @param array<int,array<array<string,mixed>>> $items_by_parent Items grouped by parent.
	 * @param int $old_parent_id Old parent ID.
	 * @param int $new_parent_id New parent ID.
	 * @param array<int,int> $item_map Old ID => New ID mapping.
	 * @return void
	 */
	private function import_items_recursive( int $menu_id, array $items_by_parent, int $old_parent_id, int $new_parent_id, array &$item_map ): void {
		if ( ! isset($items_by_parent[ $old_parent_id ]) ) {
			return;
		}

		foreach ( $items_by_parent[ $old_parent_id ] as $item ) {
			$old_item_id = isset($item['id']) ? (int) $item['id'] : 0;
			
			// Create menu item
			$new_item_id = $this->create_menu_item($menu_id, $item, $new_parent_id);
			
			if ( $new_item_id && $old_item_id ) {
				$item_map[ $old_item_id ] = $new_item_id;
				
				// Import children
				$this->import_items_recursive($menu_id, $items_by_parent, $old_item_id, $new_item_id, $item_map);
			}
		}
	}

	/**
	 * Create a menu item
	 *
	 * @param int $menu_id Menu term ID.
	 * @param array<string,mixed> $item Item data.
	 * @param int $parent_id Parent item ID.
	 * @return int|false New menu item ID on success, false on failure.
	 */
	private function create_menu_item( int $menu_id, array $item, int $parent_id = 0 ) {
		$type = isset($item['type']) ? $item['type'] : 'custom';
		$object = isset($item['object']) ? $item['object'] : '';
		$object_id = isset($item['object_id']) ? (int) $item['object_id'] : 0;
		$title = isset($item['title']) ? $item['title'] : '';
		$url = isset($item['url']) ? $item['url'] : '';
		$slug = isset($item['slug']) ? $item['slug'] : '';

		// Check if user provided custom mapping
		if ( isset($item['custom_mapping']) && is_array($item['custom_mapping']) ) {
			$custom_type = $item['custom_mapping']['type'];
			$custom_id = (int) $item['custom_mapping']['id'];

			if ( $custom_type === 'custom' || $custom_id === 0 ) {
				// User wants to keep as custom link
				$type = 'custom';
				$object = 'custom';
				$object_id = 0;
			} elseif ( $custom_type === 'post' || $custom_type === 'page' ) {
				// User mapped to a specific post/page
				$type = 'post_type';
				$object = $custom_type;
				$object_id = $custom_id;
			} elseif ( $custom_type === 'category' ) {
				// User mapped to a category
				$type = 'taxonomy';
				$object = 'category';
				$object_id = $custom_id;
			}
		} else {
			// Try to find matching object (auto-matching)
			$matched_object_id = 0;
			if ( $type === 'post_type' && ! empty($object) && ! empty($slug) ) {
				$matched_object_id = $this->find_post_by_slug($slug, $object);
			} elseif ( $type === 'taxonomy' && ! empty($object) && ! empty($slug) ) {
				$matched_object_id = $this->find_term_by_slug($slug, $object);
			}

			// If no match found, convert to custom link
			if ( $matched_object_id === 0 && $type !== 'custom' ) {
				$type = 'custom';
				$object = 'custom';
				$object_id = 0;
			} else {
				$object_id = $matched_object_id;
			}
		}

		// Build menu item data
		$item_data = array(
			'menu-item-title' => $title,
			'menu-item-type' => $type,
			'menu-item-object' => $object,
			'menu-item-object-id' => $object_id,
			'menu-item-parent-id' => $parent_id,
			'menu-item-status' => 'publish',
		);

		// Add URL for custom links and normalize it
		if ( $type === 'custom' ) {
			// Normalize URL for both original custom links and converted items
			$url = $this->normalize_url($url);
			$item_data['menu-item-url'] = $url;
		}

		// Add meta data
		$meta = isset($item['meta']) && is_array($item['meta']) ? $item['meta'] : array();
		
		if ( isset($meta['target']) && ! empty($meta['target']) ) {
			$item_data['menu-item-target'] = $meta['target'];
		}
		
		if ( isset($meta['rel']) && ! empty($meta['rel']) ) {
			$item_data['menu-item-xfn'] = $meta['rel'];
		}
		
		if ( isset($meta['css_classes']) && is_array($meta['css_classes']) ) {
			$item_data['menu-item-classes'] = implode(' ', $meta['css_classes']);
		}
		
		if ( isset($meta['description']) && ! empty($meta['description']) ) {
			$item_data['menu-item-description'] = $meta['description'];
		}

		// Allow plugins to modify item data before creation
		$item_data = apply_filters('menupilot_import_menu_item_data', $item_data, $item, $menu_id);

		// Fire action before item creation
		do_action('menupilot_before_import_item', $item, $menu_id, $parent_id);

		// Create the menu item
		$new_item_id = wp_update_nav_menu_item($menu_id, 0, $item_data);

		if ( is_wp_error($new_item_id) ) {
			return false;
		}

		// Fire action after item creation
		do_action('menupilot_after_import_item', $new_item_id, $item, $menu_id);

		return $new_item_id;
	}

	/**
	 * Find post by slug
	 *
	 * @param string $slug Post slug.
	 * @param string $post_type Post type.
	 * @return int Post ID or 0 if not found.
	 */
	private function find_post_by_slug( string $slug, string $post_type ): int {
		$posts = get_posts(array(
			'name' => $slug,
			'post_type' => $post_type,
			'post_status' => 'publish',
			'numberposts' => 1,
		));

		return ! empty($posts) ? $posts[0]->ID : 0;
	}

	/**
	 * Find term by slug
	 *
	 * @param string $slug Term slug.
	 * @param string $taxonomy Taxonomy name.
	 * @return int Term ID or 0 if not found.
	 */
	private function find_term_by_slug( string $slug, string $taxonomy ): int {
		$term = get_term_by('slug', $slug, $taxonomy);
		return $term && ! is_wp_error($term) ? $term->term_id : 0;
	}

	/**
	 * Normalize URL to destination site
	 *
	 * @param string $url Original URL.
	 * @return string Normalized URL.
	 */
	private function normalize_url( string $url ): string {
		// Check if URL normalization is enabled
		$settings = new Settings();
		$enable_normalization = $settings->get_option('enable_url_normalization', true);
		
		if ( ! $enable_normalization ) {
			return $url;
		}

		// If URL is empty, return it as is
		if ( empty($url) ) {
			return $url;
		}

		// If no source URL or URLs are the same, no normalization needed
		if ( empty($this->source_url) || $this->source_url === $this->destination_url ) {
			return $url;
		}

		// Check if URL starts with source URL
		if ( strpos($url, $this->source_url) === 0 ) {
			// Replace source URL with destination URL
			$url = str_replace($this->source_url, $this->destination_url, $url);
		} else {
			// Try without trailing slash
			$source_no_slash = untrailingslashit($this->source_url);
			if ( strpos($url, $source_no_slash) === 0 ) {
				$url = str_replace($source_no_slash, untrailingslashit($this->destination_url), $url);
			}
		}

		return $url;
	}
}

