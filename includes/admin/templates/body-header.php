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
		<img src="<?php echo esc_url(MENUPILOT_PLUGIN_URL . 'assets/images/favicon.svg'); ?>" alt="MenuPilot" width="40" height="40" />
	</div>
	<div class="mp-bh-right">
		<h1><?php echo esc_html(isset($mp_title) && $mp_title !== '' ? $mp_title : get_admin_page_title()); ?></h1>
		<?php if ( ! empty($mp_desc) ) : ?>
			<p class="mp-page-desc"><?php echo esc_html($mp_desc); ?></p>
		<?php endif; ?>
	</div>
</div>

