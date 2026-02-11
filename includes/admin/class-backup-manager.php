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

if ( ! defined('ABSPATH') ) {
	exit;
}

use MenuPilot\Menu_Exporter;
use MenuPilot\Menu_Importer;
use MenuPilot\Settings;

/**
 * Class Backup_Manager
 *
 * Manages menu backups stored in options table.
 */
class Backup_Manager {

	private const OPTION_KEY = 'menupilot_backups';

	/**
	 * Create a backup of a menu
	 *
	 * @param int $menu_id Menu term ID.
	 * @return array{id: string, created_at: string}|false Backup info or false on failure.
	 */
	public static function create_backup( int $menu_id ) {
		$menu = wp_get_nav_menu_object($menu_id);
		if ( ! $menu ) {
			return false;
		}

		$exporter = new Menu_Exporter();
		$export_data = $exporter->export($menu_id);
		if ( ! $export_data ) {
			return false;
		}

		$backup_id = uniqid('backup_', true);
		$created_at = current_time('c');

		$user_id = get_current_user_id();
		if ( $user_id <= 0 ) {
			$current_user = wp_get_current_user();
			$user_id = $current_user && $current_user->ID ? (int) $current_user->ID : 0;
		}
		$backup = array(
			'id'         => $backup_id,
			'menu_id'    => $menu_id,
			'menu_name'  => $menu->name,
			'created_at' => $created_at,
			'user_id'    => $user_id,
			'data'       => $export_data,
		);

		$all = get_option(self::OPTION_KEY, array());
		if ( ! is_array($all) ) {
			$all = array();
		}
		if ( ! isset($all[ $menu_id ]) || ! is_array($all[ $menu_id ]) ) {
			$all[ $menu_id ] = array();
		}

		array_unshift($all[ $menu_id ], $backup);

		$settings = new Settings();
		$limit = (int) $settings->get_option('backup_limit', 5);
		$limit = max(1, min(20, $limit));

		$all[ $menu_id ] = array_slice($all[ $menu_id ], 0, $limit);
		update_option(self::OPTION_KEY, $all);

		return array(
			'id'         => $backup_id,
			'created_at' => $created_at,
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
		$backup = self::get_backup($menu_id, $backup_id);
		if ( ! $backup ) {
			return false;
		}

		$data = $backup['data'];
		if ( ! isset($data['menu']) || ! is_array($data['menu']) ) {
			return false;
		}

		$importer = new Menu_Importer();
		return $importer->restore($menu_id, $data);
	}

	/**
	 * Get a specific backup
	 *
	 * @param int    $menu_id   Menu term ID.
	 * @param string $backup_id Backup identifier.
	 * @return array<string,mixed>|null Backup data or null.
	 */
	public static function get_backup( int $menu_id, string $backup_id ): ?array {
		$all = get_option(self::OPTION_KEY, array());
		if ( ! is_array($all) || ! isset($all[ $menu_id ]) ) {
			return null;
		}
		foreach ( $all[ $menu_id ] as $b ) {
			if ( isset($b['id']) && $b['id'] === $backup_id ) {
				return $b;
			}
		}
		return null;
	}

	/**
	 * List backups for a menu (or all if menu_id is 0)
	 *
	 * @param int $menu_id Menu term ID. Use 0 for all backups.
	 * @return array<int, array{id: string, menu_id: int, created_at: string, menu_name: string, user_login: string, user_id: int}>
	 */
	public static function list_backups( int $menu_id = 0 ): array {
		$all = get_option(self::OPTION_KEY, array());
		if ( ! is_array($all) ) {
			return array();
		}
		$result = array();
		foreach ( $all as $mid => $backups ) {
			if ( isset($menu_id) && $menu_id > 0 && (int) $mid !== $menu_id ) {
				continue;
			}
			if ( ! is_array($backups) ) {
				continue;
			}
			foreach ( $backups as $b ) {
				$user_id = isset($b['user_id']) ? (int) $b['user_id'] : 0;
				$user_login = '';
				if ( $user_id > 0 ) {
					$user = get_userdata($user_id);
					$user_login = $user ? $user->user_login : '';
				}
				$result[] = array(
					'id'         => $b['id'] ?? '',
					'menu_id'    => (int) $mid,
					'created_at' => $b['created_at'] ?? '',
					'menu_name'  => $b['menu_name'] ?? '',
					'user_login' => $user_login,
					'user_id'    => $user_id,
				);
			}
		}
		usort($result, function ( $a, $b ) {
			return strcmp($b['created_at'], $a['created_at']);
		});
		return $result;
	}

	/**
	 * Get backup by ID across all menus
	 *
	 * @param string $backup_id Backup identifier.
	 * @return array{backup: array, menu_id: int}|null
	 */
	public static function get_backup_by_id( string $backup_id ): ?array {
		$all = get_option(self::OPTION_KEY, array());
		if ( ! is_array($all) ) {
			return null;
		}
		foreach ( $all as $menu_id => $backups ) {
			if ( ! is_array($backups) ) {
				continue;
			}
			foreach ( $backups as $b ) {
				if ( isset($b['id']) && $b['id'] === $backup_id ) {
					return array( 'backup' => $b, 'menu_id' => (int) $menu_id );
				}
			}
		}
		return null;
	}

	/**
	 * Delete a backup by ID
	 *
	 * @param string $backup_id Backup identifier.
	 * @return bool True on success.
	 */
	public static function delete_backup( string $backup_id ): bool {
		$found = self::get_backup_by_id($backup_id);
		if ( ! $found ) {
			return false;
		}
		$all = get_option(self::OPTION_KEY, array());
		$menu_id = $found['menu_id'];
		if ( ! isset($all[ $menu_id ]) || ! is_array($all[ $menu_id ]) ) {
			return false;
		}
		$all[ $menu_id ] = array_values(array_filter($all[ $menu_id ], function ( $b ) use ( $backup_id ) {
			return ( $b['id'] ?? '' ) !== $backup_id;
		}));
		if ( empty($all[ $menu_id ]) ) {
			unset($all[ $menu_id ]);
		}
		update_option(self::OPTION_KEY, $all);
		return true;
	}

	/**
	 * Delete all backups
	 *
	 * @return bool True on success.
	 */
	public static function delete_all_backups(): bool {
		update_option(self::OPTION_KEY, array());
		return true;
	}

	/**
	 * Get backup count and limit
	 *
	 * @return array{count: int, limit: int}
	 */
	public static function get_backup_stats(): array {
		$all = get_option(self::OPTION_KEY, array());
		$count = 0;
		if ( is_array($all) ) {
			foreach ( $all as $backups ) {
				$count += is_array($backups) ? count($backups) : 0;
			}
		}
		$settings = new Settings();
		$limit = (int) $settings->get_option('backup_limit', 5);
		$limit = max(1, min(20, $limit));
		return array( 'count' => $count, 'limit' => $limit );
	}

	/**
	 * Export backup as JSON-serializable data
	 *
	 * @param int    $menu_id   Menu term ID.
	 * @param string $backup_id Backup identifier.
	 * @return array<string,mixed>|null Backup export data or null.
	 */
	public static function export_backup( int $menu_id, string $backup_id ): ?array {
		$backup = self::get_backup($menu_id, $backup_id);
		if ( ! $backup || ! isset($backup['data']) ) {
			return null;
		}
		return $backup['data'];
	}

	/**
	 * Register backup meta box on nav-menus screen
	 *
	 * @return void
	 */
	public static function register_meta_box(): void {
		// Output in main content area (after Menu Settings), not in sidebar.
		add_action('admin_footer-nav-menus.php', array( self::class, 'render_backup_section' ));
	}

	/**
	 * Render backup section in main content, outside the menu form.
	 * Output in footer, then JS moves it after the form#update-nav-menu.
	 *
	 * @return void
	 */
	public static function render_backup_section(): void {
		global $pagenow;
		if ( $pagenow !== 'nav-menus.php' ) {
			return;
		}
		// Only on Edit Menus tab, not Manage Locations.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only
		if ( isset($_GET['action']) && $_GET['action'] === 'locations' ) {
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
		$menu_id = isset($nav_menu_selected_id) && is_nav_menu($nav_menu_selected_id)
			? (int) $nav_menu_selected_id
			: 0;
		$backups = self::list_backups(0);
		$stats = self::get_backup_stats();
		$nonce = wp_create_nonce('menupilot_admin');
		$settings_url = admin_url('admin.php?page=menupilot-settings&settings_tab=backup');
		?>
		<div id="menupilot-backup-section" class="menupilot-backup-section" style="display:none;">
			<div class="postbox">
				<h2 class="hndle"><?php esc_html_e('Menu backup & restore', 'menupilot'); ?></h2>
				<div class="inside">
		<div id="menupilot-backup-ui" data-menu-id="<?php echo esc_attr((string) $menu_id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">
			<div class="menupilot-backup-tabs">
				<button type="button" class="menupilot-backup-tab is-active" data-tab="backup-restore">
					<?php esc_html_e('Backup & Restore', 'menupilot'); ?>
				</button>
				<button type="button" class="menupilot-backup-tab" data-tab="import">
					<?php esc_html_e('Import', 'menupilot'); ?>
				</button>
			</div>
			<div class="menupilot-backup-tab-content" id="menupilot-backup-restore-panel">
				<p class="menupilot-backup-limit">
					<?php
					printf(
						/* translators: 1: current count, 2: limit, 3: Settings link */
						esc_html__('You have %1$s of %2$s allowed backups. Limit can be changed in %3$s.', 'menupilot'),
						'<strong>' . (int) $stats['count'] . '</strong>',
						'<strong>' . (int) $stats['limit'] . '</strong>',
						'<a href="' . esc_url($settings_url) . '">' . esc_html__('Settings', 'menupilot') . '</a>'
					);
					?>
				</p>
				<?php if ( $menu_id > 0 ) : ?>
					<p>
						<button type="button" class="button button-primary" id="menupilot-create-backup">
							<?php esc_html_e('Create Backup', 'menupilot'); ?>
						</button>
					</p>
				<?php else : ?>
					<p class="description"><?php esc_html_e('Select a menu above to create a backup.', 'menupilot'); ?></p>
				<?php endif; ?>
				<?php if ( ! empty($backups) ) : ?>
					<table class="widefat striped menupilot-backup-table">
						<thead>
							<tr>
								<th><?php esc_html_e('Menu Name', 'menupilot'); ?></th>
								<th><?php esc_html_e('Date', 'menupilot'); ?></th>
								<th><?php esc_html_e('User', 'menupilot'); ?></th>
								<th><?php esc_html_e('Actions', 'menupilot'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $backups as $b ) : ?>
								<?php
								$edit_url = admin_url('nav-menus.php?action=edit&menu=' . (int) $b['menu_id']);
								?>
								<tr data-backup-id="<?php echo esc_attr($b['id']); ?>" data-menu-id="<?php echo esc_attr((string) $b['menu_id']); ?>">
									<td>
										<a href="<?php echo esc_url($edit_url); ?>"><?php echo esc_html($b['menu_name']); ?></a>
									</td>
									<td><?php echo esc_html(gmdate('M j, Y g:i:s', strtotime($b['created_at']))); ?></td>
									<td>
										<?php
										$user_display = ! empty($b['user_login']) ? esc_html($b['user_login']) : esc_html__('—', 'menupilot');
										if ( ! empty($b['user_id']) && ! empty($b['user_login']) ) :
											?>
											<a href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . (int) $b['user_id'])); ?>"><?php echo esc_html($b['user_login']); ?></a>
										<?php else : ?>
											<span aria-hidden="true"><?php echo $user_display; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above via esc_html() ?></span>
										<?php endif; ?>
									</td>
									<td class="menupilot-backup-actions">
										<button type="button" class="button button-small menupilot-restore-backup" title="<?php esc_attr_e('Restore', 'menupilot'); ?>"><?php esc_html_e('Restore', 'menupilot'); ?></button>
										<button type="button" class="button button-small menupilot-export-backup" title="<?php esc_attr_e('Export', 'menupilot'); ?>"><?php esc_html_e('Export', 'menupilot'); ?></button>
										<button type="button" class="button button-small button-link-delete menupilot-delete-backup" title="<?php esc_attr_e('Delete', 'menupilot'); ?>"><?php esc_html_e('Delete', 'menupilot'); ?></button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p class="description"><?php esc_html_e('No backups yet.', 'menupilot'); ?></p>
				<?php endif; ?>
			</div>
			<div class="menupilot-backup-tab-content" id="menupilot-backup-import-panel" style="display:none;">
				<?php if ( $menu_id > 0 ) : ?>
					<p class="description"><?php esc_html_e('Import a JSON file to replace the current menu. You can preview and map items before importing.', 'menupilot'); ?></p>
					<form id="mp-backup-import-form" class="mp-backup-import-form" method="post" enctype="multipart/form-data">
						<div class="mp-upload-area mp-upload-area--compact">
							<input type="file" name="menu_file" id="mp-backup-menu-file" accept=".json,application/json" />
							<label for="mp-backup-menu-file" class="mp-upload-label">
								<span class="dashicons dashicons-upload"></span>
								<span class="mp-upload-text">
									<strong><?php esc_html_e('Choose a JSON file', 'menupilot'); ?></strong>
									<br>
									<span class="description"><?php esc_html_e('or drag and drop here', 'menupilot'); ?></span>
								</span>
							</label>
							<div id="mp-backup-file-info" class="mp-file-info" style="display:none;margin-top:8px;">
								<strong><?php esc_html_e('Selected:', 'menupilot'); ?></strong>
								<span id="mp-backup-file-name"></span>
							</div>
						</div>
						<p>
							<button type="submit" class="button button-primary" id="mp-backup-import-btn" disabled>
								<?php esc_html_e('Upload & Preview', 'menupilot'); ?>
							</button>
							<span class="spinner" id="mp-backup-import-spinner" style="float:none;margin:0 8px;"></span>
						</p>
					</form>
					<div id="mp-backup-import-result" style="margin-top:12px;"></div>
				<?php else : ?>
					<p class="description"><?php esc_html_e('Select a menu above to import into.', 'menupilot'); ?></p>
				<?php endif; ?>
			</div>
				</div>
				</div>
			<?php if ( ! empty($backups) ) : ?>
				<p class="menupilot-backup-footer">
					<button type="button" class="button button-link-delete" id="menupilot-delete-all-backups">
						<?php esc_html_e('Delete All', 'menupilot'); ?>
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
	 * Maybe backup before native menu save (call from admin_init)
	 *
	 * @return void
	 */
	public static function maybe_backup_before_nav_menu_save(): void {
		// Only on nav-menus.php POST with action=update. Nonce is verified by WordPress core when processing the nav menu form.
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Early hook; WordPress verifies update-nav_menu nonce later in the save flow.
		if ( ! isset($_POST['action']) || sanitize_text_field(wp_unslash((string) $_POST['action'])) !== 'update' ) {
			return;
		}
		if ( ! isset($_POST['menu']) ) {
			return;
		}

		$menu_id = absint($_POST['menu']);
		if ( $menu_id <= 0 ) {
			return;
		}

		$menu = wp_get_nav_menu_object($menu_id);
		if ( ! $menu ) {
			return;
		}

		self::create_backup($menu_id);
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}
}
