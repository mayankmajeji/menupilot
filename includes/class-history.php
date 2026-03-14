<?php
/**
 * History Class
 *
 * Logs import and export actions to a custom table.
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class History
 *
 * Handles logging of import and export actions to a custom database table.
 */
class History {

	/**
	 * Tablename (with prefix)
	 *
	 * @var string
	 */
	private static ?string $table = null;

	/**
	 * Get the history table name (use $wpdb->prefix—never hardcode wp_)
	 *
	 * @return string
	 */
	public static function get_table(): string {
		if ( null === self::$table ) {
			global $wpdb;
			self::$table = $wpdb->prefix . 'menupilot_history';
		}
		return self::$table;
	}

	/**
	 * Log an import or export action
	 *
	 * @param string      $action_type 'import' or 'export'.
	 * @param int|null    $menu_id     Menu term ID.
	 * @param string|null $menu_name Menu name.
	 * @param string      $outcome     'success' or 'failure'.
	 * @param string|null $details   Optional details.
	 * @return bool True on success.
	 */
	public static function log(
		string $action_type,
		?int $menu_id = null,
		?string $menu_name = null,
		string $outcome = 'success',
		?string $details = null
	): bool {
		if ( ! in_array( $action_type, array( 'import', 'export' ), true ) ) {
			return false;
		}
		if ( ! in_array( $outcome, array( 'success', 'failure' ), true ) ) {
			return false;
		}

		self::maybe_create_table();

		$user_id = get_current_user_id();
		$table   = self::get_table();

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table; no WordPress API for menupilot_history.
		$result = $wpdb->insert(
			$table,
			array(
				'action_type' => $action_type,
				'menu_id'     => $menu_id ? $menu_id : null,
				'menu_name'   => $menu_name ? sanitize_text_field( $menu_name ) : null,
				'user_id'     => $user_id ? $user_id : null,
				'outcome'     => $outcome,
				'details'     => $details,
			),
			array( '%s', '%d', '%s', '%d', '%s', '%s' )
		);

		return false !== $result;
	}

	/**
	 * Ensure the history table exists (for upgrades / existing installs that missed activation)
	 *
	 * @return void
	 */
	public static function maybe_create_table(): void {
		static $checked = false;
		if ( $checked ) {
			return;
		}
		$checked = true;

		global $wpdb;
		$table = self::get_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
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
		dbDelta( $sql );
	}

	/**
	 * Initialize hooks to log export and import actions
	 *
	 * @return void
	 */
	public static function init(): void {
		self::maybe_create_table();

		// Export is logged explicitly in REST export_menu and Backup_Manager::create_backup.
		add_action( 'menupilot_after_import', array( self::class, 'on_import_complete' ), 10, 3 );
		add_action( 'menupilot_import_failed', array( self::class, 'on_import_failed' ), 10, 2 );
	}

	/**
	 * Callback when export completes
	 *
	 * @param array<string,mixed> $export_data Export data.
	 * @param int                 $menu_id     Menu term ID.
	 * @return void
	 */
	public static function on_export_complete( array $export_data, int $menu_id ): void {
		$menu_name = '';
		if ( isset( $export_data['menu']['name'] ) ) {
			$menu_name = (string) $export_data['menu']['name'];
		}
		self::log( 'export', $menu_id, $menu_name, 'success' );
	}

	/**
	 * Callback when import completes successfully
	 *
	 * @param int                 $menu_id     New menu term ID.
	 * @param array<string,mixed> $import_data Import data.
	 * @param array<int,int>      $item_map    Item ID mapping.
	 * @return void
	 */
	public static function on_import_complete( int $menu_id, array $import_data, array $item_map ): void {
		$menu_name = '';
		if ( isset( $import_data['menu']['name'] ) ) {
			$menu_name = (string) $import_data['menu']['name'];
		}
		self::log( 'import', $menu_id, $menu_name, 'success' );
	}

	/**
	 * Callback when import fails (hook must be fired by importer)
	 *
	 * @param array<string,mixed> $import_data Import data.
	 * @param string              $message     Error message.
	 * @return void
	 */
	public static function on_import_failed( array $import_data, string $message ): void {
		$menu_name = '';
		if ( isset( $import_data['menu']['name'] ) ) {
			$menu_name = (string) $import_data['menu']['name'];
		}
		self::log( 'import', null, $menu_name, 'failure', $message );
	}

