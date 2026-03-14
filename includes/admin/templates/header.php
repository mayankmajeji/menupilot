<?php
/**
 * Admin Header Template
 *
 * @package MenuPilot
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get plugin version.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable, local scope
$plugin_version = defined( 'MENUPILOT_VERSION' ) ? MENUPILOT_VERSION : '1.0.0';

// Resolve current page for active link.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Read-only navigation parameter, not a form submission. Template variable, local scope.
$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template helper function variable, local scope
$is_active = function ( string $slug ) use ( $current_page ): string {
	return $slug === $current_page ? ' is-active' : '';
};
?>
<div class="menupilot-header">
	<div class="mp-header-inner">
		<div class="mp-left">
			<a class="mp-logo" href="<?php echo esc_url( admin_url( 'admin.php?page=menupilot-settings' ) ); ?>">
				<img src="<?php echo esc_url( MENUPILOT_PLUGIN_URL . 'assets/images/favicon.svg' ); ?>" alt="MenuPilot" width="28" height="28" style="display: block;" />
				<span class="mp-brand">MenuPilot</span>
			</a>
		</div>
		<nav class="mp-nav">
			<a class="mp-nav-item<?php echo esc_attr( $is_active( 'menupilot-settings' ) ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=menupilot-settings' ) ); ?>">Settings</a>
			<a class="mp-nav-item<?php echo esc_attr( $is_active( 'menupilot-export' ) ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=menupilot-export' ) ); ?>">Export Menu</a>
			<a class="mp-nav-item<?php echo esc_attr( $is_active( 'menupilot-import' ) ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=menupilot-import' ) ); ?>">Import Menu</a>
			<a class="mp-nav-item<?php echo esc_attr( $is_active( 'menupilot-tools' ) ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=menupilot-tools' ) ); ?>">Tools</a>
			<a class="mp-nav-item<?php echo esc_attr( $is_active( 'menupilot-help' ) ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=menupilot-help' ) ); ?>">Help</a>
			<span class="mp-version">v<?php echo esc_html( $plugin_version ); ?></span>
		</nav>
	</div>
</div>