<?php
/**
 * Common utility functions for the MenuPilot plugin.
 *
 * @package MenuPilot
 */

declare(strict_types=1);

namespace MenuPilot;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get client IP address with proper fallback and sanitization
 *
 * @return string Sanitized client IP address, or empty string if not available
 */
function get_client_ip(): string {
	$ip = '';

	// Check HTTP_CLIENT_IP first (least common but highest priority).
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// For proxied requests, get the first IP in the chain.
		$forwarded = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		// Handle comma-separated list of IPs.
		$ip_list = explode( ',', $forwarded );
		$ip      = trim( $ip_list[0] );
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	/**
	 * Filter the detected client IP address
	 *
	 * @param string $ip The detected IP address
	 */
	return apply_filters( 'menupilot_client_ip', $ip );
}

/**
 * Log debug message
 *
 * @param string $message Message to log.
 * @param string $level Log level (error, warning, info, debug).
 * @return void
 */
function log_debug( string $message, string $level = 'info' ): void {
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG || ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
		return;
	}

	$prefix = '[MenuPilot]';
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging function, only runs when WP_DEBUG is enabled
	error_log( "{$prefix} [{$level}] {$message}" );
}

/**
 * Check if plugin debug mode is enabled
 *
 * @return bool
 */
function is_debug_mode(): bool {
	$settings = new Settings();
	return (bool) $settings->get_option( 'debug_mode', false );
}
