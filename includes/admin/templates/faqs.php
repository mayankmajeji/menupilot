<?php
/**
 * FAQs Template
 *
 * @package MenuPilot
 */

if ( ! defined('WPINC') ) {
	die;
}

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

<div class="mp-faqs-wrapper">
	<?php foreach ( $faqs as $index => $faq ) : ?>
		<div class="mp-faq-item <?php echo $index === 0 ? 'is-open' : ''; ?>">
			<button type="button" class="mp-faq-question">
				<span class="mp-faq-title"><?php echo esc_html($faq['question']); ?></span>
				<span class="mp-faq-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<polyline points="6 9 12 15 18 9"></polyline>
					</svg>
				</span>
			</button>
			<div class="mp-faq-answer">
				<p><?php echo esc_html($faq['answer']); ?></p>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var faqItems = document.querySelectorAll('.mp-faq-item');
	
	faqItems.forEach(function(item) {
		var button = item.querySelector('.mp-faq-question');
		
		button.addEventListener('click', function() {
			var isOpen = item.classList.contains('is-open');
			
			// Close all items
			faqItems.forEach(function(otherItem) {
				otherItem.classList.remove('is-open');
			});
			
			// Toggle current item
			if (!isOpen) {
				item.classList.add('is-open');
			}
		});
	});
});
</script>

