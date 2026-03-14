<?php
/**
 * Plugin initialization and bootstrap class.
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Init
 *
 * Main plugin initialization and coordination class.
 * Implements singleton pattern for global access.
 */
class Init {


	/**
	 * Plugin settings instance
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * AJAX handler instance
	 *
	 * @var \MenuPilot\Admin\Ajax_Handler|null
	 * @phpstan-ignore-next-line
	 */
	private ?\MenuPilot\Admin\Ajax_Handler $ajax_handler = null;

	/**
	 * REST controller instance
	 *
	 * @var \MenuPilot\Rest\REST_Controller|null
	 */
	private ?\MenuPilot\Rest\REST_Controller $rest_controller = null;

	/**
	 * Plugin admin screen IDs
	 *
	 * @var array<int, string>
	 */
	private const PLUGIN_SCREEN_IDS = array(
		'toplevel_page_menupilot-settings',
		'menupilot_page_menupilot-settings',
		'menupilot_page_menupilot-export',
		'menupilot_page_menupilot-import',
		'menupilot_page_menupilot-tools',
		'menupilot_page_menupilot-help',
		'menupilot_page_menupilot-history',
		'nav-menus',
	);

	/**
	 * Admin hooks registration flag
	 *
	 * @var bool
	 */
	private static bool $admin_hooks_registered = false;

	/**
	 * Singleton instance
	 *
	 * @var Init|null
	 */
	private static ?Init $instance = null;

	/**
	 * Get the singleton instance
	 *
	 * @return Init
	 */
	public static function get_instance(): Init {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init(): void {
		// Prevent double initialization.
		if ( defined( 'MENUPILOT_INIT_DONE' ) ) {
			return;
		}
		define( 'MENUPILOT_INIT_DONE', true );

		// Load dependencies.
		$this->load_dependencies();

		// Initialize components.
		$this->settings = new Settings();

		// Initialize Column Manager.
		Column_Manager::init_default_columns();

		// Initialize AJAX handler (for settings export only).
		if ( is_admin() ) {
			require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/class-ajax-handler.php';
			$this->ajax_handler = new \MenuPilot\Admin\Ajax_Handler();
		}

		// Initialize REST API controller.
		add_action( 'rest_api_init', array( $this, 'init_rest_api' ) );

		// Initialize integrations.
		$this->init_integrations();

		// Initialize History logging.
		History::init();

		// Hook into WordPress.
		$this->init_hooks();
	}

	/**
	 * Load plugin dependencies
	 *
	 * @return void
	 */
	private function load_dependencies(): void {
		// Load common functions.
		require_once MENUPILOT_PLUGIN_DIR . 'includes/functions-common.php';
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		// Admin hooks.
		if ( is_admin() && ! self::$admin_hooks_registered ) {
			require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/class-backup-manager.php';
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_filter( 'plugin_action_links_' . MENUPILOT_PLUGIN_BASENAME, array( $this, 'add_plugin_action_links' ) );
			add_action( 'admin_init', array( $this->settings, 'register_settings' ) );
			add_action( 'admin_init', array( \MenuPilot\Admin\Backup_Manager::class, 'maybe_backup_before_nav_menu_save' ), 1 );
			add_action( 'load-nav-menus.php', array( \MenuPilot\Admin\Backup_Manager::class, 'register_meta_box' ) );
			require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/class-history-page.php';
			add_action( 'admin_post_menupilot_download_history', array( \MenuPilot\Admin\History_Page::class, 'handle_download' ) );
			add_action( 'admin_post_menupilot_clear_history', array( \MenuPilot\Admin\History_Page::class, 'handle_clear_history' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
			add_filter( 'admin_body_class', array( $this, 'add_admin_body_class' ) );

			// Initialize Tools_Page early to register form processing hooks.
			require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/class-tools-page.php';
			new \MenuPilot\Admin\Tools_Page();

			self::$admin_hooks_registered = true;
		}

		// Frontend hooks intentionally omitted — no frontend assets in this version.

		/**
		 * Fires after plugin initialization
		 *
		 * @since 1.0.0
		 *
		 * @param Init $instance Plugin instance
		 */
		do_action( 'menupilot_init', $this );
	}

	/**
	 * Enqueue admin assets
	 *
	 * @return void
	 */
	public function enqueue_admin_assets(): void {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, self::PLUGIN_SCREEN_IDS, true ) ) {
			return;
		}

		wp_enqueue_style(
			'menupilot-admin',
			MENUPILOT_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			( file_exists( MENUPILOT_PLUGIN_DIR . 'assets/css/admin.css' ) ? filemtime( MENUPILOT_PLUGIN_DIR . 'assets/css/admin.css' ) : MENUPILOT_VERSION )
		);

		wp_enqueue_script(
			'menupilot-admin',
			MENUPILOT_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			MENUPILOT_VERSION,
			true
		);

		// Enqueue admin pages script.
		wp_enqueue_script(
			'menupilot-admin-pages',
			MENUPILOT_PLUGIN_URL . 'assets/js/admin-pages.js',
			array( 'jquery', 'menupilot-admin' ),
			MENUPILOT_VERSION,
			true
		);

		$localize_data = array(
			'ajaxurl'                => admin_url( 'admin-ajax.php' ),
			'restUrl'                => rest_url( 'menupilot/v1' ),
			'nonce'                  => wp_create_nonce( 'wp_rest' ),
			'adminNonce'             => wp_create_nonce( 'menupilot_admin' ),
			'siteUrl'                => get_site_url(),
			'registeredLocations'    => get_registered_nav_menus(),
			'previewColumns'         => Column_Manager::get_columns_for_js(),
			'defaultMenuNamePattern' => $this->settings->get_option( 'default_menu_name_pattern', '{original_name}' ),
			'initFunctions'          => array(),
		);

		// Add initialization data based on current page.
		if ( 'menupilot_page_menupilot-settings' === $screen->id || 'toplevel_page_menupilot-settings' === $screen->id ) {
			// Settings page - add menu name pattern and export filename pattern init data.
			$localize_data['initFunctions']['menuNamePattern']       = array(
				'fieldId'       => 'default_menu_name_pattern',
				'customFieldId' => 'default_menu_name_pattern_custom',
			);
			$localize_data['initFunctions']['exportFilenamePattern'] = array(
				'fieldId'       => 'export_filename_pattern',
				'customFieldId' => 'export_filename_pattern_custom',
			);
		}

		if ( 'menupilot_page_menupilot-help' === $screen->id ) {
			// Help page - add FAQ accordion and copy system info init data.
			$localize_data['initFunctions']['faqAccordion'] = true;
			global $wp_version;
			$localize_data['initFunctions']['copySystemInfo'] = array(
				'pluginVer'   => defined( 'MENUPILOT_VERSION' ) ? MENUPILOT_VERSION : '1.0.0',
				'wpVersion'   => $wp_version,
				'phpVersion'  => PHP_VERSION,
				'memoryLimit' => (string) ini_get( 'memory_limit' ),
			);
		}

		wp_localize_script(
			'menupilot-admin-pages',
			'menupilot',
			$localize_data
		);
	}

	/**
	 * Add custom classes to admin body on plugin pages
	 *
	 * @param string $classes Existing admin body classes.
	 * @return string
	 */
	public function add_admin_body_class( string $classes ): string {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $classes;
		}
		$screen = get_current_screen();
		if ( ! $screen ) {
			return $classes;
		}

		if ( in_array( $screen->id, self::PLUGIN_SCREEN_IDS, true ) ) {
			$classes .= ' menupilot-admin menupilot-screen-' . sanitize_html_class( (string) $screen->id );
		}
		return $classes;
	}

