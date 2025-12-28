# MenuPilot Extension Hooks Documentation

## Overview
MenuPilot provides a comprehensive hook system for extending functionality, especially useful for creating Pro versions or third-party extensions.

## Table of Contents
1. [Export Hooks](#export-hooks)
2. [Import Hooks](#import-hooks)
3. [Column Management](#column-management)
4. [JavaScript Hooks](#javascript-hooks)
5. [Pro Version Examples](#pro-version-examples)

---

## Export Hooks

### `menupilot_pre_export`
**Type:** Filter  
**Description:** Allows you to modify or block an export before it begins.  
**Parameters:**
- `null` - Default value (return non-null to override export)
- `int $menu_id` - Menu term ID being exported

**Example:**
```php
add_filter('menupilot_pre_export', function($pre_export, $menu_id) {
    // Block export for specific menus
    if ($menu_id === 5) {
        return false;
    }
    return $pre_export;
}, 10, 2);
```

---

### `menupilot_export_plugin_info`
**Type:** Filter  
**Description:** Modify plugin information in export data.  
**Parameters:**
- `array $plugin_info` - Plugin name and version

**Example:**
```php
add_filter('menupilot_export_plugin_info', function($plugin_info) {
    $plugin_info['pro_version'] = '2.0.0';
    $plugin_info['license'] = 'pro';
    return $plugin_info;
});
```

---

### `menupilot_export_context`
**Type:** Filter  
**Description:** Add custom context data to exports (environment info, custom metadata, etc.).  
**Parameters:**
- `array $context` - Export context (site_url, wp_version, theme, exported_at, exported_by)

**Example:**
```php
add_filter('menupilot_export_context', function($context) {
    $context['environment'] = wp_get_environment_type();
    $context['custom_field'] = get_option('my_plugin_setting');
    return $context;
});
```

---

### `menupilot_export_menu_item`
**Type:** Filter  
**Description:** Modify individual menu item data during export. Perfect for adding custom metadata.  
**Parameters:**
- `array $item_data` - Menu item data array
- `WP_Post $item` - WordPress menu item post object

**Example:**
```php
add_filter('menupilot_export_menu_item', function($item_data, $item) {
    // Add SEO metadata
    $item_data['seo'] = array(
        'title' => get_post_meta($item->ID, '_menu_item_seo_title', true),
        'nofollow' => get_post_meta($item->ID, '_menu_item_nofollow', true),
    );
    
    // Add visibility rules
    $item_data['visibility'] = get_post_meta($item->ID, '_menu_item_visibility', true);
    
    return $item_data;
}, 10, 2);
```

---

### `menupilot_export_data`
**Type:** Filter  
**Description:** Modify the complete export data structure before returning.  
**Parameters:**
- `array $export_data` - Complete export data
- `WP_Term $menu` - Menu term object
- `array $menu_items` - Array of menu item post objects

**Example:**
```php
add_filter('menupilot_export_data', function($export_data, $menu, $menu_items) {
    // Add Pro extension data
    $export_data['extensions']['menupilot_pro'] = array(
        'version' => '2.0.0',
        'features' => array('seo', 'visibility', 'icons'),
        'settings' => get_option('menupilot_pro_settings'),
    );
    
    return $export_data;
}, 10, 3);
```

---

### `menupilot_export_complete`
**Type:** Action  
**Description:** Fires after export is complete. Useful for logging or analytics.  
**Parameters:**
- `array $export_data` - Complete export data
- `int $menu_id` - Menu term ID

**Example:**
```php
add_action('menupilot_export_complete', function($export_data, $menu_id) {
    // Log export
    error_log("Menu {$menu_id} exported by " . wp_get_current_user()->user_login);
    
    // Track analytics
    do_action('my_analytics_track', 'menu_export', array('menu_id' => $menu_id));
}, 10, 2);
```

---

## Import Hooks

### `menupilot_pre_import`
**Type:** Filter  
**Description:** Allows you to modify or block an import before it begins.  
**Parameters:**
- `null` - Default value (return non-null to override import)
- `array $import_data` - Import data array
- `string $menu_name` - New menu name
- `string $location` - Theme location (optional)

**Example:**
```php
add_filter('menupilot_pre_import', function($pre_import, $import_data, $menu_name, $location) {
    // Validate Pro license before import
    if (isset($import_data['extensions']['menupilot_pro']) && !has_pro_license()) {
        wp_die('Pro license required to import this menu.');
    }
    return $pre_import;
}, 10, 4);
```

---

### `menupilot_import_data`
**Type:** Filter  
**Description:** Modify import data before processing.  
**Parameters:**
- `array $import_data` - Complete import data

**Example:**
```php
add_filter('menupilot_import_data', function($import_data) {
    // Process Pro extension data
    if (isset($import_data['extensions']['menupilot_pro'])) {
        update_option('menupilot_pro_settings', $import_data['extensions']['menupilot_pro']['settings']);
    }
    return $import_data;
});
```

---

### `menupilot_import_menu_name`
**Type:** Filter  
**Description:** Modify menu name before creation.  
**Parameters:**
- `string $menu_name` - Menu name
- `array $import_data` - Import data

**Example:**
```php
add_filter('menupilot_import_menu_name', function($menu_name, $import_data) {
    // Add prefix to imported menus
    return '[Imported] ' . $menu_name;
}, 10, 2);
```

---

### `menupilot_before_import`
**Type:** Action  
**Description:** Fires before import begins, after validation.  
**Parameters:**
- `array $import_data` - Import data
- `string $menu_name` - Menu name
- `string $location` - Theme location

**Example:**
```php
add_action('menupilot_before_import', function($import_data, $menu_name, $location) {
    // Backup existing menu in location
    if (!empty($location)) {
        $locations = get_nav_menu_locations();
        if (isset($locations[$location])) {
            do_action('backup_menu', $locations[$location]);
        }
    }
}, 10, 3);
```

---

### `menupilot_import_menu_item_data`
**Type:** Filter  
**Description:** Modify menu item data before creation.  
**Parameters:**
- `array $item_data` - WordPress menu item data (menu-item-* keys)
- `array $item` - Raw item data from import
- `int $menu_id` - New menu ID

**Example:**
```php
add_filter('menupilot_import_menu_item_data', function($item_data, $item, $menu_id) {
    // Import SEO metadata
    if (isset($item['seo'])) {
        // Store for later use in after_import_item hook
        set_transient('menu_item_seo_' . $item['id'], $item['seo'], 60);
    }
    return $item_data;
}, 10, 3);
```

---

### `menupilot_before_import_item`
**Type:** Action  
**Description:** Fires before a menu item is created.  
**Parameters:**
- `array $item` - Raw item data
- `int $menu_id` - Menu term ID
- `int $parent_id` - Parent menu item ID

**Example:**
```php
add_action('menupilot_before_import_item', function($item, $menu_id, $parent_id) {
    // Pre-process item
    error_log("Importing item: {$item['title']}");
}, 10, 3);
```

---

### `menupilot_after_import_item`
**Type:** Action  
**Description:** Fires after a menu item is created. Perfect for adding custom post meta.  
**Parameters:**
- `int $new_item_id` - New WordPress menu item ID
- `array $item` - Raw item data from import
- `int $menu_id` - Menu term ID

**Example:**
```php
add_action('menupilot_after_import_item', function($new_item_id, $item, $menu_id) {
    // Import SEO metadata
    if ($seo = get_transient('menu_item_seo_' . $item['id'])) {
        update_post_meta($new_item_id, '_menu_item_seo_title', $seo['title']);
        update_post_meta($new_item_id, '_menu_item_nofollow', $seo['nofollow']);
        delete_transient('menu_item_seo_' . $item['id']);
    }
    
    // Import visibility rules
    if (isset($item['visibility'])) {
        update_post_meta($new_item_id, '_menu_item_visibility', $item['visibility']);
    }
}, 10, 3);
```

---

### `menupilot_after_import`
**Type:** Action  
**Description:** Fires after import is complete.  
**Parameters:**
- `int $menu_id` - New menu ID
- `array $import_data` - Import data
- `array $item_map` - Old ID => New ID mapping

**Example:**
```php
add_action('menupilot_after_import', function($menu_id, $import_data, $item_map) {
    // Log import
    error_log("Menu imported: {$menu_id}, " . count($item_map) . " items");
    
    // Send notification
    wp_mail(get_option('admin_email'), 'Menu Imported', "Menu {$menu_id} has been imported.");
}, 10, 3);
```

---

## Column Management

### Registering Custom Columns

Use the `Column_Manager` class to add custom columns to the import preview.

**Example:**
```php
add_action('menupilot_register_columns', function() {
    // Register SEO column
    MenuPilot\Column_Manager::register_column('seo_status', array(
        'label' => 'SEO',
        'order' => 35, // After "Auto Status" (30), before "Map To" (40)
        'visible' => true,
        'render_callback' => 'render_seo_column', // Optional: for server-side rendering
    ));
    
    // Register Visibility column
    MenuPilot\Column_Manager::register_column('visibility', array(
        'label' => 'Visibility',
        'order' => 36,
        'visible' => true,
    ));
});
```

---

### `menupilot_register_columns`
**Type:** Action  
**Description:** Fires when columns should be registered.  
**No parameters**

**Example:** See above

---

### `menupilot_preview_columns`
**Type:** Filter  
**Description:** Modify all registered columns.  
**Parameters:**
- `array $columns` - All registered columns (sorted by order)

**Example:**
```php
add_filter('menupilot_preview_columns', function($columns) {
    // Hide a column conditionally
    if (!current_user_can('manage_seo')) {
        unset($columns['seo_status']);
    }
    return $columns;
});
```

---

## JavaScript Hooks

### Accessing Columns in JavaScript

Columns are available via `menupilot.previewColumns`:

```javascript
// Example: Add custom column data to preview
const columns = menupilot.previewColumns;
console.log(columns); // { title: "Title", type: "Type", ... }

// Pro version can add custom column rendering
if (columns.seo_status) {
    // Render custom SEO column
}
```

---

## Pro Version Examples

### Complete Pro Extension Example

```php
<?php
/**
 * Plugin Name: MenuPilot Pro
 * Description: Premium extension for MenuPilot
 */

// Add Pro features to export
add_filter('menupilot_export_menu_item', function($item_data, $item) {
    $item_data['pro'] = array(
        'seo_title' => get_post_meta($item->ID, '_mp_seo_title', true),
        'icon' => get_post_meta($item->ID, '_mp_icon', true),
        'visibility' => get_post_meta($item->ID, '_mp_visibility', true),
    );
    return $item_data;
}, 10, 2);

// Mark export as Pro
add_filter('menupilot_export_data', function($export_data) {
    $export_data['extensions']['menupilot_pro'] = array(
        'version' => '2.0.0',
        'features' => array('seo', 'icons', 'visibility'),
    );
    return $export_data;
});

// Validate Pro license on import
add_filter('menupilot_pre_import', function($pre_import, $import_data) {
    if (isset($import_data['extensions']['menupilot_pro'])) {
        if (!function_exists('menupilot_pro_is_licensed') || !menupilot_pro_is_licensed()) {
            wp_die('MenuPilot Pro license required to import this menu.');
        }
    }
    return $pre_import;
}, 10, 2);

// Import Pro metadata
add_action('menupilot_after_import_item', function($new_item_id, $item) {
    if (isset($item['pro'])) {
        update_post_meta($new_item_id, '_mp_seo_title', $item['pro']['seo_title']);
        update_post_meta($new_item_id, '_mp_icon', $item['pro']['icon']);
        update_post_meta($new_item_id, '_mp_visibility', $item['pro']['visibility']);
    }
}, 10, 2);

// Register custom columns
add_action('menupilot_register_columns', function() {
    MenuPilot\Column_Manager::register_column('pro_seo', array(
        'label' => 'SEO Optimized',
        'order' => 35,
        'visible' => true,
    ));
    
    MenuPilot\Column_Manager::register_column('pro_icon', array(
        'label' => 'Icon',
        'order' => 36,
        'visible' => true,
    ));
});
```

---

## Hook Priority Best Practices

1. **Early validation**: Use priority `5` for validation hooks
2. **Default behavior**: Use priority `10` (default)
3. **Late modifications**: Use priority `20` or higher
4. **Final cleanup**: Use priority `100` or higher

## Testing Your Extensions

```php
// Test export hooks
add_action('menupilot_export_complete', function($data) {
    error_log('Export data: ' . print_r($data, true));
});

// Test import hooks
add_action('menupilot_after_import', function($menu_id, $import_data, $item_map) {
    error_log("Imported menu {$menu_id} with " . count($item_map) . " items");
});
```

---

## Need Help?

For questions or support with extending MenuPilot, please:
1. Check the examples above
2. Review the source code in `includes/class-menu-exporter.php` and `includes/class-menu-importer.php`
3. Contact support at support@menupilot.com

