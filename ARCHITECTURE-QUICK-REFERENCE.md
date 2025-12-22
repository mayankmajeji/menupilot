# MenuPilot Architecture - Quick Reference

## 🏗️ Core Classes

```
MenuPilot\Init              → includes/class-init.php
MenuPilot\Loader            → includes/class-loader.php
MenuPilot\Settings          → includes/class-settings.php
```

## 📁 Directory Map

```
/workspace/
├── menupilot.php                  # Bootstrap
├── includes/                      # PHP Classes
│   ├── class-init.php            # Main controller
│   ├── class-loader.php          # PSR-4 autoloader
│   ├── class-settings.php        # Settings API
│   └── functions-common.php      # Utilities
├── assets/
│   ├── css/                      # SCSS → CSS
│   └── js/                       # JavaScript
├── tests/                        # Codeception tests
└── i18n/languages/               # Translations
```

## 🔄 Initialization Flow

```
1. WordPress loads plugin
   ↓
2. menupilot.php
   - Define constants
   - Load autoloader
   ↓
3. Hook: plugins_loaded
   - menupilot_init()
   - Load text domain
   ↓
4. Init::get_instance()->init()
   - Load dependencies
   - Initialize Settings
   - Register hooks
   ↓
5. Ready for use
```

## 🎣 Custom Hooks

### Actions
```php
do_action('menupilot_init', $instance)
do_action('menupilot_activate')
do_action('menupilot_deactivate')
do_action('menupilot_uninstall')
do_action('menupilot_init_integrations')
```

### Filters
```php
apply_filters('menupilot_load_assets', true)
apply_filters('menupilot_client_ip', $ip)
apply_filters('menupilot_settings_fields', [])
```

## ⚙️ Constants

```php
MENUPILOT_VERSION          // '1.0.0'
MENUPILOT_PLUGIN_DIR       // '/path/to/plugin/'
MENUPILOT_PLUGIN_URL       // 'https://site.com/wp-content/plugins/menupilot/'
MENUPILOT_PLUGIN_BASENAME  // 'menupilot/menupilot.php'
MENUPILOT_INIT_DONE        // true (after init)
```

## 📝 Settings API

### Get/Set Options
```php
$settings = new \MenuPilot\Settings();
$value = $settings->get_option('key', 'default');
$settings->update_option('key', 'value');
```

### Add Custom Field
```php
add_filter('menupilot_settings_fields', function($fields) {
    $fields[] = [
        'field_id' => 'my_option',
        'type' => 'text',
        'label' => __('My Option', 'menupilot'),
        'tab' => 'general',
        'section' => 'default',
        'priority' => 10,
    ];
    return $fields;
});
```

### Field Types
- text
- textarea
- checkbox
- select
- multiselect
- number
- email
- url

## 🎨 Asset Loading

### Admin Assets
```php
// Only on plugin pages with screen IDs:
// - settings_page_menupilot
// - toplevel_page_menupilot
// - menupilot_page_menupilot-settings

wp_enqueue_style('menupilot-admin', .../assets/css/admin.css)
wp_enqueue_script('menupilot-admin', .../assets/js/admin.js)
```

### Frontend Assets
```php
// Conditional loading via filter
if (apply_filters('menupilot_load_assets', true)) {
    wp_enqueue_style('menupilot-frontend', .../assets/css/main.css)
    wp_enqueue_script('menupilot-frontend', .../assets/js/main.js)
}
```

### Localized Data
```javascript
// JavaScript object: menupilot
menupilot.ajaxurl  // admin-ajax.php URL
menupilot.nonce    // Security nonce
```

## 🔧 Build Commands

### Install
```bash
composer install        # PHP dependencies
npm install            # Node dependencies
```

### Build
```bash
npm run gulp:styles    # Compile SCSS
npm run gulp:watch     # Watch for changes
```

### Code Quality
```bash
composer lint          # PHP linting
composer format        # Auto-fix PHP
npm run lint:js        # JavaScript linting
npm run lint:css       # CSS/SCSS linting
npm run lint:all       # All linters
```

### Testing
```bash
composer test          # All tests
npm test              # Run tests
codecept run unit     # Unit tests only
codecept run integration  # Integration tests only
```

## 🔐 Security Features

| Feature | Implementation |
|---------|---------------|
| Direct access | `if (!defined('WPINC')) die;` |
| Capabilities | `current_user_can('manage_options')` |
| Sanitization | Type-based auto-sanitization |
| Escaping | `esc_html()`, `esc_url()`, etc. |
| Nonces | AJAX requests |
| Type safety | `declare(strict_types=1)` |