	/**
	 * Add plugin action links on the Plugins list page
	 *
	 * Prepends "Menus" and "Settings" links before the default "Deactivate" link,
	 * giving the user quick access to the most common destinations.
	 *
	 * @param array<int|string,string> $links Existing action links.
	 * @return array<int|string,string>
	 */
	public function add_plugin_action_links( array $links ): array {
		$action_links = array(
			'menus'    => '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Menus', 'menupilot' ) . '</a>',
			'settings' => '<a href="' . esc_url( admin_url( 'admin.php?page=menupilot-settings' ) ) . '">' . esc_html__( 'Settings', 'menupilot' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Plugin activation
	 *
	 * @return void
	 */
	public function activate(): void {
		// Initialize settings before using them.
		$this->settings = new Settings();

		// Add default options.
		$this->settings->add_default_options();

		// Create custom tables (use $wpdb->prefix—never hardcode wp_).
		global $wpdb;
		$charset = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// History table.
		$history_table = $wpdb->prefix . 'menupilot_history';
		$history_sql   = "CREATE TABLE $history_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			action_type varchar(20) NOT NULL,
			menu_id bigint(20) unsigned DEFAULT NULL,
			menu_name varchar(255) DEFAULT NULL,
			user_id bigint(20) unsigned DEFAULT NULL,
			outcome varchar(20) NOT NULL,
			details text DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY action_type (action_type),
			KEY menu_id (menu_id),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) $charset;";
		dbDelta( $history_sql );

		// Backups table.
		$backups_table = $wpdb->prefix . 'menupilot_backups';
		$backups_sql   = "CREATE TABLE $backups_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			backup_id varchar(50) NOT NULL,
			menu_id bigint(20) unsigned NOT NULL,
			menu_name varchar(255) NOT NULL DEFAULT '',
			user_id bigint(20) unsigned DEFAULT 0,
			data longtext NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY backup_id (backup_id),
			KEY menu_id (menu_id),
			KEY created_at (created_at)
		) $charset;";
		dbDelta( $backups_sql );

		// Migrate any existing backups from wp_options to the new table.
		require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/class-backup-manager.php';
		\MenuPilot\Admin\Backup_Manager::maybe_create_table();

		/**
		 * Fires on plugin activation
		 *
		 * @since 1.0.0
		 */
		do_action( 'menupilot_activate' );
	}

