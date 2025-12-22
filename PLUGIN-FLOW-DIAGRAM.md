# MenuPilot Plugin Flow Diagrams

## 1. Plugin Initialization Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    WordPress Loads Plugin                        │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                        menupilot.php                             │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. Security Check: if (!defined('WPINC')) die;           │  │
│  │ 2. Define Constants:                                      │  │
│  │    - MENUPILOT_VERSION                                    │  │
│  │    - MENUPILOT_PLUGIN_DIR                                 │  │
│  │    - MENUPILOT_PLUGIN_URL                                 │  │
│  │    - MENUPILOT_PLUGIN_BASENAME                            │  │
│  │ 3. Load Autoloader: includes/class-loader.php             │  │
│  │ 4. Register Hooks:                                        │  │
│  │    - add_action('plugins_loaded', 'menupilot_init')       │  │
│  │    - register_activation_hook()                           │  │
│  │    - register_deactivation_hook()                         │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Autoloader Registers                          │
│                  (class-loader.php)                              │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ spl_autoload_register()                                   │  │
│  │   - Maps: MenuPilot\ClassName                             │  │
│  │   - To: includes/class-classname.php                      │  │
│  │ load_integrations()                                       │  │
│  │   - Loads all integration files from subdirectories       │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                    WordPress Hook: plugins_loaded
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                     menupilot_init()                             │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. load_plugin_textdomain('menupilot')                    │  │
│  │ 2. Init::get_instance()->init()                           │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│               Init::get_instance()->init()                       │
│                  (class-init.php)                                │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. Check: if MENUPILOT_INIT_DONE → return                │  │
│  │ 2. define('MENUPILOT_INIT_DONE', true)                    │  │
│  │ 3. load_dependencies()                                    │  │
│  │    └─ require functions-common.php                        │  │
│  │ 4. Initialize Settings:                                   │  │
│  │    └─ $this->settings = new Settings()                    │  │
│  │ 5. init_integrations()                                    │  │
│  │    └─ do_action('menupilot_init_integrations')            │  │
│  │ 6. init_hooks()                                           │  │
│  │    ├─ Admin hooks (if is_admin())                         │  │
│  │    │  ├─ admin_menu                                       │  │
│  │    │  ├─ admin_init                                       │  │
│  │    │  ├─ admin_enqueue_scripts                            │  │
│  │    │  └─ admin_body_class                                 │  │
│  │    └─ Frontend hooks                                      │  │
│  │       └─ wp_enqueue_scripts                               │  │
│  │ 7. do_action('menupilot_init', $this)                     │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Settings Initialize                          │
│                    (class-settings.php)                          │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ __construct()                                             │  │
│  │   ├─ add_default_options()                                │  │
│  │   └─ register_centralized_fields()                        │  │
│  │      └─ apply_filters('menupilot_settings_fields', [])    │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                      ✓ Plugin Initialized
```

## 2. Admin Page Load Flow

```
                    User Accesses Admin Page
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                       WordPress Admin                            │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                     Hook: admin_enqueue_scripts
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│              Init::enqueue_admin_assets()                        │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. Get current screen                                     │  │
│  │ 2. Check if screen ID in PLUGIN_SCREEN_IDS               │  │
│  │    ├─ settings_page_menupilot                             │  │
│  │    ├─ toplevel_page_menupilot                             │  │
│  │    └─ menupilot_page_menupilot-settings                   │  │
│  │ 3. If match:                                              │  │
│  │    ├─ wp_enqueue_style('menupilot-admin')                 │  │
│  │    ├─ wp_enqueue_script('menupilot-admin')                │  │
│  │    └─ wp_localize_script() with ajaxurl & nonce           │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                        Hook: admin_body_class
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│             Init::add_admin_body_class()                         │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Adds classes:                                             │  │
│  │  - menupilot-admin                                        │  │
│  │  - menupilot-screen-{screen_id}                           │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                          Hook: admin_menu
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│            Settings::add_admin_menu()                            │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ add_menu_page(                                            │  │
│  │   'MenuPilot',                                            │  │
│  │   'MenuPilot',                                            │  │
│  │   'manage_options',                                       │  │
│  │   'menupilot-settings',                                   │  │
│  │   [Settings, 'render_settings_page'],                     │  │
│  │   'dashicons-admin-generic',                              │  │
│  │   65                                                      │  │
│  │ )                                                         │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                          Hook: admin_init
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│            Settings::register_settings()                         │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ register_setting(                                         │  │
│  │   'menupilot_settings',                                   │  │
│  │   'menupilot_settings',                                   │  │
│  │   sanitize_callback: [Settings, 'sanitize_settings']      │  │
│  │ )                                                         │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│           Settings::render_settings_page()                       │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. Check: current_user_can('manage_options')             │  │
│  │ 2. Display <div class="wrap">                            │  │
│  │ 3. Show settings_errors()                                │  │
│  │ 4. Render <form action="options.php">                    │  │
│  │    ├─ settings_fields('menupilot_settings')              │  │
│  │    ├─ do_settings_sections('menupilot_settings')         │  │
│  │    └─ submit_button()                                    │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                        ✓ Admin Page Rendered
```

## 3. Settings Save Flow

```
                    User Clicks "Save Settings"
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│               Form Submit → options.php                          │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                  WordPress Settings API Processes
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│            Settings::sanitize_settings($input)                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. Ensure $input is array                                │  │
│  │ 2. Get fields structure via get_fields_structure()       │  │
│  │ 3. Loop through each field:                              │  │
│  │    ├─ Check if custom sanitize callback exists           │  │
│  │    │  └─ If yes: call_user_func(callback, value)         │  │
│  │    └─ If no: sanitize based on field type:               │  │
│  │       ├─ text/select → sanitize_text_field()             │  │
│  │       ├─ textarea → sanitize_textarea_field()            │  │
│  │       ├─ checkbox → convert to 0/1                       │  │
│  │       ├─ number → intval()                               │  │
│  │       ├─ email → sanitize_email()                        │  │
│  │       ├─ url → esc_url_raw()                             │  │
│  │       └─ multiselect → array_map(sanitize_text_field)    │  │
│  │ 4. add_settings_error('settings_updated')                │  │
│  │ 5. Return sanitized array                                │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│             WordPress saves to database                          │
│             update_option('menupilot_settings', $sanitized)      │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                         Redirect to settings page
                                │
                                ▼
                    ✓ Settings Saved Successfully
