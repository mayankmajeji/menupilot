<?php
/**
 * Help Page Class
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot\Admin;

/**
 * Class Help_Page
 *
 * Handles the help admin page (Support, FAQs, System Info)
 */
class Help_Page {

	/**
	 * Render the help page
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can('manage_options') ) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'menupilot'));
		}

		// Gather environment info
		global $wp_version;
		$php_version = PHP_VERSION;
		$plugin_ver = defined('MENUPILOT_VERSION') ? MENUPILOT_VERSION : '1.0.0';

		// Define tabs
		$help_tabs = array(
			'support' => __('Support', 'menupilot'),
			'faqs'    => __('FAQs', 'menupilot'),
			'system'  => __('System Info', 'menupilot'),
		);

		// Get current tab
		$current_help_tab = isset($_GET['help_tab']) ? (string) $_GET['help_tab'] : 'support';
		if ( ! array_key_exists($current_help_tab, $help_tabs) ) {
			$current_help_tab = 'support';
		}

		?>
		<div class="menupilot-page menupilot-page--help">
			<?php require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/header.php'; ?>
			<div class="menupilot-body">
				<?php
				$mp_title = __('Help', 'menupilot');
				$mp_desc  = __('Support resources, FAQs, and system information.', 'menupilot');
				require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/body-header.php';
				?>

				<div class="mp-2col">
					<aside class="mp-vtabs">
						<?php foreach ( $help_tabs as $tab_id => $tab_label ) : ?>
							<?php
							$icon_partial = 'support-icon.php';
							if ( $tab_id === 'faqs' ) {
								$icon_partial = 'faq-icon.php';
							} elseif ( $tab_id === 'system' ) {
								$icon_partial = 'info-icon.php';
							}
							$icon_path = MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/icons/' . $icon_partial;
							?>
							<a class="mp-vtab <?php echo $current_help_tab === $tab_id ? 'is-active' : ''; ?>"
								href="<?php echo esc_url(admin_url('admin.php?page=menupilot-help&help_tab=' . urlencode($tab_id))); ?>">
								<span class="mp-vtab-icon">
									<?php
									if ( file_exists($icon_path) ) {
										require $icon_path;
									}
									?>
								</span>
								<span class="mp-vtab-text"><?php echo esc_html($tab_label); ?></span>
							</a>
						<?php endforeach; ?>
					</aside>

					<section class="mp-2col-content">
						<div class="mp-toolbar">
							<button type="button" class="mp-collapse-btn" data-mp-toggle="vtabs">
								<span class="mp-collapse-icon icon-open" aria-hidden="true">
									<?php require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/icons/panel-close-icon.php'; ?>
								</span>
								<span class="mp-collapse-icon icon-close" aria-hidden="true" style="display:none;">
									<?php require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/icons/panel-open-icon.php'; ?>
								</span>
							</button>
							<div></div>
						</div>

						<div class="mp-content-inner">
							<?php if ( $current_help_tab === 'support' ) : ?>
								<!-- Support Tab Content -->
								<div class="mp-support-cards">
									<div class="mp-support-card mp-card">
										<div class="mp-support-card-content">
											<div class="mp-support-icon is-primary-icon">
												<?php require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/icons/wordpress-support-icon.php'; ?>
											</div>
											<h3><?php esc_html_e('WordPress.org', 'menupilot'); ?></h3>
											<p><?php esc_html_e('MenuPilot is available on WordPress.org where you can download the plugin, submit a bug ticket, and follow along with updates.', 'menupilot'); ?></p>
										</div>
										<div class="menupilot-buttons">
											<a class="button button-primary" href="https://wordpress.org/plugins/menupilot/" target="_blank" rel="noopener">
												<?php esc_html_e('Visit WordPress.org', 'menupilot'); ?>
											</a>
										</div>
									</div>

									<div class="mp-support-card mp-card">
										<div class="mp-support-card-content">
											<div class="mp-support-icon is-primary-icon">
												<?php require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/icons/github-icon.php'; ?>
											</div>
											<h3><?php esc_html_e('GitHub', 'menupilot'); ?></h3>
											<p><?php esc_html_e('MenuPilot is also available on GitHub where you can browse the code, open a bug report, and follow along with development.', 'menupilot'); ?></p>
										</div>
										<div class="menupilot-buttons">
											<a class="button button-primary" href="https://github.com/mayankmajeji/menupilot" target="_blank" rel="noopener">
												<?php esc_html_e('Visit GitHub', 'menupilot'); ?>
											</a>
										</div>
									</div>
								</div>

							<?php elseif ( $current_help_tab === 'faqs' ) : ?>
								<!-- FAQs Tab Content -->
								<div class="mp-card">
									<h2><?php esc_html_e('Frequently Asked Questions', 'menupilot'); ?></h2>
									<?php require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/faqs.php'; ?>
								</div>

							<?php elseif ( $current_help_tab === 'system' ) : ?>
								<!-- System Info Tab Content -->
								<div class="mp-card">
									<h2><?php esc_html_e('System Information', 'menupilot'); ?></h2>
									<div class="mp-system-info">
										<ul>
											<li>
												<span class="mp-si-label"><?php esc_html_e('Plugin Version', 'menupilot'); ?></span>
												<span class="mp-si-value"><?php
													// translators: %s: Plugin version number
													echo esc_html(sprintf(__('v%s', 'menupilot'), $plugin_ver));
												?></span>
											</li>
											<li>
												<span class="mp-si-label"><?php esc_html_e('WordPress', 'menupilot'); ?></span>
												<span class="mp-si-value"><?php
													// translators: %s: WordPress version number
													echo esc_html(sprintf(__('v%s', 'menupilot'), $wp_version));
												?></span>
											</li>
											<li>
												<span class="mp-si-label"><?php esc_html_e('PHP', 'menupilot'); ?></span>
												<span class="mp-si-value"><?php
													// translators: %s: PHP version number
													echo esc_html(sprintf(__('v%s', 'menupilot'), $php_version));
												?></span>
											</li>
											<li>
												<span class="mp-si-label"><?php esc_html_e('Memory Limit', 'menupilot'); ?></span>
												<span class="mp-si-value"><?php echo esc_html((string) ini_get('memory_limit')); ?></span>
											</li>
										</ul>
										<div class="menupilot-buttons">
											<button type="button" class="button button-primary" id="mp-copy-system-info"><?php esc_html_e('Copy System Info', 'menupilot'); ?></button>
											<span id="mp-copy-system-info-msg" style="margin-left:8px;color:#46b450;display:none;"><?php esc_html_e('Copied!', 'menupilot'); ?></span>
										</div>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</section>
				</div>
			</div>
		</div>

		<script>
		document.addEventListener('DOMContentLoaded', function() {
			var btn = document.getElementById('mp-copy-system-info');
			if (!btn) return;

			btn.addEventListener('click', function() {
				var info = [
					'MenuPilot: v<?php echo esc_js($plugin_ver); ?>',
					'WordPress: v<?php echo esc_js($wp_version); ?>',
					'PHP: v<?php echo esc_js($php_version); ?>',
					'Memory Limit: <?php echo esc_js((string) ini_get('memory_limit')); ?>'
				].join('\n');

				function showCopied() {
					var msg = document.getElementById('mp-copy-system-info-msg');
					if (msg) {
						msg.style.display = 'inline';
						setTimeout(function() {
							msg.style.display = 'none';
						}, 1500);
					}
				}

				function fallbackCopy(text) {
					var ta = document.createElement('textarea');
					ta.value = text;
					ta.setAttribute('readonly', '');
					ta.style.position = 'absolute';
					ta.style.left = '-9999px';
					document.body.appendChild(ta);
					ta.select();
					try {
						var ok = document.execCommand('copy');
						document.body.removeChild(ta);
						if (ok) showCopied();
					} catch (e) {
						document.body.removeChild(ta);
					}
				}

				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(info).then(showCopied).catch(function() {
						fallbackCopy(info);
					});
				} else {
					fallbackCopy(info);
				}
			});
		});
		</script>
		<?php
	}
}