	/**
	 * Plugin deactivation
	 *
	 * @return void
	 */
	public function deactivate(): void {
		/**
		 * Fires on plugin deactivation
		 *
		 * @since 1.0.0
		 */
		do_action( 'menupilot_deactivate' );
	}

	/**
	 * Add admin menu pages
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		// Main menu page (Settings).
		// Use base64-encoded SVG favicon for menu icon.
		$svg_file  = file_get_contents( MENUPILOT_PLUGIN_DIR . 'assets/images/favicon.svg' );
		$menu_icon = 'data:image/svg+xml;base64,' . base64_encode( false !== $svg_file ? $svg_file : '' );
		add_menu_page(
			__( 'MenuPilot', 'menupilot' ),
			__( 'MenuPilot', 'menupilot' ),
			'manage_options',
			'menupilot-settings',
			array( $this, 'render_settings_page' ),
			$menu_icon,
			65
		);

		// Settings submenu (1) - duplicate of main page for consistency.
		add_submenu_page(
			'menupilot-settings',
			__( 'Settings', 'menupilot' ),
			__( 'Settings', 'menupilot' ),
			'manage_options',
			'menupilot-settings',
			array( $this, 'render_settings_page' )
		);

		// Import Menu submenu (2).
		add_submenu_page(
			'menupilot-settings',
			__( 'Import Menu', 'menupilot' ),
			__( 'Import Menu', 'menupilot' ),
			'manage_options',
			'menupilot-import',
			array( $this, 'render_import_page' )
		);

		// Export Menu submenu (3).
		add_submenu_page(
			'menupilot-settings',
			__( 'Export Menu', 'menupilot' ),
			__( 'Export Menu', 'menupilot' ),
			'manage_options',
			'menupilot-export',
			array( $this, 'render_export_page' )
		);

		// History submenu (4).
		add_submenu_page(
			'menupilot-settings',
			__( 'History', 'menupilot' ),
			__( 'History', 'menupilot' ),
			'manage_options',
			'menupilot-history',
			array( $this, 'render_history_page' )
		);

		// Tools submenu (5).
		add_submenu_page(
			'menupilot-settings',
			__( 'Tools', 'menupilot' ),
			__( 'Tools', 'menupilot' ),
			'manage_options',
			'menupilot-tools',
			array( $this, 'render_tools_page' )
		);

		// Help submenu (6).
		add_submenu_page(
			'menupilot-settings',
			__( 'Help', 'menupilot' ),
			__( 'Help', 'menupilot' ),
			'manage_options',
			'menupilot-help',
			array( $this, 'render_help_page' )
		);
	}

	/**
	 * Render settings page
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/class-settings-page.php';
		$settings_page = new \MenuPilot\Admin\Settings_Page();
		$settings_page->render();
	}

	/**
	 * Render export page
	 *
	 * @return void
	 */
	public function render_export_page(): void {
		require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/class-export-page.php';
		$export_page = new \MenuPilot\Admin\Export_Page();
		$export_page->render();
	}

	/**
	 * Render import page
	 *
	 * @return void
	 */
	public function render_import_page(): void {
		require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/class-import-page.php';
		$import_page = new \MenuPilot\Admin\Import_Page();
		$import_page->render();
	}

	/**
	 * Render tools page
	 *
	 * @return void
	 */
	public function render_tools_page(): void {
		require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/class-tools-page.php';
		// Instantiate early to register hooks (form processing).
		static $tools_page = null;
		if ( null === $tools_page ) {
			$tools_page = new \MenuPilot\Admin\Tools_Page();
		}
		$tools_page->render();
	}

	/**
	 * Render help page
	 *
	 * @return void
	 */
	public function render_help_page(): void {
		require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/class-help-page.php';
		$help_page = new \MenuPilot\Admin\Help_Page();
		$help_page->render();
	}

	/**
	 * Render history page
	 *
	 * @return void
	 */
	public function render_history_page(): void {
		require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/class-history-page.php';
		$history_page = new \MenuPilot\Admin\History_Page();
		$history_page->render();
	}

	/**
	 * Initialize REST API
	 *
	 * @return void
	 */
	public function init_rest_api(): void {
		require_once MENUPILOT_PLUGIN_DIR . 'includes/rest/class-rest-controller.php';
		$this->rest_controller = new \MenuPilot\Rest\REST_Controller();
		$this->rest_controller->register_routes();
	}

	/**
	 * Initialize all integrations
	 *
	 * Override this method or use hooks to add your integrations
	 *
	 * @return void
	 */
	private function init_integrations(): void {
		/**
		 * Fires when integrations should be initialized
		 *
		 * Use this hook to register your plugin's integrations with other plugins.
		 *
		 * Example:
		 * add_action('menupilot_init_integrations', function() {
		 *     if (class_exists('WooCommerce')) {
		 *         new \MenuPilot\Integrations\WooCommerce();
		 *     }
		 * });
		 *
		 * @since 1.0.0
		 */
		do_action( 'menupilot_init_integrations' );
	}
}
