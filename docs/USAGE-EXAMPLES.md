# Usage Examples

Practical examples of how to use the Maverick Plugin Boilerplate for common WordPress plugin development tasks.

## Table of Contents

- [Basic Setup](#basic-setup)
- [Settings Management](#settings-management)
- [Custom Post Types](#custom-post-types)
- [REST API Endpoints](#rest-api-endpoints)
- [Admin Pages](#admin-pages)
- [AJAX Handlers](#ajax-handlers)
- [Shortcodes](#shortcodes)
- [Widgets](#widgets)
- [Cron Jobs](#cron-jobs)
- [Transients & Caching](#transients--caching)

## Basic Setup

### Adding Plugin Logic

Edit `includes/class-init.php`:

```php
private function init_hooks(): void {
    // Your hooks here
    add_action('init', array($this, 'register_custom_post_type'));
    add_action('rest_api_init', array($this, 'register_api_routes'));
    add_shortcode('your_shortcode', array($this, 'render_shortcode'));
}
```

## Settings Management

### Adding Settings Fields

```php
// In your plugin or theme's functions.php
add_filter('your_plugin_settings_fields', function($fields) {
    
    // Text field
    $fields[] = array(
        'field_id' => 'api_key',
        'type' => 'text',
        'label' => __('API Key', 'your-plugin'),
        'description' => __('Enter your API key', 'your-plugin'),
        'tab' => 'general',
        'section' => 'api',
        'priority' => 10,
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    );
    
    // Checkbox field
    $fields[] = array(
        'field_id' => 'enable_feature',
        'type' => 'checkbox',
        'label' => __('Enable Feature', 'your-plugin'),
        'description' => __('Check to enable this feature', 'your-plugin'),
        'tab' => 'general',
        'section' => 'features',
        'priority' => 20,
        'default' => false,
    );
    
    // Select field
    $fields[] = array(
        'field_id' => 'layout',
        'type' => 'select',
        'label' => __('Layout', 'your-plugin'),
        'description' => __('Choose a layout', 'your-plugin'),
        'tab' => 'appearance',
        'section' => 'layout',
        'priority' => 10,
        'options' => array(
            'grid' => __('Grid', 'your-plugin'),
            'list' => __('List', 'your-plugin'),
            'masonry' => __('Masonry', 'your-plugin'),
        ),
        'default' => 'grid',
    );
    
    // Textarea field
    $fields[] = array(
        'field_id' => 'custom_css',
        'type' => 'textarea',
        'label' => __('Custom CSS', 'your-plugin'),
        'description' => __('Add custom CSS code', 'your-plugin'),
        'tab' => 'advanced',
        'section' => 'custom_code',
        'priority' => 10,
        'default' => '',
        'sanitize_callback' => 'wp_strip_all_tags',
    );
    
    return $fields;
});
```

### Retrieving Settings

```php
// Get settings instance
$settings = new \YourPlugin\Settings();

// Get single option
$api_key = $settings->get_option('api_key');

// Get all settings
$all_settings = $settings->get_settings();

// Update option
$settings->update_option('api_key', 'new-key');
```

## Custom Post Types

### Register Custom Post Type

```php
// Add to includes/class-init.php

public function register_custom_post_type(): void {
    $labels = array(
        'name' => __('Items', 'your-plugin'),
        'singular_name' => __('Item', 'your-plugin'),
        'add_new' => __('Add New', 'your-plugin'),
        'add_new_item' => __('Add New Item', 'your-plugin'),
        'edit_item' => __('Edit Item', 'your-plugin'),
        'view_item' => __('View Item', 'your-plugin'),
        'all_items' => __('All Items', 'your-plugin'),
    );
    
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'menu_icon' => 'dashicons-portfolio',
        'show_in_rest' => true, // Enable Gutenberg
    );
    
    register_post_type('your_cpt', $args);
}
```

### Register Custom Taxonomy

```php
public function register_custom_taxonomy(): void {
    $labels = array(
        'name' => __('Categories', 'your-plugin'),
        'singular_name' => __('Category', 'your-plugin'),
        'search_items' => __('Search Categories', 'your-plugin'),
        'all_items' => __('All Categories', 'your-plugin'),
        'edit_item' => __('Edit Category', 'your-plugin'),
        'update_item' => __('Update Category', 'your-plugin'),
        'add_new_item' => __('Add New Category', 'your-plugin'),
    );
    
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'public' => true,
        'show_in_rest' => true,
    );
    
    register_taxonomy('your_taxonomy', 'your_cpt', $args);
}
```

## REST API Endpoints

### Register API Route

```php
// Create includes/class-api.php

namespace YourPlugin;

class API {
    
    public function register_routes(): void {
        register_rest_route('your-plugin/v1', '/items', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_items'),
                'permission_callback' => '__return_true',
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'create_item'),
                'permission_callback' => array($this, 'check_permission'),
            ),
        ));
    }
    
    public function get_items(\WP_REST_Request $request): \WP_REST_Response {
        $items = array(
            array('id' => 1, 'name' => 'Item 1'),
            array('id' => 2, 'name' => 'Item 2'),
        );
        
        return new \WP_REST_Response($items, 200);
    }
    
    public function create_item(\WP_REST_Request $request): \WP_REST_Response {
        $params = $request->get_params();
        
        // Validate and sanitize
        $name = sanitize_text_field($params['name'] ?? '');
        
        if (empty($name)) {
            return new \WP_REST_Response(
                array('error' => 'Name is required'),
                400
            );
        }
        
        // Create item logic here
        
        return new \WP_REST_Response(
            array('id' => 3, 'name' => $name),
            201
        );
    }
    
    public function check_permission(): bool {
        return current_user_can('manage_options');
    }
}

// Register in class-init.php
add_action('rest_api_init', function() {
    $api = new \YourPlugin\API();
    $api->register_routes();
});
```

## Admin Pages

### Add Custom Admin Page

```php
// In includes/class-settings.php

public function add_custom_admin_page(): void {
    add_submenu_page(
        'your-plugin-settings',
        __('Reports', 'your-plugin'),
        __('Reports', 'your-plugin'),
        'manage_options',
        'your-plugin-reports',
        array($this, 'render_reports_page')
    );
}

public function render_reports_page(): void {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Get data
    $data = $this->get_report_data();
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <div class="your-plugin-reports">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date', 'your-plugin'); ?></th>
                        <th><?php esc_html_e('Count', 'your-plugin'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row['date']); ?></td>
                        <td><?php echo esc_html($row['count']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
```

## AJAX Handlers

### Backend AJAX Handler

```php
// In includes/class-init.php

private function init_hooks(): void {
    add_action('wp_ajax_your_action', array($this, 'handle_ajax'));
    add_action('wp_ajax_nopriv_your_action', array($this, 'handle_ajax'));
}

public function handle_ajax(): void {
    // Verify nonce
    check_ajax_referer('your_plugin_nonce', 'nonce');
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('Permission denied', 'your-plugin'),
        ));
    }
    
    // Get data
    $data = sanitize_text_field($_POST['data'] ?? '');
    
    // Process data
    $result = $this->process_data($data);
    
    // Send response
    if ($result) {
        wp_send_json_success(array(
            'message' => __('Success!', 'your-plugin'),
            'data' => $result,
        ));
    } else {
        wp_send_json_error(array(
            'message' => __('Failed to process', 'your-plugin'),
        ));
    }
}
```

### Frontend JavaScript

```javascript
// In assets/js/main.js

(function ($) {
    'use strict';
    
    $(document).ready(function () {
        $('#your-button').on('click', function (e) {
            e.preventDefault();
            
            const data = $('#your-input').val();
            
            $.ajax({
                url: your_plugin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'your_action',
                    nonce: your_plugin.nonce,
                    data: data,
                },
                beforeSend: function () {
                    // Show loading
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.data.message);
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function () {
                    alert('Request failed');
                },
            });
        });
    });
})(jQuery);
```

## Shortcodes

### Simple Shortcode

```php
// In includes/class-init.php

public function register_shortcodes(): void {
    add_shortcode('your_shortcode', array($this, 'render_shortcode'));
}

public function render_shortcode($atts): string {
    $atts = shortcode_atts(array(
        'title' => '',
        'count' => 5,
        'layout' => 'grid',
    ), $atts);
    
    ob_start();
    ?>
    <div class="your-plugin-shortcode" data-layout="<?php echo esc_attr($atts['layout']); ?>">
        <?php if (!empty($atts['title'])): ?>
            <h3><?php echo esc_html($atts['title']); ?></h3>
        <?php endif; ?>
        
        <!-- Your content here -->
    </div>
    <?php
    return ob_get_clean();
}
```

### Usage

```
[your_shortcode title="My Title" count="10" layout="list"]
```

## Widgets

### Create Widget

```php
// Create includes/class-widget.php

namespace YourPlugin;

class Widget extends \WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'your_widget',
            __('Your Widget', 'your-plugin'),
            array('description' => __('Widget description', 'your-plugin'))
        );
    }
    
    public function widget($args, $instance): void {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . esc_html($instance['title']) . $args['after_title'];
        }
        
        // Widget content
        echo '<div class="your-widget-content">';
        echo esc_html($instance['content'] ?? '');
        echo '</div>';
        
        echo $args['after_widget'];
    }
    
    public function form($instance): void {
        $title = $instance['title'] ?? '';
        $content = $instance['content'] ?? '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'your-plugin'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('content')); ?>">
                <?php esc_html_e('Content:', 'your-plugin'); ?>
            </label>
            <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('content')); ?>"
                      name="<?php echo esc_attr($this->get_field_name('content')); ?>"><?php echo esc_textarea($content); ?></textarea>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance): array {
        $instance = array();
        $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
        $instance['content'] = sanitize_textarea_field($new_instance['content'] ?? '');
        return $instance;
    }
}

// Register widget
add_action('widgets_init', function() {
    register_widget('YourPlugin\Widget');
});
```

## Cron Jobs

### Schedule Cron Job

```php
// In includes/class-init.php

public function activate(): void {
    // Schedule on activation
    if (!wp_next_scheduled('your_plugin_daily_task')) {
        wp_schedule_event(time(), 'daily', 'your_plugin_daily_task');
    }
}

public function deactivate(): void {
    // Clear on deactivation
    wp_clear_scheduled_hook('your_plugin_daily_task');
}

private function init_hooks(): void {
    add_action('your_plugin_daily_task', array($this, 'run_daily_task'));
}

public function run_daily_task(): void {
    // Your scheduled task logic
    \YourPlugin\log_debug('Running daily task', 'info');
    
    // Do something
    $this->cleanup_old_data();
}
```

## Transients & Caching

### Using Transients

```php
public function get_cached_data(): array {
    // Check cache
    $cached = get_transient('your_plugin_data');
    
    if (false !== $cached) {
        return $cached;
    }
    
    // Generate data
    $data = $this->generate_expensive_data();
    
    // Cache for 12 hours
    set_transient('your_plugin_data', $data, 12 * HOUR_IN_SECONDS);
    
    return $data;
}

public function clear_cache(): void {
    delete_transient('your_plugin_data');
}
```

---

These examples cover the most common WordPress plugin development scenarios. Adapt them to fit your specific needs!
