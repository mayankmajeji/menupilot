# Architecture & Technical Details

Technical architecture and code structure of MenuPilot.

## Table of Contents

1. [Overview](#overview)
2. [File Structure](#file-structure)
3. [Class Structure](#class-structure)
4. [Data Flow](#data-flow)
5. [API Endpoints](#api-endpoints)
6. [Database Schema](#database-schema)
7. [Security](#security)
8. [Performance](#performance)

---

## Overview

MenuPilot follows WordPress coding standards and best practices:

- **PSR-4 Autoloading** - Modern PHP namespace structure
- **WordPress Hooks** - Extensible via filters and actions
- **REST API** - Modern API endpoints
- **No Database Modifications** - Uses WordPress core APIs
- **Type Declarations** - PHP 7.4+ type hints
- **Strict Typing** - `declare(strict_types=1)`

---

## File Structure

```
menupilot/
├── menupilot.php                 # Main plugin file
├── includes/
│   ├── class-init.php           # Plugin initialization
│   ├── class-menu-exporter.php  # Export logic
│   ├── class-menu-importer.php  # Import logic
│   ├── class-column-manager.php # Column management
│   ├── class-settings.php       # Settings management
│   ├── admin/
│   │   ├── class-settings-page.php
│   │   ├── class-tools-page.php
│   │   ├── class-ajax-handler.php
│   │   └── templates/
│   │       ├── header.php
│   │       └── ...
│   └── rest/
│       └── class-rest-controller.php
├── assets/
│   ├── css/
│   │   ├── admin.scss
│   │   ├── admin.css
│   │   └── modules/
│   │       ├── _modal.scss
│   │       └── ...
│   └── js/
│       ├── admin.js
│       └── admin-pages.js
├── docs/                        # Documentation
└── vendor/                      # Composer dependencies (if any)
```

---

## Class Structure

### Core Classes

#### `MenuPilot\Init`
**Purpose:** Main plugin initialization  
**Responsibilities:**
- Register admin menus
- Enqueue assets
- Initialize components
- Handle activation/deactivation

**Key Methods:**
- `init()` - Initialize plugin
- `add_admin_menu()` - Register admin pages
- `enqueue_admin_assets()` - Load CSS/JS
- `activate()` - Plugin activation
- `deactivate()` - Plugin deactivation

#### `MenuPilot\Menu_Exporter`
**Purpose:** Export menus to JSON  
**Responsibilities:**
- Build export data structure
- Collect menu items
- Format JSON output
- Apply export hooks

**Key Methods:**
- `export($menu_id)` - Main export method
- `build_menu_data()` - Build menu structure
- `build_menu_item_data()` - Build item data
- `get_object_slug()` - Get slug for matching

#### `MenuPilot\Menu_Importer`
**Purpose:** Import menus from JSON  
**Responsibilities:**
- Parse import data
- Create menus and items
- Handle URL normalization
- Apply import hooks

**Key Methods:**
- `import($import_data, $menu_name, $location)` - Main import method
- `create_menu_item()` - Create individual item
- `normalize_url()` - Replace source URLs
- `find_post_by_slug()` - Auto-matching logic
- `find_term_by_slug()` - Auto-matching logic

#### `MenuPilot\Column_Manager`
**Purpose:** Manage preview columns  
**Responsibilities:**
- Register custom columns
- Order columns
- Filter columns
- Expose to JavaScript

**Key Methods:**
- `register_column($id, $args)` - Register column
- `get_columns()` - Get all columns
- `get_columns_for_js()` - JavaScript-ready format
- `init_default_columns()` - Setup defaults

#### `MenuPilot\Settings`
**Purpose:** Manage plugin settings  
**Responsibilities:**
- Store/retrieve settings
- Default options
- Settings validation

**Key Methods:**
- `get_option($key)` - Get setting
- `update_option($key, $value)` - Update setting
- `add_default_options()` - Set defaults

### Admin Classes

#### `MenuPilot\Admin\Settings_Page`
**Purpose:** Render Settings page (Export/Import)  
**Responsibilities:**
- Display export form
- Display import form
- Handle tab navigation

#### `MenuPilot\Admin\Tools_Page`
**Purpose:** Render Tools page  
**Responsibilities:**
- Display plugin settings
- Import/Export settings tabs
- Reset settings

#### `MenuPilot\Admin\Ajax_Handler`
**Purpose:** Handle AJAX requests  
**Responsibilities:**
- Settings export
- Non-critical operations

### REST API Classes

#### `MenuPilot\REST\Rest_Controller`
**Purpose:** REST API endpoints  
**Responsibilities:**
- Register routes
- Handle export requests
- Handle import requests
- Authentication/authorization

**Endpoints:**
- `POST /menupilot/v1/menus/export`
- `POST /menupilot/v1/menus/import`
- `GET /menupilot/v1/menus/mapping-options`

---

## Data Flow

### Export Flow

```
User clicks "Export Menu"
    ↓
JavaScript: POST to /menupilot/v1/menus/export
    ↓
REST Controller: validate request
    ↓
Menu_Exporter::export($menu_id)
    ↓
Get menu object & items
    ↓
Build export data structure
    ↓
Apply filters (menupilot_export_menu_item, etc.)
    ↓
Return JSON response
    ↓
JavaScript: Download file
```

### Import Flow

```
User uploads JSON file
    ↓
JavaScript: Read file (FileReader API)
    ↓
Fetch mapping options (GET /menupilot/v1/menus/mapping-options)
    ↓
Generate preview HTML client-side
    ↓
Show preview modal
    ↓
User adjusts mappings & removes items
    ↓
JavaScript: POST to /menupilot/v1/menus/import
    ↓
REST Controller: validate request
    ↓
Menu_Importer::import($data, $name, $location)
    ↓
Apply filters (menupilot_pre_import, etc.)
    ↓
Create menu
    ↓
Import items recursively
    ↓
Apply filters (menupilot_after_import_item, etc.)
    ↓
Assign to location
    ↓
Return success response
    ↓
JavaScript: Show success message
```

---

## API Endpoints

### Export Menu

**Endpoint:** `POST /wp-json/menupilot/v1/menus/export`

**Request:**
```json
{
  "menu_id": 2
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "schema_version": "1.0",
    "plugin": {...},
    "export_context": {...},
    "menu": {...},
    "extensions": {}
  }
}
```

### Import Menu

**Endpoint:** `POST /wp-json/menupilot/v1/menus/import`

**Request:**
```json
{
  "menu_name": "Imported Menu",
  "menu_data": {...},
  "location": "primary"
}
```

**Response:**
```json
{
  "success": true,
  "menu_id": 5,
  "message": "Menu imported successfully!",
  "edit_url": "/wp-admin/nav-menus.php?action=edit&menu=5"
}
```

### Mapping Options

**Endpoint:** `GET /wp-json/menupilot/v1/menus/mapping-options`

**Response:**
```json
{
  "success": true,
  "options": {
    "posts": [...],
    "pages": [...],
    "taxonomies": [...]
  }
}
```

---

## Database Schema

### No Custom Tables

MenuPilot does **not** create custom database tables. It uses:

- **WordPress Core Tables:**
  - `wp_terms` - Menu terms
  - `wp_term_taxonomy` - Menu taxonomy
  - `wp_posts` - Menu items (post_type = 'nav_menu_item')
  - `wp_postmeta` - Menu item metadata
  - `wp_options` - Plugin settings (optional)

### Settings Storage

If settings are used, they're stored in `wp_options`:

```php
// Example option names
menupilot_export_settings
menupilot_import_settings
menupilot_version
```

### Transient Usage

Temporary data uses WordPress transients:

```php
// Example transient names
_transient_menupilot_export_*
_transient_menupilot_import_*
```

---

## Security

### Authentication

- **REST API:** Requires logged-in user
- **Admin Pages:** Requires `manage_options` capability
- **Nonces:** All forms and AJAX requests

### Authorization

```php
// REST API permission check
public function check_admin_permissions() {
    if (!current_user_can('manage_options')) {
        return new WP_Error('rest_forbidden', ...);
    }
    return true;
}
```

### Input Sanitization

- All user input sanitized
- JSON data validated
- File uploads checked
- SQL queries use prepared statements

### Output Escaping

- All output escaped
- HTML sanitized
- URLs validated
- JSON properly encoded

### REST API Security

```php
// Block unauthenticated access
add_filter('rest_authentication_errors', function($result, $server, $request) {
    $route = $request->get_route();
    if (strpos($route, '/menupilot/v1') === 0) {
        if (!is_user_logged_in()) {
            return new WP_Error('rest_unauthorized', ...);
        }
    }
    return $result;
}, 10, 3);
```

---

## Performance

### Optimization Strategies

1. **Client-Side Preview**
   - Preview generated in browser
   - No server round-trip
   - Faster user experience

2. **Efficient Queries**
   - Uses WordPress core functions
   - No unnecessary queries
   - Cached where possible

3. **Lazy Loading**
   - Mapping options fetched on demand
   - Assets loaded only on plugin pages
   - No global script loading

4. **Minimal Database**
   - No custom tables
   - Uses WordPress APIs
   - Efficient data structures

### Performance Benchmarks

**Export:**
- Small menu (10 items): < 100ms
- Medium menu (50 items): < 300ms
- Large menu (200 items): < 1s

**Import:**
- Small menu (10 items): < 500ms
- Medium menu (50 items): < 2s
- Large menu (200 items): < 5s

**Preview Generation:**
- Client-side: < 50ms
- No server impact

---

## Code Quality

### Standards

- **WordPress Coding Standards** - PHPCS validated
- **PSR-4 Autoloading** - Namespace structure
- **Type Declarations** - PHP 7.4+ types
- **Strict Typing** - `declare(strict_types=1)`
- **Documentation** - PHPDoc comments

### Testing

- **Manual Testing** - All features tested
- **Browser Testing** - Chrome, Firefox, Safari
- **WordPress Versions** - 5.8, 6.0, 6.1+
- **PHP Versions** - 7.4, 8.0, 8.1+

### Dependencies

- **WordPress Core** - No external PHP dependencies
- **jQuery** - WordPress bundled version
- **SCSS** - Compiled to CSS (build tool)

---

## Extensibility Points

### Hooks

- **15 WordPress hooks** throughout codebase
- **Filter system** for data modification
- **Action system** for side effects
- **Column Manager** for UI extension

### JSON Schema

- **Extensions field** for Pro data
- **Versioned schema** (1.0)
- **Backward compatible** structure

### JavaScript

- **Localized data** via `wp_localize_script`
- **Column data** exposed to JS
- **Event system** for extensions

---

## Future Architecture Considerations

### Planned Improvements

1. **Caching Layer**
   - Cache mapping options
   - Cache menu structures
   - Transient-based caching

2. **Background Processing**
   - Async imports for large menus
   - Queue system for bulk operations

3. **API Versioning**
   - Versioned endpoints
   - Deprecation handling
   - Migration paths

4. **Multisite Support**
   - Network-level settings
   - Site-specific overrides
   - Cross-site imports

---

**Next:** [Changelog](./07-changelog.md)

