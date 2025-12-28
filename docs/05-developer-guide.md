# Developer Guide

Complete guide for developers extending MenuPilot.

## Table of Contents

1. [Overview](#overview)
2. [Hooks Reference](#hooks-reference)
3. [Column Management](#column-management)
4. [Extending Export](#extending-export)
5. [Extending Import](#extending-import)
6. [Building Pro Version](#building-pro-version)
7. [Examples](#examples)

---

## Overview

MenuPilot is built with extensibility in mind. The plugin provides:

- **15 WordPress hooks** for customization
- **Column Manager** for dynamic preview columns
- **JSON extensions field** for Pro data
- **Filter/action system** throughout

### Architecture Principles

- ✅ Standard WordPress hooks API
- ✅ No breaking changes
- ✅ Backward compatible
- ✅ Zero performance impact when unused
- ✅ Follows WordPress coding standards

---

## Hooks Reference

### Export Hooks

#### `menupilot_pre_export`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `null $pre_export` - Default null, return non-null to override
- `int $menu_id` - Menu term ID

**Usage:**
```php
add_filter('menupilot_pre_export', function($pre_export, $menu_id) {
    // Block export for specific menus
    if ($menu_id === 5) {
        return false;
    }
    return $pre_export;
}, 10, 2);
```

#### `menupilot_export_plugin_info`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `array $plugin_info` - Plugin name and version

**Usage:**
```php
add_filter('menupilot_export_plugin_info', function($plugin_info) {
    $plugin_info['pro_version'] = '2.0.0';
    return $plugin_info;
});
```

#### `menupilot_export_context`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `array $context` - Export context data

**Usage:**
```php
add_filter('menupilot_export_context', function($context) {
    $context['environment'] = wp_get_environment_type();
    return $context;
});
```

#### `menupilot_export_menu_item`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `array $item_data` - Menu item data
- `WP_Post $item` - WordPress menu item object

**Usage:**
```php
add_filter('menupilot_export_menu_item', function($item_data, $item) {
    // Add custom metadata
    $item_data['custom_field'] = get_post_meta($item->ID, '_custom', true);
    return $item_data;
}, 10, 2);
```

#### `menupilot_export_data`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `array $export_data` - Complete export data
- `WP_Term $menu` - Menu term object
- `array $menu_items` - Array of menu item objects

**Usage:**
```php
add_filter('menupilot_export_data', function($export_data, $menu, $menu_items) {
    $export_data['extensions']['my_plugin'] = array(
        'version' => '1.0.0',
        'data' => get_option('my_plugin_settings'),
    );
    return $export_data;
}, 10, 3);
```

#### `menupilot_export_complete`
**Type:** Action  
**Priority:** 10  
**Parameters:**
- `array $export_data` - Complete export data
- `int $menu_id` - Menu term ID

**Usage:**
```php
add_action('menupilot_export_complete', function($export_data, $menu_id) {
    error_log("Menu {$menu_id} exported");
});
```

### Import Hooks

#### `menupilot_pre_import`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `null $pre_import` - Default null
- `array $import_data` - Import data
- `string $menu_name` - Menu name
- `string $location` - Theme location

**Usage:**
```php
add_filter('menupilot_pre_import', function($pre_import, $import_data, $menu_name, $location) {
    // Validate license
    if (isset($import_data['extensions']['pro']) && !has_license()) {
        wp_die('License required');
    }
    return $pre_import;
}, 10, 4);
```

#### `menupilot_import_data`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `array $import_data` - Import data

**Usage:**
```php
add_filter('menupilot_import_data', function($import_data) {
    // Process extension data
    if (isset($import_data['extensions']['my_plugin'])) {
        update_option('my_settings', $import_data['extensions']['my_plugin']);
    }
    return $import_data;
});
```

#### `menupilot_import_menu_name`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `string $menu_name` - Menu name
- `array $import_data` - Import data

**Usage:**
```php
add_filter('menupilot_import_menu_name', function($menu_name, $import_data) {
    return '[Imported] ' . $menu_name;
}, 10, 2);
```

#### `menupilot_before_import`
**Type:** Action  
**Priority:** 10  
**Parameters:**
- `array $import_data` - Import data
- `string $menu_name` - Menu name
- `string $location` - Theme location

**Usage:**
```php
add_action('menupilot_before_import', function($import_data, $menu_name, $location) {
    // Backup existing menu
    do_action('backup_menu', $location);
}, 10, 3);
```

#### `menupilot_import_menu_item_data`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `array $item_data` - WordPress menu item data
- `array $item` - Raw item data from import
- `int $menu_id` - Menu term ID

**Usage:**
```php
add_filter('menupilot_import_menu_item_data', function($item_data, $item, $menu_id) {
    // Store custom data for after_import_item hook
    if (isset($item['custom'])) {
        set_transient('item_custom_' . $item['id'], $item['custom'], 60);
    }
    return $item_data;
}, 10, 3);
```

#### `menupilot_before_import_item`
**Type:** Action  
**Priority:** 10  
**Parameters:**
- `array $item` - Raw item data
- `int $menu_id` - Menu term ID
- `int $parent_id` - Parent item ID

**Usage:**
```php
add_action('menupilot_before_import_item', function($item, $menu_id, $parent_id) {
    error_log("Importing: {$item['title']}");
}, 10, 3);
```

#### `menupilot_after_import_item`
**Type:** Action  
**Priority:** 10  
**Parameters:**
- `int $new_item_id` - New WordPress menu item ID
- `array $item` - Raw item data
- `int $menu_id` - Menu term ID

**Usage:**
```php
add_action('menupilot_after_import_item', function($new_item_id, $item, $menu_id) {
    // Import custom metadata
    if ($custom = get_transient('item_custom_' . $item['id'])) {
        update_post_meta($new_item_id, '_custom', $custom);
        delete_transient('item_custom_' . $item['id']);
    }
}, 10, 3);
```

#### `menupilot_after_import`
**Type:** Action  
**Priority:** 10  
**Parameters:**
- `int $menu_id` - New menu ID
- `array $import_data` - Import data
- `array $item_map` - Old ID => New ID mapping

**Usage:**
```php
add_action('menupilot_after_import', function($menu_id, $import_data, $item_map) {
    error_log("Imported menu {$menu_id} with " . count($item_map) . " items");
}, 10, 3);
```

---

## Column Management

### Registering Custom Columns

```php
add_action('menupilot_register_columns', function() {
    MenuPilot\Column_Manager::register_column('seo_status', array(
        'label' => 'SEO Status',
        'order' => 35, // After Auto Status (30), before Map To (40)
        'visible' => true,
    ));
});
```

### Column Options

- `label` (string, required) - Column header text
- `order` (int, default: 100) - Display order
- `visible` (bool, default: true) - Show in preview
- `render_callback` (callable, optional) - Server-side rendering
- `export_callback` (callable, optional) - Export data handler
- `import_callback` (callable, optional) - Import data handler

### Filtering Columns

```php
add_filter('menupilot_preview_columns', function($columns) {
    // Hide column conditionally
    if (!current_user_can('manage_seo')) {
        unset($columns['seo_status']);
    }
    return $columns;
});
```

### Accessing Columns in JavaScript

```javascript
// Columns available via menupilot.previewColumns
const columns = menupilot.previewColumns;
console.log(columns); // { title: "Title", type: "Type", ... }
```

---

## Extending Export

### Adding Custom Data to Export

```php
add_filter('menupilot_export_menu_item', function($item_data, $item) {
    // Add SEO data
    $item_data['seo'] = array(
        'title' => get_post_meta($item->ID, '_seo_title', true),
        'description' => get_post_meta($item->ID, '_seo_desc', true),
    );
    
    // Add icon data
    $item_data['icon'] = get_post_meta($item->ID, '_icon', true);
    
    return $item_data;
}, 10, 2);
```

### Marking Export as Pro

```php
add_filter('menupilot_export_data', function($export_data) {
    $export_data['extensions']['menupilot_pro'] = array(
        'version' => '2.0.0',
        'features' => array('seo', 'icons', 'visibility'),
    );
    return $export_data;
});
```

---

## Extending Import

### Importing Custom Data

```php
add_action('menupilot_after_import_item', function($new_item_id, $item, $menu_id) {
    // Import SEO data
    if (isset($item['seo'])) {
        update_post_meta($new_item_id, '_seo_title', $item['seo']['title']);
        update_post_meta($new_item_id, '_seo_desc', $item['seo']['description']);
    }
    
    // Import icon
    if (isset($item['icon'])) {
        update_post_meta($new_item_id, '_icon', $item['icon']);
    }
}, 10, 3);
```

### Validating Import

```php
add_filter('menupilot_pre_import', function($pre_import, $import_data) {
    // Check for Pro features
    if (isset($import_data['extensions']['menupilot_pro'])) {
        if (!function_exists('menupilot_pro_is_licensed')) {
            wp_die('MenuPilot Pro required');
        }
    }
    return $pre_import;
}, 10, 2);
```

---

## Building Pro Version

### Plugin Structure

```
menupilot-pro/
├── menupilot-pro.php
├── includes/
│   ├── class-seo.php
│   ├── class-icons.php
│   └── class-visibility.php
└── assets/
    ├── js/pro-columns.js
    └── css/pro-admin.css
```

### Main Plugin File

```php
<?php
/**
 * Plugin Name: MenuPilot Pro
 * Description: Premium extension for MenuPilot
 * Version: 2.0.0
 */

// Check for MenuPilot
add_action('plugins_loaded', function() {
    if (!class_exists('MenuPilot\Column_Manager')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>MenuPilot Pro requires MenuPilot (free) to be installed.</p></div>';
        });
        return;
    }
    
    // Initialize Pro features
    require_once 'includes/class-seo.php';
    require_once 'includes/class-icons.php';
    require_once 'includes/class-visibility.php';
});
```

### Export Pro Features

```php
add_filter('menupilot_export_menu_item', function($item_data, $item) {
    $item_data['pro'] = array(
        'seo_title' => get_post_meta($item->ID, '_mp_seo_title', true),
        'icon' => get_post_meta($item->ID, '_mp_icon', true),
        'visibility' => get_post_meta($item->ID, '_mp_visibility', true),
    );
    return $item_data;
}, 10, 2);
```

### Import Pro Features

```php
add_action('menupilot_after_import_item', function($new_item_id, $item) {
    if (isset($item['pro'])) {
        update_post_meta($new_item_id, '_mp_seo_title', $item['pro']['seo_title']);
        update_post_meta($new_item_id, '_mp_icon', $item['pro']['icon']);
        update_post_meta($new_item_id, '_mp_visibility', $item['pro']['visibility']);
    }
}, 10, 2);
```

### Register Pro Columns

```php
add_action('menupilot_register_columns', function() {
    MenuPilot\Column_Manager::register_column('pro_seo', array(
        'label' => 'SEO',
        'order' => 35,
    ));
    
    MenuPilot\Column_Manager::register_column('pro_icon', array(
        'label' => 'Icon',
        'order' => 36,
    ));
});
```

---

## Examples

### Example 1: Add SEO Metadata

```php
// Export SEO
add_filter('menupilot_export_menu_item', function($item_data, $item) {
    $item_data['seo'] = array(
        'title' => get_post_meta($item->ID, '_seo_title', true),
        'nofollow' => get_post_meta($item->ID, '_nofollow', true),
    );
    return $item_data;
}, 10, 2);

// Import SEO
add_action('menupilot_after_import_item', function($new_id, $item) {
    if (isset($item['seo'])) {
        update_post_meta($new_id, '_seo_title', $item['seo']['title']);
        update_post_meta($new_id, '_nofollow', $item['seo']['nofollow']);
    }
}, 10, 2);
```

### Example 2: Log All Exports

```php
add_action('menupilot_export_complete', function($export_data, $menu_id) {
    $log = array(
        'menu_id' => $menu_id,
        'menu_name' => $export_data['menu']['name'],
        'items_count' => count($export_data['menu']['items']),
        'exported_by' => wp_get_current_user()->user_login,
        'timestamp' => current_time('mysql'),
    );
    
    $logs = get_option('menupilot_export_logs', array());
    $logs[] = $log;
    update_option('menupilot_export_logs', $logs);
}, 10, 2);
```

### Example 3: Auto-Backup Before Import

```php
add_action('menupilot_before_import', function($import_data, $menu_name, $location) {
    if (!empty($location)) {
        $locations = get_nav_menu_locations();
        if (isset($locations[$location])) {
            $existing_menu_id = $locations[$location];
            $exporter = new MenuPilot\Menu_Exporter();
            $backup = $exporter->export($existing_menu_id);
            
            $backups = get_option('menupilot_auto_backups', array());
            $backups[] = array(
                'menu_id' => $existing_menu_id,
                'data' => $backup,
                'timestamp' => current_time('mysql'),
            );
            update_option('menupilot_auto_backups', $backups);
        }
    }
}, 10, 3);
```

---

## Testing Your Extensions

### Debug Mode

```php
// Enable debug logging
add_action('menupilot_export_complete', function($data) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('MenuPilot Export: ' . print_r($data, true));
    }
});

add_action('menupilot_after_import', function($menu_id, $data, $item_map) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("MenuPilot Import: Menu {$menu_id}, Items: " . count($item_map));
    }
}, 10, 3);
```

### Unit Testing

```php
// Test export hook
function test_export_hook() {
    $result = apply_filters('menupilot_export_menu_item', array(), new WP_Post());
    assert(isset($result['custom_field']));
}
```

---

## Best Practices

1. **Always check for MenuPilot** before using hooks
2. **Use transients** for temporary data between hooks
3. **Validate data** before saving
4. **Log errors** for debugging
5. **Document your hooks** for other developers
6. **Test backward compatibility** with existing exports
7. **Use proper priorities** (10 = default, 5 = early, 20 = late)

---

**Next:** [Architecture](./06-architecture.md)

