<?php
/**
 * Import Page Class
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot\Admin;

/**
 * Class Import_Page
 *
 * Handles the import menu admin page
 */
class Import_Page {

	/**
	 * Render the import page
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'menupilot' ) );
		}

		?>
		<div class="menupilot-page menupilot-page--import">
			<?php require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/header.php'; ?>
			<div class="menupilot-body">
				<?php
				$mp_title = __( 'Import Menu', 'menupilot' );
				$mp_desc  = __( 'Upload a JSON file to import a menu. You will be able to preview and map items before importing.', 'menupilot' );
				require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/body-header.php';
				?>

				<?php settings_errors( 'menupilot_settings' ); ?>

				<div class="mp-2col mp-2col--no-sidebar">
					<section class="mp-2col-content">
						<div class="mp-toolbar">
							<div></div>
							<div></div>
						</div>

						<div class="mp-content-inner">
							<div class="mp-card">
							<h2><?php esc_html_e( 'Import Menu', 'menupilot' ); ?></h2>
							<p><?php esc_html_e( 'Upload a JSON file to import a menu. You will be able to preview and map items before importing.', 'menupilot' ); ?></p>
							
							<form id="mp-import-form" method="post" enctype="multipart/form-data">
								<?php wp_nonce_field( 'menupilot_import', 'menupilot_import_nonce' ); ?>
								
								<div class="mp-upload-area">
									<input type="file" name="menu_file" id="mp-menu-file" accept=".json,application/json" required />
									<label for="mp-menu-file" class="mp-upload-label">
										<span class="dashicons dashicons-upload" style="font-size:48px;width:48px;height:48px;"></span>
										<span class="mp-upload-text">
											<strong><?php esc_html_e( 'Choose a JSON file', 'menupilot' ); ?></strong>
											<br>
											<span class="description"><?php esc_html_e( 'or drag and drop here', 'menupilot' ); ?></span>
										</span>
									</label>
									<div id="mp-file-info" style="display:none;margin-top:15px;">
										<strong><?php esc_html_e( 'Selected file:', 'menupilot' ); ?></strong>
										<span id="mp-file-name"></span>
									</div>
								</div>

								<p class="submit">
									<button type="submit" class="button button-primary button-hero" id="mp-import-btn" disabled>
										<?php esc_html_e( 'Upload & Preview', 'menupilot' ); ?>
									</button>
									<span class="spinner" id="mp-import-spinner" style="float:none;margin:0 10px;"></span>
								</p>
							</form>

							<div id="mp-import-preview" style="display:none;margin-top:30px;">
								<!-- Preview will be loaded here via AJAX -->
							</div>

							<div id="mp-import-result" style="margin-top:20px;"></div>
						</div>
						
						<div class="mp-card" style="margin-top:30px;">
							<h3><?php esc_html_e( 'Import Instructions', 'menupilot' ); ?></h3>
							<ol>
								<li><?php esc_html_e( 'Upload a menu JSON file exported from MenuPilot', 'menupilot' ); ?></li>
								<li><?php esc_html_e( 'Review the preview and mapping suggestions', 'menupilot' ); ?></li>
								<li><?php esc_html_e( 'Adjust mappings if needed for unmatched items', 'menupilot' ); ?></li>
								<li><?php esc_html_e( 'Enter a name for the imported menu', 'menupilot' ); ?></li>
								<li><?php esc_html_e( 'Optionally assign to a theme location', 'menupilot' ); ?></li>
								<li><?php esc_html_e( 'Click "Import Menu" to complete the process', 'menupilot' ); ?></li>
							</ol>
						</div>
						</div>
					</section>
				</div>
			</div>
		</div>
		<?php
	}
}
