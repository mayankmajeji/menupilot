<?php
/**
 * REST API Mapping Class (Fragment)
 *
 * @package MenuPilot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	/**
	 * Get available content for mapping
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Fragment file, method is part of REST_Controller class
	public function get_mapping_options( \WP_REST_Request $request ) {
		$options = array(
			'posts' => array(),
			'pages' => array(),
			'taxonomies' => array(),
		);

		// Get all posts
		$posts = get_posts(array(
			'post_type' => 'post',
			'numberposts' => -1,
			'post_status' => 'publish',
			'orderby' => 'title',
			'order' => 'ASC',
		));

		foreach ( $posts as $post ) {
			$options['posts'][] = array(
				'id' => $post->ID,
				'title' => $post->post_title,
				'slug' => $post->post_name,
			);
		}

		// Get all pages
		$pages = get_posts(array(
			'post_type' => 'page',
			'numberposts' => -1,
			'post_status' => 'publish',
			'orderby' => 'title',
			'order' => 'ASC',
		));

		foreach ( $pages as $page ) {
			$options['pages'][] = array(
				'id' => $page->ID,
				'title' => $page->post_title,
				'slug' => $page->post_name,
			);
		}

		// Get all categories
		$categories = get_categories(array(
			'hide_empty' => false,
		));

		foreach ( $categories as $cat ) {
			$options['taxonomies'][] = array(
				'id' => $cat->term_id,
				'title' => $cat->name,
				'slug' => $cat->slug,
				'taxonomy' => 'category',
			);
		}

		return rest_ensure_response(array(
			'success' => true,
			'options' => $options,
		));
	}
}

