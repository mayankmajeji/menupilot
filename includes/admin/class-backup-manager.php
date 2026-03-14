<?php
/**
 * Backup Manager Class
 *
 * Handles menu backup creation, restore, list, and export.
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MenuPilot\Menu_Exporter;
use MenuPilot\Menu_Importer;
use MenuPilot\Settings;

/**
 * Class Backup_Manager
 *
 * Manages menu backups stored in the wp_menupilot_backups custom database table.
 * Backups are created automatically after each nav menu save and can also be
 * created manually. On upgrade from earlier versions, existing backups are
 * migrated from the menupilot_backups wp_options entry.
 */
class Backup_Manager {

	/**
	 * Table name (with prefix)
	 *
	 * @var string|null
	 */
	private static ?string $table = null;

	/**
	 * Get the backups table name
	 *
	 * @return string
	 */
	public static function get_table(): string {
		if ( null === self::$table ) {
			global $wpdb;
			self::$table = $wpdb->prefix . 'menupilot_backups';
		}
		return self::$table;
	}

	/**
	 * Ensure the backups table exists
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
			self::maybe_migrate_from_options();
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
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
		dbDelta( $sql );

		self::maybe_migrate_from_options();
	}

	/**
	 * Migrate existing backups from wp_options to custom table
	 *
	 * Provides backward compatibility for upgrades from versions
	 * that stored backups in the menupilot_backups option.
	 *
	 * @return void
	 */
	private static function maybe_migrate_from_options(): void {
		$old = get_option( 'menupilot_backups' );
		if ( ! is_array( $old ) || empty( $old ) ) {
			return;
		}

		global $wpdb;
		$table = self::get_table();

		foreach ( $old as $menu_id => $backups ) {
			if ( ! is_array( $backups ) ) {
				continue;
			}
			foreach ( $backups as $b ) {
				if ( ! isset( $b['id'] ) ) {
					continue;
				}
				$created_at = isset( $b['created_at'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $b['created_at'] ) ) : current_time( 'mysql', true );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table; no WordPress API.
				$wpdb->insert(
					$table,
					array(
						'backup_id'  => $b['id'],
						'menu_id'    => (int) $menu_id,
						'menu_name'  => $b['menu_name'] ?? '',
						'user_id'    => $b['user_id'] ?? 0,
						'data'       => wp_json_encode( $b['data'] ?? array() ),
						'created_at' => $created_at,
					),
					array( '%s', '%d', '%s', '%d', '%s', '%s' )
				);
			}
		}

		delete_option( 'menupilot_backups' );
	}

	/**
	 * Create a backup of a menu
	 *
	 * @param int $menu_id Menu term ID.
	 * @return array{id: string, created_at: string}|false Backup info or false on failure.
	 */
	public static function create_backup( int $menu_id ) {
		$menu = wp_get_nav_menu_object( $menu_id );
		if ( ! $menu ) {
			return false;
		}

		$exporter    = new Menu_Exporter();
		$export_data = $exporter->export( $menu_id );
		if ( ! $export_data ) {
			return false;
		}

		self::maybe_create_table();

		$backup_id        = uniqid( 'backup_', true );
		$created_at       = current_time( 'c' );
		$created_at_mysql = current_time( 'mysql', true );

		$user_id = get_current_user_id();
		if ( $user_id <= 0 ) {
			$current_user = wp_get_current_user();
			$user_id      = $current_user->ID ? (int) $current_user->ID : 0;
		}

		global $wpdb;
		$table = self::get_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table; no WordPress API.
		$result = $wpdb->insert(
			$table,
			array(
				'backup_id'  => $backup_id,
				'menu_id'    => $menu_id,
				'menu_name'  => $menu->name,
				'user_id'    => $user_id,
				'data'       => wp_json_encode( $export_data ),
				'created_at' => $created_at_mysql,
			),
			array( '%s', '%d', '%s', '%d', '%s', '%s' )
		);

		if ( false === $result ) {
			return false;
		}

		// Enforce backup limit per menu.
		$settings = new Settings();
		$limit    = (int) $settings->get_option( 'backup_limit', 5 );
		$limit    = max( 1, min( 20, $limit ) );

		self::enforce_backup_limit( $menu_id, $limit );

		return array(
			'id'         => $backup_id,
			'created_at' => $created_at,
		);
	}

