<?php

/**
 * Tools Page Class
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot\Admin;

/**
 * Class Tools_Page
 *
 * Handles the tools admin page (Import/Export/Reset Settings tabs)
 */
class Tools_Page
{

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