	/**
	 * Get paginated history entries
	 *
	 * @param int         $page       Page number (1-based).
	 * @param int         $per_page   Items per page.
	 * @param string|null $action_type Filter by 'import' or 'export'.
	 * @param int|null    $menu_id  Filter by menu ID.
	 * @param int|null    $user_id  Filter by user ID.
	 * @return array{entries: array<int, object>, total: int, pages: int}
	 */
	public static function get_entries(
		int $page = 1,
		int $per_page = 50,
		?string $action_type = null,
		?int $menu_id = null,
		?int $user_id = null
	): array {
		self::maybe_create_table();

		$per_page = min( 100, max( 1, $per_page ) );
		$page     = max( 1, $page );
		$offset   = ( $page - 1 ) * $per_page;

		global $wpdb;
		$table = self::get_table();

		$where  = array( '1=1' );
		$values = array();

		if ( $action_type && in_array( $action_type, array( 'import', 'export' ), true ) ) {
			$where[]  = 'action_type = %s';
			$values[] = $action_type;
		}
		if ( null !== $menu_id && $menu_id > 0 ) {
			$where[]  = 'menu_id = %d';
			$values[] = $menu_id;
		}
		if ( null !== $user_id && $user_id > 0 ) {
			$where[]  = 'user_id = %d';
			$values[] = $user_id;
		}

		$where_sql = implode( ' AND ', $where );

		// Get total count.
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $table from $wpdb->prefix, $where_sql uses placeholders; values passed to prepare().
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table; history is append-only, caching not practical.
		$count_sql = "SELECT COUNT(*) FROM $table WHERE $where_sql";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$total = (int) $wpdb->get_var( empty( $values ) ? $count_sql : $wpdb->prepare( $count_sql, $values ) );

		// Get entries.
		$values[] = $per_page;
		$values[] = $offset;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $table from $wpdb->prefix, $where_sql uses placeholders; values passed to prepare().
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table; history is append-only, caching not practical.
		$select_sql = "SELECT id, action_type, menu_id, menu_name, user_id, outcome, details, created_at FROM $table WHERE $where_sql ORDER BY created_at DESC LIMIT %d OFFSET %d";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$entries = $wpdb->get_results( $wpdb->prepare( $select_sql, $values ) );
		if ( ! is_array( $entries ) ) {
			$entries = array();
		}

		// Add user_login to each entry.
		foreach ( $entries as $entry ) {
			if ( ! empty( $entry->user_id ) ) {
				$user              = get_userdata( (int) $entry->user_id );
				$entry->user_login = $user ? $user->user_login : '';
			} else {
				$entry->user_login = '';
			}
		}

		$pages = $total > 0 ? (int) ceil( $total / $per_page ) : 0;

		return array(
			'entries' => $entries,
			'total'   => $total,
			'pages'   => $pages,
		);
	}

	/**
	 * Get all entries for download
	 *
	 * @param string $format 'json' or 'text'.
	 * @return array<int, object>|string JSON string or array of entries.
	 */
	public static function get_all_for_download( string $format = 'json' ) {
		$result  = self::get_entries( 1, 10000 );
		$entries = $result['entries'];

		foreach ( $entries as $entry ) {
			if ( ! empty( $entry->user_id ) ) {
				$user              = get_userdata( (int) $entry->user_id );
				$entry->user_login = $user ? $user->user_login : '';
			} else {
				$entry->user_login = '';
			}
		}

		if ( 'text' === $format ) {
			$lines = array();
			foreach ( $entries as $e ) {
				$lines[] = sprintf(
					'%s | %s | %s | %s | %s | %s',
					$e->created_at ?? '',
					$e->action_type ?? '',
					$e->menu_name ?? '',
					$e->user_login ?? '',
					$e->outcome ?? '',
					$e->details ?? ''
				);
			}
			return implode( "\n", $lines );
		}

		return $entries;
	}

	/**
	 * Delete all history entries
	 *
	 * @return bool True on success.
	 */
	public static function delete_all(): bool {
		global $wpdb;
		$table = self::get_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $table from get_table() uses $wpdb->prefix (trusted); TRUNCATE has no placeholders.
		$wpdb->query( "TRUNCATE TABLE {$table}" );
		return true;
	}
}