```

## 4. Frontend Asset Loading Flow

```
                        Frontend Page Load
                                │
                                ▼
                    Hook: wp_enqueue_scripts
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│           Init::enqueue_frontend_assets()                        │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. Check: should_load_assets()                            │  │
│  │    └─ apply_filters('menupilot_load_assets', true)        │  │
│  │ 2. If true:                                               │  │
│  │    ├─ wp_enqueue_style('menupilot-frontend')              │  │
│  │    │  └─ assets/css/main.css                              │  │
│  │    ├─ wp_enqueue_script('menupilot-frontend')             │  │
│  │    │  └─ assets/js/main.js                                │  │
│  │    └─ wp_localize_script() with ajaxurl & nonce           │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                        Assets Loaded in Browser
```

## 5. Plugin Activation Flow

```
                    User Clicks "Activate"
                                │
                                ▼
                register_activation_hook() triggered
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                   Init::activate()                               │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. Load class-init.php                                    │  │
│  │ 2. Initialize Settings:                                   │  │
│  │    └─ $this->settings = new Settings()                    │  │
│  │ 3. Call Settings::add_default_options()                   │  │
│  │    └─ add_option('menupilot_settings', defaults)          │  │
│  │ 4. do_action('menupilot_activate')                        │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                    ✓ Plugin Activated Successfully
```

## 6. Plugin Deactivation Flow

```
                   User Clicks "Deactivate"
                                │
                                ▼
              register_deactivation_hook() triggered
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                  Init::deactivate()                              │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ do_action('menupilot_deactivate')                         │  │
│  │   (Allows other code to run cleanup)                      │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                   ✓ Plugin Deactivated Successfully
                   (Options remain in database)
```

## 7. Plugin Uninstall Flow

```
                     User Clicks "Delete"
                                │
                                ▼
               WordPress looks for uninstall.php
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                     uninstall.php                                │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. Check: if (!defined('WP_UNINSTALL_PLUGIN')) exit      │  │
│  │ 2. menupilot_uninstall()                                  │  │
│  │    ├─ delete_option('menupilot_settings')                 │  │
│  │    ├─ delete_transient('menupilot_cache')                 │  │
│  │    └─ do_action('menupilot_uninstall')                    │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                    ✓ Plugin Completely Removed
                    (All data deleted from database)
