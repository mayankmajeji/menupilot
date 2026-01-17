<?php

declare(strict_types=1);

namespace MenuPilot\Admin;

/**
 * Tools Page Class
 *
 * @package MenuPilot
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Class Tools_Page
 *
 * Handles the tools admin page (Import/Export/Reset Settings tabs)
 */
class Tools_Page
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     *
     * @return void
     */
    private function init_hooks(): void
    {
        // Process form submissions only on admin_init to avoid running on every page load
        add_action('admin_init', array($this, 'process_form_submissions'));
    }

    /**
     * Process form submissions for tools page
     *
     * @return void
     */
    public function process_form_submissions(): void
    {
        // Only process if we're on the tools page
        if (! isset($_GET['page']) || 'menupilot-tools' !== $_GET['page']) {
            return;
        }

        // Check user capabilities first
        if (! current_user_can('manage_options')) {
            return;
        }

        // Process import settings
        if (isset($_POST['menupilot_tools_action']) && 'import' === $_POST['menupilot_tools_action']) {
            // Verify nonce
            if (! isset($_POST['menupilot_tools_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['menupilot_tools_nonce'])), 'menupilot_tools_action')) {
                add_settings_error(
                    'menupilot_tools',
                    'import_failed',
                    __('Security check failed. Please try again.', 'menupilot'),
                    'error'
                );
                return;
            }

            // Process file upload
            if (! isset($_FILES['import_file']) || ! is_array($_FILES['import_file'])) {
                add_settings_error(
                    'menupilot_tools',
                    'import_failed',
                    __('No file uploaded.', 'menupilot'),
                    'error'
                );
                return;
            }

            // Validate and sanitize file upload
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $_FILES is validated and sanitized below
            $file = $_FILES['import_file'];

            // Validate file upload error
            if (! isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                add_settings_error(
                    'menupilot_tools',
                    'import_failed',
                    __('File upload error occurred.', 'menupilot'),
                    'error'
                );
                return;
            }

            // Validate file was actually uploaded
            if (! isset($file['tmp_name']) || ! is_uploaded_file($file['tmp_name'])) {
                add_settings_error(
                    'menupilot_tools',
                    'import_failed',
                    __('Invalid file upload.', 'menupilot'),
                    'error'
                );
                return;
            }

            // Sanitize file path
            $file_tmp_name = sanitize_text_field($file['tmp_name']);

            // Validate file extension
            $file_name = isset($file['name']) ? sanitize_file_name($file['name']) : '';
            if (! preg_match('/\.json$/i', $file_name)) {
                add_settings_error(
                    'menupilot_tools',
                    'import_failed',
                    __('Invalid file type. Please upload a JSON file.', 'menupilot'),
                    'error'
                );
                return;
            }

            // Read file content
            $file_content = file_get_contents($file_tmp_name);
            if (false === $file_content) {
                add_settings_error(
                    'menupilot_tools',
                    'import_failed',
                    __('Failed to read uploaded file.', 'menupilot'),
                    'error'
                );
                return;
            }

            $import_data = json_decode($file_content, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! isset($import_data['settings'])) {
                add_settings_error(
                    'menupilot_tools',
                    'import_failed',
                    __('Invalid settings file format.', 'menupilot'),
                    'error'
                );
                return;
            }

            // Import settings
            $settings = $import_data['settings'];
            if (is_array($settings)) {
                update_option('menupilot_settings', $settings);
                add_settings_error(
                    'menupilot_tools',
                    'import_success',
                    __('Settings imported successfully!', 'menupilot'),
                    'success'
                );
            } else {
                add_settings_error(
                    'menupilot_tools',
                    'import_failed',
                    __('Invalid settings data in file.', 'menupilot'),
                    'error'
                );
            }
        }

        // Process reset settings
        if (isset($_POST['menupilot_tools_action']) && 'reset' === $_POST['menupilot_tools_action']) {
            // Verify nonce
            if (! isset($_POST['menupilot_tools_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['menupilot_tools_nonce'])), 'menupilot_tools_action')) {
                add_settings_error(
                    'menupilot_tools',
                    'reset_failed',
                    __('Security check failed. Please try again.', 'menupilot'),
                    'error'
                );
                return;
            }

            // Reset to defaults
            require_once MENUPILOT_PLUGIN_DIR . 'includes/class-settings.php';
            $settings = new \MenuPilot\Settings();
            delete_option('menupilot_settings');
            $settings->add_default_options();

            add_settings_error(
                'menupilot_tools',
                'reset_success',
                __('All settings have been reset to their default values.', 'menupilot'),
                'success'
            );
        }
    }

    /**
     * Render the tools page
     *
     * @return void
     */
    public function render(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'menupilot'));
        }

        // Define tabs
        $tabs = array(
            'import' => __('Import Settings', 'menupilot'),
            'export' => __('Export Settings', 'menupilot'),
            'reset'  => __('Reset Settings', 'menupilot'),
        );

        // Get current tab
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation parameter, not a form submission
        $current_tab = isset($_GET['tools_tab']) ? sanitize_text_field(wp_unslash((string) $_GET['tools_tab'])) : 'import';
        if (! array_key_exists($current_tab, $tabs)) {
            $current_tab = 'import';
        }

?>
        <div class="menupilot-page menupilot-page--tools">
            <?php require_once MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/header.php'; ?>
            <div class="menupilot-body">
                <?php
                $mp_title = __('Tools', 'menupilot');
                $mp_desc  = __('Import, export, or reset plugin settings.', 'menupilot');
                require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/body-header.php';
                ?>

                <?php settings_errors('menupilot_tools'); ?>

                <div class="mp-2col">
                    <aside class="mp-vtabs">
                        <?php foreach ($tabs as $tab_id => $tab_label) : ?>
                            <?php
                            $icon_partial = 'settings-icon.php';
                            if ($tab_id === 'import') {
                                $icon_partial = 'import-icon.php';
                            } elseif ($tab_id === 'export') {
                                $icon_partial = 'export-icon.php';
                            }
                            $icon_path = MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/icons/' . $icon_partial;
                            ?>
                            <a class="mp-vtab <?php echo $current_tab === $tab_id ? 'is-active' : ''; ?>"
                                href="<?php echo esc_url(admin_url('admin.php?page=menupilot-tools&tools_tab=' . urlencode($tab_id))); ?>">
                                <span class="mp-vtab-icon">
                                    <?php
                                    if (file_exists($icon_path)) {
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
                            <?php if ($current_tab === 'import') : ?>
                                <!-- Import Settings Tab Content -->
                                <div class="mp-card">
                                    <h2><?php esc_html_e('Import Settings', 'menupilot'); ?></h2>
                                    <p><?php esc_html_e('Upload a settings JSON file to restore MenuPilot configuration.', 'menupilot'); ?></p>

                                    <form method="post" enctype="multipart/form-data">
                                        <?php wp_nonce_field('menupilot_tools_action', 'menupilot_tools_nonce'); ?>
                                        <input type="hidden" name="menupilot_tools_action" value="import">
                                        <input type="file" name="import_file" accept="application/json" required>
                                        <div style="margin-top:12px;">
                                            <button type="submit" class="button button-primary"><?php esc_html_e('Import Settings', 'menupilot'); ?></button>
                                        </div>
                                    </form>
                                </div>

                            <?php elseif ($current_tab === 'export') : ?>
                                <!-- Export Settings Tab Content -->
                                <div class="mp-card">
                                    <h2><?php esc_html_e('Export Settings', 'menupilot'); ?></h2>
                                    <p><?php esc_html_e('Download your MenuPilot settings as a JSON file for backup or transfer to another site.', 'menupilot'); ?></p>

                                    <a href="<?php
                                                echo esc_url(
                                                    add_query_arg(
                                                        array(
                                                            'action' => 'menupilot_export_settings',
                                                            '_wpnonce' => wp_create_nonce('menupilot_tools_export'),
                                                        ),
                                                        admin_url('admin-ajax.php')
                                                    )
                                                );
                                                ?>" class="button button-primary"><?php esc_html_e('Export Settings', 'menupilot'); ?></a>
                                </div>

                            <?php elseif ($current_tab === 'reset') : ?>
                                <!-- Reset Settings Tab Content -->
                                <div class="mp-card">
                                    <h2><?php esc_html_e('Reset Settings', 'menupilot'); ?></h2>
                                    <p><?php esc_html_e('Reset all MenuPilot settings to their default values. This action cannot be undone.', 'menupilot'); ?></p>

                                    <form method="post" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to reset all settings to defaults? This action cannot be undone.', 'menupilot')); ?>');">
                                        <?php wp_nonce_field('menupilot_tools_action', 'menupilot_tools_nonce'); ?>
                                        <input type="hidden" name="menupilot_tools_action" value="reset">
                                        <button type="submit" class="button button-secondary"><?php esc_html_e('Reset All Settings', 'menupilot'); ?></button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>
<?php
    }
}
