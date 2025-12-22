<?php
/**
 * Admin Body Header Template
 *
 * @package MenuPilot
 */

if ( ! defined('WPINC') ) {
	die;
}

// Expected (optional) inputs:
// - $mp_title (string)
// - $mp_desc (string)
?>
<div class="mp-body-header">
	<div class="mp-bh-left">
		<span class="dashicons dashicons-menu-alt" style="font-size: 40px; width: 40px; height: 40px;"></span>
	</div>
	<div class="mp-bh-right">
		<h1><?php echo esc_html(isset($mp_title) && $mp_title !== '' ? $mp_title : get_admin_page_title()); ?></h1>
		<?php if ( ! empty($mp_desc) ) : ?>
			<p class="mp-page-desc"><?php echo esc_html($mp_desc); ?></p>
		<?php endif; ?>
	</div>
</div>

