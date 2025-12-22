<?php

/**
 * Admin Header Template
 *
 * @package MenuPilot
 */

if (! defined('WPINC')) {
    die;
}

// Get plugin version
$plugin_version = defined('MENUPILOT_VERSION') ? MENUPILOT_VERSION : '1.0.0';

// Resolve current page for active link
$current_page = isset($_GET['page']) ? (string) $_GET['page'] : '';
$is_active = function (string $slug) use ($current_page): string {
    return $current_page === $slug ? ' is-active' : '';
};
?>
<div class="menupilot-header">
    <div class="mp-header-inner">
        <div class="mp-left">
            <a class="mp-logo" href="<?php echo esc_url(admin_url('admin.php?page=menupilot-settings')); ?>">
                <span class="dashicons dashicons-menu-alt" style="font-size: 28px; width: 28px; height: 28px;"></span>
                <span class="mp-brand">MenuPilot</span>
            </a>
        </div>
        <nav class="mp-nav">
            <a class="mp-nav-item<?php echo esc_attr($is_active('menupilot-settings')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=menupilot-settings')); ?>">Settings</a>
            <a class="mp-nav-item<?php echo esc_attr($is_active('menupilot-tools')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=menupilot-tools')); ?>">Tools</a>
            <a class="mp-nav-item<?php echo esc_attr($is_active('menupilot-help')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=menupilot-help')); ?>">Help</a>
            <span class="mp-version">v<?php echo esc_html($plugin_version); ?></span>
        </nav>
    </div>
</div>