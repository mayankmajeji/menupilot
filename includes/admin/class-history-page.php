<?php
/**
 * History Page Class
 *
 * Displays import/export history logs with pagination and download.
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MenuPilot\History;

/**
 * Class History_Page
 *
 * Renders the History admin page under MenuPilot menu.
 */
class History_Page {


	/**
	 * Entries per page
	 *
	 * @var int
	 */
	private const PER_PAGE = 50;

	/**
	 * Handle history download (admin_post)
	 *
	 * @return void
	 */
	public static function handle_download(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'menupilot' ), '', array( 'response' => 403 ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified below
		$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'menupilot_download_history' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'menupilot' ), '', array( 'response' => 403 ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified above
		$format = isset( $_GET['format'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['format'] ) ) : 'json';
		$format = 'text' === $format ? 'text' : 'json';

		$data = History::get_all_for_download( $format );

		$date     = gmdate( 'Y-m-d' );
		$ext      = 'text' === $format ? 'txt' : 'json';
		$filename = 'menupilot-history-' . $date . '.' . $ext;

		if ( 'text' === $format ) {
			header( 'Content-Type: text/plain; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentionally outputting plain text
			echo $data;
		} else {
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON output
			echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		}
		exit;
	}

	/**
	 * Handle clear history (admin_post, POST only)
	 *
	 * @return void
	 */
	public static function handle_clear_history(): void {
		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) : '';
		if ( 'POST' !== $request_method ) {
			wp_safe_redirect( admin_url( 'admin.php?page=menupilot-history' ) );
			exit;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'menupilot' ), '', array( 'response' => 403 ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in next line
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'menupilot_clear_history' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'menupilot' ), '', array( 'response' => 403 ) );
		}

		History::delete_all();

		$redirect = add_query_arg( 'menupilot_history_cleared', '1', admin_url( 'admin.php?page=menupilot-history' ) );
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Render the history page
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'menupilot' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter params
		$page = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter params
		$action_type = isset( $_GET['action_type'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['action_type'] ) ) : null;
		$action_type = in_array( $action_type, array( 'import', 'export' ), true ) ? $action_type : null;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter params
		$menu_id = isset( $_GET['menu_id'] ) ? (int) $_GET['menu_id'] : null;
		$menu_id = $menu_id > 0 ? $menu_id : null;

		$result  = History::get_entries( $page, self::PER_PAGE, $action_type, $menu_id );
		$entries = $result['entries'];
		$total   = $result['total'];
		$pages   = $result['pages'];

		$download_nonce = wp_create_nonce( 'menupilot_download_history' );
		$clear_nonce    = wp_create_nonce( 'menupilot_clear_history' );
		$base_args      = array( 'page' => 'menupilot-history' );
		if ( $action_type ) {
			$base_args['action_type'] = $action_type;
		}
		if ( $menu_id ) {
			$base_args['menu_id'] = $menu_id;
		}
		$base_url = add_query_arg( $base_args, admin_url( 'admin.php' ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check for success message
		$history_cleared = isset( $_GET['menupilot_history_cleared'] );
		?>
		<div class="menupilot-page menupilot-page--history">
			<?php if ( $history_cleared ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'History cleared successfully.', 'menupilot' ); ?></p></div>
			<?php endif; ?>
			<?php require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/header.php'; ?>
			<div class="menupilot-body">
				<?php
				$mp_title = __( 'History', 'menupilot' );
				$mp_desc  = __( 'Import and export activity log. Download as JSON or text for backup or auditing.', 'menupilot' );
				require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/body-header.php';
				?>

				<div class="mp-2col mp-2col--no-sidebar">
					<section class="mp-2col-content">
						<div class="mp-toolbar">
							<div class="mp-toolbar-left">
								<form method="get" class="mp-inline-form">
									<input type="hidden" name="page" value="menupilot-history" />
									<select name="action_type">
										<option value=""><?php esc_html_e( 'All actions', 'menupilot' ); ?></option>
										<option value="export" <?php selected( $action_type, 'export' ); ?>><?php esc_html_e( 'Export', 'menupilot' ); ?></option>
										<option value="import" <?php selected( $action_type, 'import' ); ?>><?php esc_html_e( 'Import', 'menupilot' ); ?></option>
									</select>
									<button type="submit" class="button button-secondary"><?php esc_html_e( 'Filter', 'menupilot' ); ?></button>
								</form>
							</div>
							<div class="mp-toolbar-right">
								<span class="mp-toolbar-label"><strong><?php esc_html_e( 'Download History Logs:', 'menupilot' ); ?></strong></span>
								<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=menupilot_download_history&format=json&_wpnonce=' . $download_nonce ) ); ?>" class="button button-secondary" download>
									<?php esc_html_e( 'JSON', 'menupilot' ); ?>
								</a>
								<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=menupilot_download_history&format=text&_wpnonce=' . $download_nonce ) ); ?>" class="button button-secondary" download>
									<?php esc_html_e( 'Plain Text', 'menupilot' ); ?>
								</a>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php?action=menupilot_clear_history' ) ); ?>" class="mp-inline-form mp-clear-history-form" onsubmit="return confirm('<?php echo esc_js( __( 'Clear all history? This cannot be undone.', 'menupilot' ) ); ?>');">
									<?php wp_nonce_field( 'menupilot_clear_history' ); ?>
									<button type="submit" class="button button-link-delete" <?php echo empty( $entries ) ? ' disabled' : ''; ?>>
										<?php esc_html_e( 'Clear History', 'menupilot' ); ?>
									</button>
								</form>
							</div>
						</div>

						<div class="mp-content-inner">
							<div class="mp-card">
								<h2><?php esc_html_e( 'Activity Log', 'menupilot' ); ?></h2>
								<?php if ( empty( $entries ) ) : ?>
									<p><?php esc_html_e( 'No history entries yet. Export or import a menu to see activity here.', 'menupilot' ); ?></p>
								<?php else : ?>
									<table class="widefat fixed striped">
										<thead>
											<tr>
												<th><?php esc_html_e( 'Date', 'menupilot' ); ?></th>
												<th><?php esc_html_e( 'Action', 'menupilot' ); ?></th>
												<th><?php esc_html_e( 'Menu', 'menupilot' ); ?></th>
												<th><?php esc_html_e( 'User', 'menupilot' ); ?></th>
												<th><?php esc_html_e( 'Status', 'menupilot' ); ?></th>
												<th><?php esc_html_e( 'Details', 'menupilot' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ( $entries as $entry ) : ?>
												<tr>
													<td>
													<?php
														$created_at = $entry->created_at ?? '';
													if ( $created_at ) {
														$date_format = get_option( 'date_format' ) ? get_option( 'date_format' ) : 'Y-m-d';
														$time_format = get_option( 'time_format' ) ? get_option( 'time_format' ) : 'H:i:s';
														$format      = $date_format . ' ' . $time_format;
														echo esc_html( get_date_from_gmt( $created_at, $format ) );
													} else {
														echo '';
													}
													?>
														</td>
													<td><code><?php echo esc_html( $entry->action_type ?? '' ); ?></code></td>
													<td>
														<?php
														if ( ! empty( $entry->menu_id ) ) {
															$edit_url = admin_url( 'nav-menus.php?action=edit&menu=' . (int) $entry->menu_id );
															echo '<a href="' . esc_url( $edit_url ) . '">' . esc_html( $entry->menu_name ?? '-' ) . '</a>';
														} else {
															echo esc_html( $entry->menu_name ?? '-' );
														}
														?>
													</td>
													<td><?php echo esc_html( $entry->user_login ?? '-' ); ?></td>
													<td>
														<?php
														$status = $entry->outcome ?? '';
														$class  = 'success' === $status ? 'mp-outcome-success' : 'mp-outcome-failure';
														echo '<span class="' . esc_attr( $class ) . '">' . esc_html( $status ) . '</span>';
														?>
													</td>
													<td><?php echo esc_html( $entry->details ?? '-' ); ?></td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>

									<?php if ( $pages > 1 ) : ?>
										<div class="mp-pagination">
											<?php
											$pagination_base = add_query_arg( 'paged', '%#%', $base_url );
											echo wp_kses_post(
												paginate_links(
													array(
														'base' => $pagination_base,
														'format' => '',
														'prev_text' => '&laquo;',
														'next_text' => '&raquo;',
														'total' => $pages,
														'current' => $page,
													)
												)
											);
											?>
										</div>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>
					</section>
				</div>
			</div>
		</div>
		<?php
	}
}