## 📦 Autoloader Logic

```php
// Class name transformation:
MenuPilot\MyClass
  ↓
includes/class-myclass.php

// With subdirectories:
MenuPilot\Integrations\Forms\ContactForm
  ↓
includes/integrations/forms/class-contactform.php
```

## 🌍 Internationalization

```php
// Text domain: 'menupilot'
// Domain path: '/i18n/languages'

__('String', 'menupilot')              // Translate
esc_html__('String', 'menupilot')      // Translate + escape
_e('String', 'menupilot')              // Translate + echo
```

## 🎯 Plugin Screens

### Admin Menu
- **Parent:** Top-level menu
- **Slug:** `menupilot-settings`
- **Capability:** `manage_options`
- **Icon:** `dashicons-admin-generic`
- **Position:** 65

### Screen IDs
```php
get_current_screen()->id === 'settings_page_menupilot'
get_current_screen()->id === 'toplevel_page_menupilot'
get_current_screen()->id === 'menupilot_page_menupilot-settings'
```

## 📊 Database

### Options
```php
'menupilot_settings' => [
    // Array of plugin settings
]
```

### Transients
```php
'menupilot_cache'  // Cached data
```

## 🧩 Integrations

### Structure
```
includes/integrations/
├── core/          # WordPress core
├── forms/         # Form plugins
├── ecommerce/     # E-commerce plugins
├── community/     # Community plugins
└── membership/    # Membership plugins
```

### Register Integration
```php
add_action('menupilot_init_integrations', function() {
    if (class_exists('TargetPlugin')) {
        new \MenuPilot\Integrations\MyIntegration();
    }
});
```

## 🚀 Deployment Checklist

- [ ] Update version in `menupilot.php`
- [ ] Update version in `package.json`
- [ ] Update version in `composer.json`
- [ ] Update `CHANGELOG.md`
- [ ] Update `readme.txt`
- [ ] Run `npm run gulp:styles`
- [ ] Run `composer test`
- [ ] Run `npm run lint:all`
- [ ] Test in clean WordPress install
- [ ] Create distribution ZIP

## 📚 Key Files

| File | Purpose |
|------|---------|
| `menupilot.php` | Bootstrap |
| `includes/class-init.php` | Main controller |
| `includes/class-settings.php` | Settings framework |
| `includes/class-loader.php` | Autoloader |
| `uninstall.php` | Cleanup handler |
| `gulpfile.js` | Build config |
| `composer.json` | PHP dependencies |
| `package.json` | Node dependencies |

## 💡 Common Patterns

### Singleton Access
```php
$plugin = \MenuPilot\Init::get_instance();
```

### Add Action Hook
```php
add_action('menupilot_init', function($instance) {
    // Your code
});
```

### Add Filter Hook
```php
add_filter('menupilot_load_assets', function($load) {
    return is_singular('post') ? true : false;
});
```

### Utility Functions
```php
\MenuPilot\get_client_ip()
\MenuPilot\log_debug('Message', 'error')
\MenuPilot\is_debug_mode()
```

## 🎨 SCSS Variables

```scss
// From assets/css/modules/_variables.scss
$primary-color: #0073aa;
$secondary-color: #23282d;
$spacing-md: 20px;
$breakpoint-md: 768px;
```

## ⚡ Performance Tips

1. **Assets load conditionally** - Only where needed
2. **Single option** - All settings in one DB row
3. **Transients** - Use for caching
4. **Double-init protection** - Via constant check
5. **Lazy loading** - Integrations load on demand

## 🐛 Debug Mode

```php
// Enable in wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Then use:
\MenuPilot\log_debug('Debug message', 'info');
```

## 📋 Admin Body Classes

```css
.menupilot-admin { }
.menupilot-screen-settings_page_menupilot { }
```

## 🔄 Lifecycle Hooks

```
Activation:
  register_activation_hook()
    → Init::activate()
      → Settings::add_default_options()
      → do_action('menupilot_activate')

Deactivation:
  register_deactivation_hook()
    → Init::deactivate()
      → do_action('menupilot_deactivate')

Uninstall:
  uninstall.php
    → menupilot_uninstall()
      → delete_option()
      → do_action('menupilot_uninstall')
```

---

**Quick Reference v1.0**  
Last Updated: December 22, 2025
