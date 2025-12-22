# API Reference

This document provides a comprehensive reference for MenuPilot's API, hooks, filters, and functions.

## Table of Contents

- [Actions](#actions)
- [Filters](#filters)
- [Functions](#functions)
- [Classes](#classes)

## Actions

### `menupilot_init`

Fires after plugin initialization.

**Parameters:**
- `$instance` (Init) - Plugin instance

**Example:**
```php
add_action('menupilot_init', function($instance) {
    // Your code here
});
```

### `menupilot_activate`

Fires on plugin activation.

**Example:**
```php
add_action('menupilot_activate', function() {
    // Activation tasks
});
```

### `menupilot_deactivate`

Fires on plugin deactivation.

**Example:**
```php
add_action('menupilot_deactivate', function() {
    // Deactivation tasks
});
```

### `menupilot_uninstall`

Fires during plugin uninstallation.

**Example:**
```php
add_action('menupilot_uninstall', function() {
    // Cleanup tasks
});
```

### `menupilot_init_integrations`

Fires when integrations should be initialized.

**Example:**
```php
add_action('menupilot_init_integrations', function() {
    if (class_exists('WooCommerce')) {
        new \MenuPilot\Integrations\WooCommerce();
    }
});
```

## Filters

### `menupilot_load_assets`

Filter whether to load plugin assets on the current page.

**Parameters:**
- `$load` (bool) - Whether to load assets (default: true)

**Returns:** (bool) Whether to load assets

**Example:**
```php
add_filter('menupilot_load_assets', function($load) {
    // Don't load on specific pages
    if (is_page('example')) {
        return false;
    }
    return $load;
});
```

### `menupilot_settings_fields`

Filter to add or modify settings fields.

**Parameters:**
- `$fields` (array) - Settings fields array

**Returns:** (array) Modified settings fields

**Example:**
```php
add_filter('menupilot_settings_fields', function($fields) {
    $fields[] = array(
        'field_id' => 'my_setting',
        'type' => 'text',
        'label' => 'My Setting',
        'tab' => 'general',
        'section' => 'default',
        'priority' => 10,
    );
    return $fields;
});
```

### `menupilot_client_ip`

Filter the detected client IP address.

**Parameters:**
- `$ip` (string) - The detected IP address

**Returns:** (string) Filtered IP address

**Example:**
```php
add_filter('menupilot_client_ip', function($ip) {
    // Custom IP detection logic
    return $ip;
});
```

## Functions

### `MenuPilot\get_client_ip()`

Get the client's IP address.

**Returns:** (string) Client IP address

**Example:**
```php
$ip = \MenuPilot\get_client_ip();
```

### `MenuPilot\log_debug()`

Log a debug message.

**Parameters:**
- `$message` (string) - Message to log
- `$level` (string) - Log level (error, warning, info, debug)

**Example:**
```php
\MenuPilot\log_debug('Debug message', 'info');
```

### `MenuPilot\is_debug_mode()`

Check if plugin debug mode is enabled.

**Returns:** (bool) Whether debug mode is enabled

**Example:**
```php
if (\MenuPilot\is_debug_mode()) {
    // Debug code
}
```

## Classes

### `MenuPilot\Init`

Main plugin initialization class.

**Methods:**

#### `get_instance()`

Get the singleton instance.

**Returns:** (Init) Plugin instance

#### `init()`

Initialize the plugin.

**Returns:** (void)

#### `activate()`

Plugin activation handler.

**Returns:** (void)

#### `deactivate()`

Plugin deactivation handler.

**Returns:** (void)

### `MenuPilot\Settings`

Plugin settings management class.

**Methods:**

#### `get_settings()`

Get all plugin settings.

**Returns:** (array) Settings array

#### `get_option($key, $default = null)`

Get a specific option.

**Parameters:**
- `$key` (string) - Option key
- `$default` (mixed) - Default value

**Returns:** (mixed) Option value

#### `update_option($key, $value)`

Update a specific option.

**Parameters:**
- `$key` (string) - Option key
- `$value` (mixed) - Option value

**Returns:** (bool) Success

## Constants

### `MENUPILOT_VERSION`

Plugin version number.

### `MENUPILOT_PLUGIN_DIR`

Plugin directory path.

### `MENUPILOT_PLUGIN_URL`

Plugin directory URL.

### `MENUPILOT_PLUGIN_BASENAME`

Plugin basename.

## Usage Examples

### Adding Custom Settings

```php
add_filter('menupilot_settings_fields', function($fields) {
    $fields[] = array(
        'field_id' => 'api_key',
        'type' => 'text',
        'label' => 'API Key',
        'description' => 'Enter your API key',
        'tab' => 'general',
        'section' => 'api',
        'priority' => 10,
    );
    return $fields;
});
```

### Creating an Integration

```php
namespace MenuPilot\Integrations;

class My_Integration {
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Integration logic
    }
}

// Register the integration
add_action('menupilot_init_integrations', function() {
    if (class_exists('My_Plugin')) {
        new \MenuPilot\Integrations\My_Integration();
    }
});
```
