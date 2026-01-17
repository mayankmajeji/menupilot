<?php
/**
 * FAQs Template
 *
 * @package MenuPilot
 */

if ( ! defined('WPINC') ) {
	die;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable, local scope
$faqs = array(
	array(
		'question' => __('What does MenuPilot do?', 'menupilot'),
		'answer'   => __('MenuPilot allows you to export and import WordPress navigation menus between sites. It preserves menu structure, hierarchy, CSS classes, and all metadata, making it easy to move menus from development to production or between different WordPress installations.', 'menupilot'),
	),
	array(
		'question' => __('What information is included in the export?', 'menupilot'),
		'answer'   => __('MenuPilot exports include: menu name, menu items (pages, posts, custom post types, categories, custom links), hierarchical structure (parent-child relationships), menu order, CSS classes, link attributes (target, rel), descriptions, and theme location assignments.', 'menupilot'),
	),
	array(
		'question' => __('How does the import preview work?', 'menupilot'),
		'answer'   => __('Before importing, MenuPilot shows you exactly what will happen. It automatically matches menu items to existing content on your site and clearly indicates which items were found, which will be converted to custom links, and allows you to manually override any mappings.', 'menupilot'),
	),
	array(
		'question' => __('What happens if a page or post doesn\'t exist on the destination site?', 'menupilot'),
		'answer'   => __('MenuPilot will automatically convert missing pages, posts, or taxonomies into custom links, preserving the menu structure. You can see all conversions in the preview screen before importing and manually map items to different content if needed.', 'menupilot'),
	),
	array(
		'question' => __('Can I import menus from other plugins?', 'menupilot'),
		'answer'   => __('MenuPilot uses its own JSON format for exports. You can only import menus that were exported using MenuPilot. This ensures data integrity and prevents compatibility issues.', 'menupilot'),
	),
	array(
		'question' => __('Will MenuPilot overwrite my existing menus?', 'menupilot'),
		'answer'   => __('No. MenuPilot always creates a new menu when importing. You can choose the name and optionally assign it to a theme location. Your existing menus remain untouched.', 'menupilot'),
	),
	array(
		'question' => __('Does MenuPilot work with custom post types?', 'menupilot'),
		'answer'   => __('Yes! MenuPilot supports menu items linked to custom post types, custom taxonomies, and WooCommerce products. As long as the post type exists on both sites, MenuPilot will match them correctly.', 'menupilot'),
	),
	array(
		'question' => __('Can I export multiple menus at once?', 'menupilot'),
		'answer'   => __('Currently, you can export one menu at a time. This ensures clean, focused exports that are easier to manage and import. You can export as many menus as you need individually.', 'menupilot'),
	),
	array(
		'question' => __('Is there a limit to menu size?', 'menupilot'),
		'answer'   => __('There are no hard limits on menu size. MenuPilot can handle menus with hundreds of items, including nested hierarchies. However, very large menus may take longer to process during import preview.', 'menupilot'),
	),
	array(
		'question' => __('Does MenuPilot support multisite?', 'menupilot'),
		'answer'   => __('MenuPilot works on each site individually in a multisite network. You can export from one site and import to another within the same network or across different WordPress installations.', 'menupilot'),
	),
);
?>

<div id="faq-content" class="turnstilewp-faq-accordion" role="tablist">
	<?php
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template loop variables, local scope
	foreach ( $faqs as $index => $faq ) : ?>
		<?php
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables, local scope
		$faq_id = $index + 1;
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable, local scope
		$question_id = 'faq-q-' . $faq_id;
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable, local scope
		$answer_id = 'faq-a-' . $faq_id;
		?>
		<div class="faq-block">
			<h3 class="faq-question" role="tab" id="<?php echo esc_attr($question_id); ?>" aria-controls="<?php echo esc_attr($answer_id); ?>" aria-expanded="false" tabindex="0">
				<?php echo esc_html($faq['question']); ?>
				<span class="faq-icon"><?php require MENUPILOT_PLUGIN_DIR . 'includes/admin/templates/icons/caret-icon.php'; ?></span>
			</h3>
			<div class="faq-answer" id="<?php echo esc_attr($answer_id); ?>" aria-labelledby="<?php echo esc_attr($question_id); ?>" role="tabpanel" aria-hidden="true" style="display:none;">
				<p><?php echo esc_html($faq['answer']); ?></p>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var questions = document.querySelectorAll('.faq-question');
	var answers = document.querySelectorAll('.faq-answer');
	questions.forEach(function(q, idx) {
		q.addEventListener('click', function() {
			var expanded = q.getAttribute('aria-expanded') === 'true';
			// Collapse all
			questions.forEach(function(qq, i) {
				qq.setAttribute('aria-expanded', 'false');
				answers[i].style.display = 'none';
			});
			// Expand this one if it was not already open
			if (!expanded) {
				q.setAttribute('aria-expanded', 'true');
				answers[idx].style.display = 'block';
			}
		});
		q.addEventListener('keydown', function(e) {
			if (e.key === 'Enter' || e.key === ' ') {
				q.click();
				e.preventDefault();
			}
		});
		// Start collapsed
		answers[idx].style.display = 'none';
	});
});
</script>

