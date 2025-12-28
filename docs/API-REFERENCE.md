# API Reference

Complete reference for MenuPilot's REST API endpoints, WordPress hooks, classes, and functions.

## Table of Contents

- [REST API Endpoints](#rest-api-endpoints)
- [WordPress Hooks](#wordpress-hooks)
- [Classes](#classes)
- [Constants](#constants)

---

## REST API Endpoints

All endpoints require authentication (logged-in user) and `manage_options` capability.

**Base URL:** `/wp-json/menupilot/v1`

### Export Menu

**Endpoint:** `POST /menupilot/v1/menus/export`

**Description:** Export a WordPress menu to JSON format.

**Authentication:** Required (admin only)

**Request Body:**
```json
{
  "menu_id": 2
}
```

**Parameters:**
- `menu_id` (integer, required) - Menu term ID to export

**Response:**
```json
{
  "success": true,
  "data": {
    "schema_version": "1.0",
    "plugin": {
      "name": "MenuPilot",
      "version": "1.0.0"
    },
    "export_context": {
      "site_url": "https://example.com",
      "wp_version": "6.4",
      "theme": "twentytwentyfour",
      "exported_at": "2025-12-28T10:30:00+00:00",
      "exported_by": "admin",
      "exported_by_id": 1
    },
    "menu": {
      "id": 2,
      "name": "Main Menu",
      "slug": "main-menu",
      "locations": ["primary"],
      "items": [...]
    },
    "extensions": {}
  }
}
```

**Error Response:**
```json
{
  "code": "rest_forbidden",
  "message": "Access denied. Administrator privileges required.",
  "data": {
    "status": 403
  }
}
```

---

### Import Menu

**Endpoint:** `POST /menupilot/v1/menus/import`

**Description:** Import a menu from JSON data.

**Authentication:** Required (admin only)

**Request Body:**
```json
{
  "menu_name": "Imported Menu",
  "menu_data": {
    "schema_version": "1.0",
    "plugin": {...},
    "export_context": {...},
    "menu": {...},
    "extensions": {}
  },
  "location": "primary"
}
```

**Parameters:**
- `menu_name` (string, required) - Name for the new menu
- `menu_data` (object, required) - Complete menu export data
- `location` (string, optional) - Theme location to assign menu to

**Response:**
```json
{
  "success": true,
  "menu_id": 5,
  "message": "Menu \"Imported Menu\" imported successfully! It has been assigned to the \"Primary\" location.",
  "edit_url": "/wp-admin/nav-menus.php?action=edit&menu=5"
}
```

**Error Response:**
```json
{
  "code": "import_failed",
  "message": "Failed to import menu. Please check the menu data and try again.",
  "data": {
    "status": 500
  }
}
```

---

### Get Mapping Options

**Endpoint:** `GET /menupilot/v1/menus/mapping-options`

**Description:** Get available content (pages, posts, taxonomies) for manual mapping during import.

**Authentication:** Required (admin only)

**Response:**
```json
{
  "success": true,
  "options": {
    "posts": [
      {
        "id": 1,
        "title": "Hello World",
        "slug": "hello-world"
      }
    ],
    "pages": [
      {
        "id": 2,
        "title": "Sample Page",
        "slug": "sample-page"
      }
    ],
    "taxonomies": [
      {
        "id": 1,
        "title": "Uncategorized",
        "slug": "uncategorized",
        "taxonomy": "category"
      }
    ]
  }
}
```

---

## WordPress Hooks

### Export Hooks

#### `menupilot_pre_export`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `null $pre_export` - Default null, return non-null to override export
- `int $menu_id` - Menu term ID

**Example:**
```php
add_filter('menupilot_pre_export', function($pre_export, $menu_id) {
    if ($menu_id === 5) {
        return false; // Block export
    }
    return $pre_export;
}, 10, 2);
```

#### `menupilot_export_plugin_info`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `array $plugin_info` - Plugin name and version

#### `menupilot_export_context`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `array $context` - Export context data

#### `menupilot_export_menu_item`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `array $item_data` - Menu item data
- `WP_Post $item` - WordPress menu item object

#### `menupilot_export_data`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `array $export_data` - Complete export data
- `WP_Term $menu` - Menu term object
- `array $menu_items` - Array of menu item objects

#### `menupilot_export_complete`
**Type:** Action  
**Priority:** 10  
**Parameters:**
- `array $export_data` - Complete export data
- `int $menu_id` - Menu term ID

### Import Hooks

#### `menupilot_pre_import`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `null $pre_import` - Default null
- `array $import_data` - Import data
- `string $menu_name` - Menu name
- `string $location` - Theme location

#### `menupilot_import_data`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `array $import_data` - Import data

#### `menupilot_import_menu_name`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `string $menu_name` - Menu name
- `array $import_data` - Import data

#### `menupilot_before_import`
**Type:** Action  
**Priority:** 10  
**Parameters:**
- `array $import_data` - Import data
- `string $menu_name` - Menu name
- `string $location` - Theme location

#### `menupilot_import_menu_item_data`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `array $item_data` - WordPress menu item data
- `array $item` - Raw item data from import
- `int $menu_id` - Menu term ID

#### `menupilot_before_import_item`
**Type:** Action  
**Priority:** 10  
**Parameters:**
- `array $item` - Raw item data
- `int $menu_id` - Menu term ID
- `int $parent_id` - Parent item ID

#### `menupilot_after_import_item`
**Type:** Action  
**Priority:** 10  
**Parameters:**
- `int $new_item_id` - New WordPress menu item ID
- `array $item` - Raw item data
- `int $menu_id` - Menu term ID

#### `menupilot_after_import`
**Type:** Action  
**Priority:** 10  
**Parameters:**
- `int $menu_id` - New menu ID
- `array $import_data` - Import data
- `array $item_map` - Old ID => New ID mapping

### Column Management Hooks

#### `menupilot_register_columns`
**Type:** Action  
**Priority:** 10  
**Description:** Fires when columns should be registered

#### `menupilot_preview_columns`
**Type:** Filter  
**Priority:** 10  
**Parameters:**
- `array $columns` - All registered columns

---

## Classes

### `MenuPilot\Menu_Exporter`

**Purpose:** Export menus to JSON format

**Methods:**

#### `export(int $menu_id): array|false`
Export a menu to array format.

**Parameters:**
- `$menu_id` (int) - Menu term ID

**Returns:** Export data array or false on failure

---

### `MenuPilot\Menu_Importer`

**Purpose:** Import menus from JSON data

**Methods:**

#### `import(array $import_data, string $menu_name, string $location = ''): int|false`
Import a menu from JSON data.

**Parameters:**
- `$import_data` (array) - Import data array
- `$menu_name` (string) - New menu name
- `$location` (string, optional) - Theme location

**Returns:** Menu term ID on success, false on failure

---

### `MenuPilot\Column_Manager`

**Purpose:** Manage preview columns for extensibility

**Methods:**

#### `register_column(string $id, array $args): bool`
Register a custom column.

**Parameters:**
- `$id` (string) - Column ID
- `$args` (array) - Column arguments

**Returns:** True on success, false if ID already exists

#### `get_columns(): array`
Get all registered columns.

**Returns:** Array of columns sorted by order

#### `get_columns_for_js(): array`
Get columns for JavaScript.

**Returns:** Column ID => Label mapping

---

### `MenuPilot\Settings`

**Purpose:** Manage plugin settings

**Methods:**

#### `get_option(string $key, mixed $default = null): mixed`
Get a specific option.

#### `update_option(string $key, mixed $value): bool`
Update a specific option.

---

### `MenuPilot\REST\Rest_Controller`

**Purpose:** Handle REST API endpoints

**Methods:**

#### `register_routes(): void`
Register REST API routes.

#### `export_menu(WP_REST_Request $request): WP_REST_Response|WP_Error`
Handle menu export request.

#### `import_menu(WP_REST_Request $request): WP_REST_Response|WP_Error`
Handle menu import request.

#### `get_mapping_options(WP_REST_Request $request): WP_REST_Response`
Get available content for mapping.

#### `check_admin_permissions(): bool|WP_Error`
Check if user has admin permissions.

---

## Constants

### `MENUPILOT_VERSION`
Plugin version number (string)

### `MENUPILOT_PLUGIN_DIR`
Plugin directory path (string)

### `MENUPILOT_PLUGIN_URL`
Plugin directory URL (string)

### `MENUPILOT_PLUGIN_BASENAME`
Plugin basename (string)

---

## JavaScript API

### Localized Data

MenuPilot exposes data to JavaScript via `menupilot` object:

```javascript
menupilot = {
    ajaxurl: '/wp-admin/admin-ajax.php',
    restUrl: '/wp-json/menupilot/v1',
    nonce: '...',
    siteUrl: 'https://example.com',
    registeredLocations: {...},
    previewColumns: {...}
}
```

### Usage Example

```javascript
// Export menu
fetch(menupilot.restUrl + '/menus/export', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': menupilot.nonce
    },
    body: JSON.stringify({
        menu_id: 2
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Download JSON file
        const blob = new Blob([JSON.stringify(data.data, null, 2)], {
            type: 'application/json'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'menu-export.json';
        a.click();
    }
});
```

---

## Error Codes

### REST API Errors

- `rest_unauthorized` (401) - User not logged in
- `rest_forbidden` (403) - User lacks `manage_options` capability
- `rest_invalid_param` (400) - Invalid request parameter
- `export_failed` (500) - Export operation failed
- `import_failed` (500) - Import operation failed

---

**See Also:**
- [Hooks Reference](./09-hooks-reference.md) - Detailed hook documentation
- [Developer Guide](./05-developer-guide.md) - Usage examples