```

## 8. Class Autoloading Flow

```
                  Code References: new \MenuPilot\MyClass
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│              spl_autoload_register() triggers                    │
│                 Loader::autoload($class)                         │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. Check namespace: starts with 'MenuPilot\'?             │  │
│  │    └─ If no: return (not our class)                       │  │
│  │ 2. Remove namespace: 'MyClass'                            │  │
│  │ 3. Convert to filename:                                   │  │
│  │    MyClass → class-myclass.php                            │  │
│  │ 4. Check locations in order:                              │  │
│  │    ├─ includes/class-myclass.php                          │  │
│  │    ├─ includes/integrations/class-myclass.php             │  │
│  │    └─ includes/integrations/{category}/class-myclass.php  │  │
│  │       Categories: core, forms, ecommerce, etc.            │  │
│  │ 5. If found: require_once $file                           │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                         Class Loaded ✓
```

## 9. Settings Field Registration Flow

```
               Developer adds filter hook in their code
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│  add_filter('menupilot_settings_fields', function($fields) {    │
│    $fields[] = [                                                │
│      'field_id' => 'my_option',                                 │
│      'type' => 'text',                                          │
│      'label' => 'My Option',                                    │
│      'tab' => 'general',                                        │
│      'section' => 'default',                                    │
│      'priority' => 10                                           │
│    ];                                                           │
│    return $fields;                                              │
│  });                                                            │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│        Settings::register_centralized_fields()                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. $fields = apply_filters('menupilot_settings_fields', [])│  │
│  │ 2. organize_fields($fields)                               │  │
│  │    ├─ Group by tab                                        │  │
│  │    ├─ Group by section                                    │  │
│  │    ├─ Sort by priority                                    │  │
│  │    └─ Store in $this->fields                              │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                Fields available via get_fields_structure()
                                │
                                ▼
              Used for rendering & sanitization ✓
```

## 10. Integration Registration Flow

```
               Developer adds action hook in their code
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│  add_action('menupilot_init_integrations', function() {         │
│    if (class_exists('WooCommerce')) {                           │
│      new \MenuPilot\Integrations\WooCommerce();                 │
│    }                                                            │
│  });                                                            │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                  During Init::init_integrations()
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│            Init::init_integrations()                             │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ do_action('menupilot_init_integrations')                  │  │
│  │   └─ Triggers all registered callbacks                    │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│          Integration Class Constructor                           │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. Check if target plugin exists                         │  │
│  │ 2. If yes: register hooks                                │  │
│  │ 3. Add filters/actions as needed                         │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                      Integration Active ✓
```

## 11. SCSS Build Flow

```
              Developer runs: npm run gulp:styles
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Gulp Task: styles                            │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. Read: assets/css/**/*.scss                            │  │
│  │    ├─ main.scss                                          │  │
│  │    ├─ admin.scss                                         │  │
│  │    └─ modules/*.scss                                     │  │
│  │ 2. Compile SCSS → CSS                                    │  │
│  │ 3. Save: assets/css/main.css                             │  │
│  │ 4. Minify with cleanCSS                                  │  │
│  │ 5. Save: assets/css/main.min.css                         │  │
│  │ 6. Repeat for admin.scss → admin.css                     │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                   CSS files ready for enqueuing ✓
```

## 12. Debug Logging Flow

```
                Code calls: \MenuPilot\log_debug()
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│            \MenuPilot\log_debug($message, $level)                │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. Check: WP_DEBUG && WP_DEBUG_LOG enabled?              │  │
│  │    └─ If no: return early                                │  │
│  │ 2. Format: "[MenuPilot] [$level] $message"               │  │
│  │ 3. error_log($formatted_message)                         │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
              Writes to: wp-content/debug.log ✓
```

---

## Key Takeaways

### Initialization Order
1. Constants defined
2. Autoloader registered
3. WordPress `plugins_loaded` hook
4. Text domain loaded
5. Settings initialized
6. Integrations loaded
7. Hooks registered

### Security Checkpoints
- ✓ WPINC check (direct access)
- ✓ WP_UNINSTALL_PLUGIN check (uninstall)
- ✓ Capability checks (manage_options)
- ✓ Input sanitization (type-based)
- ✓ Output escaping (context-aware)
- ✓ Nonce verification (AJAX)

### Extension Points
- ✓ Settings fields via filter
- ✓ Integrations via action
- ✓ Asset loading via filter
- ✓ Lifecycle hooks (activate, deactivate, init)
- ✓ Custom hooks throughout

### Optimization Strategies
- ✓ Conditional asset loading
- ✓ Single option for all settings
- ✓ Double-init protection
- ✓ Screen-specific admin assets
- ✓ Lazy integration loading

---

**Flow Diagrams v1.0**  
Last Updated: December 22, 2025