	/**
	 * Enforce backup limit for a menu by deleting oldest backups
	 *
	 * @param int $menu_id Menu term ID.
	 * @param int $limit   Maximum number of backups to keep.
	 * @return void
	 */
	private static function enforce_backup_limit( int $menu_id, int $limit ): void {
		global $wpdb;
		$table = self::get_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table; $table from get_table() uses $wpdb->prefix (trusted).
		$count = (int) $wpdb->get_var(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table from $wpdb->prefix (trusted); table names cannot be parameterized.
			$wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE menu_id = %d", $menu_id )
		);

		if ( $count <= $limit ) {
			return;
		}

		$to_delete = $count - $limit;

		// Delete the oldest backups beyond the limit.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table; $table from $wpdb->prefix (trusted); no WordPress API.
		$wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table from $wpdb->prefix (trusted); table names cannot be parameterized.
				"DELETE FROM $table WHERE menu_id = %d ORDER BY created_at ASC LIMIT %d",
				$menu_id,
				$to_delete
			)
		);
	}

	/**
	 * Restore a menu from a backup
	 *
	 * @param int    $menu_id   Menu term ID to restore into.
	 * @param string $backup_id Backup identifier.
	 * @return bool True on success.
	 */
	public static function restore( int $menu_id, string $backup_id ): bool {
		$backup = self::get_backup( $menu_id, $backup_id );
		if ( ! $backup ) {
			return false;
		}

		$data = $backup['data'];
		if ( ! isset( $data['menu'] ) || ! is_array( $data['menu'] ) ) {
			return false;
		}

		$importer = new Menu_Importer();
		return $importer->restore( $menu_id, $data );
	}

	/**
	 * Get a specific backup
	 *
	 * @param int    $menu_id   Menu term ID.
	 * @param string $backup_id Backup identifier.
	 * @return array<string,mixed>|null Backup data or null.
	 */
	public static function get_backup( int $menu_id, string $backup_id ): ?array {
		self::maybe_create_table();

		global $wpdb;
		$table = self::get_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table; $table from $wpdb->prefix (trusted); no WordPress API.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table from $wpdb->prefix (trusted); table names cannot be parameterized.
				"SELECT * FROM $table WHERE menu_id = %d AND backup_id = %s",
				$menu_id,
				$backup_id
			),
			ARRAY_A
		);

		if ( ! $row ) {
			return null;
		}

		return self::format_backup_row( $row );
	}

	/**
	 * List backups for a menu (or all if menu_id is 0)
	 *
	 * @param int $menu_id Menu term ID. Use 0 for all backups.
	 * @return array<int, array{id: string, menu_id: int, created_at: string, menu_name: string, user_login: string, user_id: int}>
	 */
	public static function list_backups( int $menu_id = 0 ): array {
		self::maybe_create_table();

		global $wpdb;
		$table = self::get_table();

		if ( $menu_id > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table; $table from $wpdb->prefix (trusted); no WordPress API.
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table from $wpdb->prefix (trusted); table names cannot be parameterized.
					"SELECT backup_id, menu_id, menu_name, user_id, created_at FROM $table WHERE menu_id = %d ORDER BY created_at DESC",
					$menu_id
				),
				ARRAY_A
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table; $table from $wpdb->prefix (trusted); no WordPress API.
			$rows = $wpdb->get_results(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table from $wpdb->prefix (trusted); table names cannot be parameterized.
				"SELECT backup_id, menu_id, menu_name, user_id, created_at FROM $table ORDER BY created_at DESC",
				ARRAY_A
			);
		}

		if ( ! is_array( $rows ) ) {
			return array();
		}

		// Batch-fetch user data to avoid N+1 queries.
		$user_ids = array_values(
			array_unique(
				array_filter(
					array_column( $rows, 'user_id' ),
					fn( $id ) => (int) $id > 0
				)
			)
		);
		$user_map = array();
		if ( ! empty( $user_ids ) ) {
			$users = get_users(
				array(
					'include' => $user_ids,
					'fields'  => array( 'ID', 'user_login' ),
				)
			);
			foreach ( $users as $u ) {
				$user_map[ (int) $u->ID ] = $u->user_login;
			}
		}

		$result = array();
		foreach ( $rows as $row ) {
			$user_id    = isset( $row['user_id'] ) ? (int) $row['user_id'] : 0;
			$user_login = $user_map[ $user_id ] ?? '';
			$result[]   = array(
				'id'         => $row['backup_id'] ?? '',
				'menu_id'    => (int) ( $row['menu_id'] ?? 0 ),
				'created_at' => $row['created_at'] ?? '',
				'menu_name'  => $row['menu_name'] ?? '',
				'user_login' => $user_login,
				'user_id'    => $user_id,
			);
		}

		return $result;
	}

	/**
	 * Get backup by ID across all menus
	 *
	 * @param string $backup_id Backup identifier.
	 * @return array{backup: array<string,mixed>, menu_id: int}|null
	 */
	public static function get_backup_by_id( string $backup_id ): ?array {
		self::maybe_create_table();

		global $wpdb;
		$table = self::get_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table; $table from $wpdb->prefix (trusted); no WordPress API.
		$row = $wpdb->get_row(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table from $wpdb->prefix (trusted); table names cannot be parameterized.
			$wpdb->prepare( "SELECT * FROM $table WHERE backup_id = %s", $backup_id ),
			ARRAY_A
		);

		if ( ! $row ) {
			return null;
		}

		return array(
			'backup'  => self::format_backup_row( $row ),
			'menu_id' => (int) $row['menu_id'],
		);
	}

	/**
	 * Delete a backup by ID
	 *
	 * @param string $backup_id Backup identifier.
	 * @return bool True on success.
	 */
	public static function delete_backup( string $backup_id ): bool {
		self::maybe_create_table();

		global $wpdb;
		$table = self::get_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
		$deleted = $wpdb->delete(
			$table,
			array( 'backup_id' => $backup_id ),
			array( '%s' )
		);

		return false !== $deleted && $deleted > 0;
	}

	/**
	 * Delete all backups
	 *
	 * @return bool True on success.
	 */
	public static function delete_all_backups(): bool {
		self::maybe_create_table();

		global $wpdb;
		$table = self::get_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table; $table from $wpdb->prefix; TRUNCATE has no placeholders.
		$wpdb->query( "TRUNCATE TABLE {$table}" );
		return true;
	}

	/**
	 * Get backup count and limit
	 *
	 * @param int $menu_id Menu term ID to scope the count. 0 returns the total across all menus.
	 * @return array{count: int, limit: int}
	 */
	public static function get_backup_stats( int $menu_id = 0 ): array {
		self::maybe_create_table();

		global $wpdb;
		$table = self::get_table();

		if ( $menu_id > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table; $table from $wpdb->prefix (trusted).
			$count = (int) $wpdb->get_var(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table from $wpdb->prefix (trusted); table names cannot be parameterized.
				$wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE menu_id = %d", $menu_id )
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table; $table from $wpdb->prefix.
			$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
		}

		$settings = new Settings();
		$limit    = (int) $settings->get_option( 'backup_limit', 5 );
		$limit    = max( 1, min( 20, $limit ) );

		return array(
			'count' => $count,
			'limit' => $limit,
		);
	}

	/**
	 * Export backup as JSON-serializable data
	 *
	 * @param int    $menu_id   Menu term ID.
	 * @param string $backup_id Backup identifier.
	 * @return array<string,mixed>|null Backup export data or null.
	 */
	public static function export_backup( int $menu_id, string $backup_id ): ?array {
		$backup = self::get_backup( $menu_id, $backup_id );
		if ( ! $backup || ! isset( $backup['data'] ) ) {
			return null;
		}
		return $backup['data'];
	}

	/**
	 * Format a database row into the backup array structure
	 *
	 * @param array<string,mixed> $row Database row.
	 * @return array<string,mixed> Formatted backup.
	 */
	private static function format_backup_row( array $row ): array {
		$data = isset( $row['data'] ) ? json_decode( $row['data'], true ) : array();
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		return array(
			'id'         => $row['backup_id'] ?? '',
			'menu_id'    => (int) ( $row['menu_id'] ?? 0 ),
			'menu_name'  => $row['menu_name'] ?? '',
			'created_at' => $row['created_at'] ?? '',
			'user_id'    => (int) ( $row['user_id'] ?? 0 ),
			'data'       => $data,
		);
	}

	/**
	 * Register backup meta box on nav-menus screen
	 *
	 * @return void
	 */
	public static function register_meta_box(): void {
		// Output in main content area (after Menu Settings), not in sidebar.
		add_action( 'admin_footer-nav-menus.php', array( self::class, 'render_backup_section' ) );
	}

	/**
	 * Render backup section in main content, outside the menu form.
	 * Output in footer, then JS moves it after the form#update-nav-menu.
	 *
	 * @return void
	 */
	public static function render_backup_section(): void {
		global $pagenow;
		if ( 'nav-menus.php' !== $pagenow ) {
			return;
		}
		// Only on Edit Menus tab, not Manage Locations.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only
		if ( isset( $_GET['action'] ) && 'locations' === $_GET['action'] ) {
			return;
		}
		self::render_backup_content();
	}

	/**
	 * Render backup meta box content (shared)
	 *
	 * @return void
	 */
	private static function render_backup_content(): void {
		global $nav_menu_selected_id;
		$menu_id      = isset( $nav_menu_selected_id ) && is_nav_menu( $nav_menu_selected_id )
			? (int) $nav_menu_selected_id
			: 0;
		$backups      = self::list_backups( $menu_id );
		$stats        = self::get_backup_stats( $menu_id );
		$nonce        = wp_create_nonce( 'menupilot_admin' );
		$settings_url = admin_url( 'admin.php?page=menupilot-settings&settings_tab=backup' );
		?>
		<div id="menupilot-backup-section" class="menupilot-backup-section" style="display:none;">
			<div class="postbox">
				<h2 class="hndle"><?php esc_html_e( 'Menu backup & restore', 'menupilot' ); ?></h2>
				<div class="inside">
		<div id="menupilot-backup-ui" data-menu-id="<?php echo esc_attr( (string) $menu_id ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<div class="menupilot-backup-tabs">
				<button type="button" class="menupilot-backup-tab is-active" data-tab="backup-restore">
					<?php esc_html_e( 'Backup & Restore', 'menupilot' ); ?>
				</button>
				<button type="button" class="menupilot-backup-tab" data-tab="import">
					<?php esc_html_e( 'Import', 'menupilot' ); ?>
				</button>
			</div>
			<div class="menupilot-backup-tab-content" id="menupilot-backup-restore-panel">
				<p class="menupilot-backup-limit">
					<?php
					if ( $menu_id > 0 ) {
						printf(
							/* translators: 1: backup count for this menu, 2: per-menu limit, 3: Settings link */
							esc_html__( 'This menu has %1$s of %2$s allowed backups. Limit can be changed in %3$s.', 'menupilot' ),
							'<strong>' . (int) $stats['count'] . '</strong>',
							'<strong>' . (int) $stats['limit'] . '</strong>',
							'<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'menupilot' ) . '</a>'
						);
					} else {
						printf(
							/* translators: 1: total backup count across all menus, 2: per-menu limit, 3: Settings link */
							esc_html__( '%1$s total backups across all menus (per-menu limit: %2$s). Manage in %3$s.', 'menupilot' ),
							'<strong>' . (int) $stats['count'] . '</strong>',
							'<strong>' . (int) $stats['limit'] . '</strong>',
							'<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'menupilot' ) . '</a>'
						);
					}
					?>
				</p>
				<?php if ( $menu_id > 0 ) : ?>
					<p>
						<button type="button" class="button button-primary" id="menupilot-create-backup">
							<?php esc_html_e( 'Create Backup', 'menupilot' ); ?>
						</button>
					</p>
				<?php else : ?>
					<p class="description"><?php esc_html_e( 'Select a menu above to create a backup.', 'menupilot' ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $backups ) ) : ?>
					<table class="widefat striped menupilot-backup-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Menu Name', 'menupilot' ); ?></th>
								<th><?php esc_html_e( 'Date', 'menupilot' ); ?></th>
								<th><?php esc_html_e( 'User', 'menupilot' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'menupilot' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $backups as $b ) : ?>
								<?php
								$edit_url = admin_url( 'nav-menus.php?action=edit&menu=' . (int) $b['menu_id'] );
								?>
								<tr data-backup-id="<?php echo esc_attr( $b['id'] ); ?>" data-menu-id="<?php echo esc_attr( (string) $b['menu_id'] ); ?>">
									<td>
										<a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $b['menu_name'] ); ?></a>
									</td>
									<td><?php echo esc_html( gmdate( 'M j, Y g:i:s', strtotime( $b['created_at'] ) ?: 0 ) ); ?></td>
									<td>
										<?php
										$user_display = ! empty( $b['user_login'] ) ? esc_html( $b['user_login'] ) : esc_html__( '—', 'menupilot' );
										if ( ! empty( $b['user_id'] ) && ! empty( $b['user_login'] ) ) :
											?>
											<a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . (int) $b['user_id'] ) ); ?>"><?php echo esc_html( $b['user_login'] ); ?></a>
										<?php else : ?>
											<span aria-hidden="true"><?php echo $user_display; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above via esc_html() ?></span>
										<?php endif; ?>
									</td>
									<td class="menupilot-backup-actions">
										<button type="button" class="button button-small menupilot-restore-backup" title="<?php esc_attr_e( 'Restore', 'menupilot' ); ?>"><?php esc_html_e( 'Restore', 'menupilot' ); ?></button>
										<button type="button" class="button button-small menupilot-export-backup" title="<?php esc_attr_e( 'Export', 'menupilot' ); ?>"><?php esc_html_e( 'Export', 'menupilot' ); ?></button>
										<button type="button" class="button button-small button-link-delete menupilot-delete-backup" title="<?php esc_attr_e( 'Delete', 'menupilot' ); ?>"><?php esc_html_e( 'Delete', 'menupilot' ); ?></button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p class="description"><?php esc_html_e( 'No backups yet.', 'menupilot' ); ?></p>
				<?php endif; ?>
			</div>
			<div class="menupilot-backup-tab-content" id="menupilot-backup-import-panel" style="display:none;">
				<?php if ( $menu_id > 0 ) : ?>
					<p class="description"><?php esc_html_e( 'Import a JSON file to replace the current menu. You can preview and map items before importing.', 'menupilot' ); ?></p>
					<form id="mp-backup-import-form" class="mp-backup-import-form" method="post" enctype="multipart/form-data">
						<div class="mp-upload-area mp-upload-area--compact">
							<input type="file" name="menu_file" id="mp-backup-menu-file" accept=".json,application/json" />
							<label for="mp-backup-menu-file" class="mp-upload-label">
								<span class="dashicons dashicons-upload"></span>
								<span class="mp-upload-text">
									<strong><?php esc_html_e( 'Choose a JSON file', 'menupilot' ); ?></strong>
									<br>
									<span class="description"><?php esc_html_e( 'or drag and drop here', 'menupilot' ); ?></span>
								</span>
							</label>
							<div id="mp-backup-file-info" class="mp-file-info" style="display:none;margin-top:8px;">
								<strong><?php esc_html_e( 'Selected:', 'menupilot' ); ?></strong>
								<span id="mp-backup-file-name"></span>
							</div>
						</div>
						<p>
							<button type="submit" class="button button-primary" id="mp-backup-import-btn" disabled>
								<?php esc_html_e( 'Upload & Preview', 'menupilot' ); ?>
							</button>
							<span class="spinner" id="mp-backup-import-spinner" style="float:none;margin:0 8px;"></span>
						</p>
					</form>
					<div id="mp-backup-import-result" style="margin-top:12px;"></div>
				<?php else : ?>
					<p class="description"><?php esc_html_e( 'Select a menu above to import into.', 'menupilot' ); ?></p>
				<?php endif; ?>
			</div>
				</div>
				</div>
			<?php if ( ! empty( $backups ) ) : ?>
				<p class="menupilot-backup-footer">
					<button type="button" class="button button-link-delete" id="menupilot-delete-all-backups">
						<?php esc_html_e( 'Delete All', 'menupilot' ); ?>
					</button>
				</p>
			<?php endif; ?>
			</div>
		</div>
		<script>
		(function() {
			function menupilotMoveBackupSection() {
				var section = document.getElementById('menupilot-backup-section');
				var target = document.getElementById('update-nav-menu');
				if ( section && target && target.parentNode ) {
					target.parentNode.insertBefore(section, target.nextSibling);
					section.style.display = '';
				}
			}
			if ( document.readyState === 'loading' ) {
				document.addEventListener('DOMContentLoaded', menupilotMoveBackupSection);
			} else {
				menupilotMoveBackupSection();
			}
		})();
		</script>
		<?php
	}

	/**
	 * Detect a nav menu save and schedule an auto-backup for after processing
	 *
	 * Runs on admin_init (priority 1) to detect the POST, then defers the actual
	 * backup to admin_head (priority 999). By that point WordPress has fully
	 * processed all item updates, deletions, and additions — so the backup
	 * captures the correct saved state. admin_head fires before the backup list
	 * is rendered in admin_footer, so the new entry is visible immediately.
	 *
	 * @return void
	 */
	public static function maybe_backup_before_nav_menu_save(): void {
		// Only on nav-menus.php POST with action=update. Nonce is verified by WordPress core when processing the nav menu form.
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Early hook; WordPress verifies update-nav_menu nonce later in the save flow.
		if ( ! isset( $_POST['action'] ) || sanitize_text_field( wp_unslash( (string) $_POST['action'] ) ) !== 'update' ) {
			return;
		}
		if ( ! isset( $_POST['menu'] ) ) {
			return;
		}

		$menu_id = absint( $_POST['menu'] );
		if ( $menu_id <= 0 ) {
			return;
		}

		$menu = wp_get_nav_menu_object( $menu_id );
		if ( ! $menu ) {
			return;
		}

		// Defer to admin_head (priority 999) which fires AFTER WordPress has finished
		// processing all menu item changes (adds, updates, deletes) in the same
		// request, but BEFORE the backup section is rendered in admin_footer.
		// WordPress does not redirect after a nav menu save — it renders the page
		// in the same request — so wp_redirect never fires here.
		add_action(
			'admin_head',
			function () use ( $menu_id ): void {
				$menu = wp_get_nav_menu_object( $menu_id );
				if ( $menu ) {
					self::create_backup( $menu_id );
				}
			},
			999
		);
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}
}
