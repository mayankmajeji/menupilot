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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable, local scope
$plugin_version = defined('MENUPILOT_VERSION') ? MENUPILOT_VERSION : '1.0.0';

// Resolve current page for active link
// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Read-only navigation parameter, not a form submission. Template variable, local scope.
$current_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash((string) $_GET['page'])) : '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template helper function variable, local scope
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
            <a class="mp-nav-item<?php echo esc_attr($is_active('menupilot-export')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=menupilot-export')); ?>">Export Menu</a>
            <a class="mp-nav-item<?php echo esc_attr($is_active('menupilot-import')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=menupilot-import')); ?>">Import Menu</a>
            <a class="mp-nav-item<?php echo esc_attr($is_active('menupilot-tools')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=menupilot-tools')); ?>">Tools</a>
            <a class="mp-nav-item<?php echo esc_attr($is_active('menupilot-help')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=menupilot-help')); ?>">Help</a>
            <span class="mp-version">v<?php echo esc_html($plugin_version); ?></span>
        </nav>
    </div>
</div>