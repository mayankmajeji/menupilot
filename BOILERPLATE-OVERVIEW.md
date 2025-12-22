# Maverick Plugin Boilerplate - Complete Overview

## 📦 What's Included

### Core Structure

```
maverick-plugin-boilerplate/
├── plugin-name.php              # Main plugin file (rename this)
├── uninstall.php                # Cleanup on uninstall
├── setup.sh                     # Automated setup script ⭐
├── README.md                    # Main documentation
├── QUICKSTART.md                # Quick start guide
├── CHANGELOG.md                 # Version history
├── CONTRIBUTING.md              # Contribution guidelines
├── LICENSE                      # MIT License
└── readme.txt                   # WordPress.org readme
```

### Includes (Core PHP)

```
includes/
├── class-init.php               # Main initialization class
├── class-loader.php             # PSR-4 autoloader
├── class-settings.php           # Settings framework
├── functions-common.php         # Utility functions
├── admin/
│   ├── templates/               # Admin UI templates
│   └── views/                   # Admin view files
├── settings/                    # Settings framework classes
│   ├── class-settings-page.php
│   ├── class-tab.php
│   └── field-renderer.php
└── integrations/                # Plugin integrations
    ├── core/                    # WordPress core
    ├── forms/                   # Form plugins
    ├── ecommerce/               # E-commerce plugins
    ├── community/               # Community plugins
    └── membership/              # Membership plugins
```

### Assets

```
assets/
├── css/
│   ├── main.scss                # Frontend styles
│   ├── admin.scss               # Admin styles
│   └── modules/
│       ├── _variables.scss      # SCSS variables
│       ├── _mixins.scss         # SCSS mixins
│       └── _base.scss           # Base styles
├── js/
│   ├── main.js                  # Frontend JavaScript
│   └── admin.js                 # Admin JavaScript
└── images/                      # Images and icons
```

### Testing

```
tests/
├── unit/                        # Unit tests
│   └── SettingsTest.php
├── integration/                 # Integration tests
│   └── PluginActivationTest.php
├── acceptance/                  # Acceptance tests
│   └── AdminSettingsPageCest.php
├── _support/                    # Test helpers
│   └── Helper/
├── _envs/                       # Environment configs
└── _output/                     # Test outputs
```

### Configuration Files

```
Configuration Files:
├── composer.json                # PHP dependencies
├── package.json                 # Node.js dependencies
├── gulpfile.js                  # Gulp tasks
├── codeception.yml              # Testing config
├── phpcs.xml.dist               # PHP CodeSniffer
├── phpstan.neon                 # PHPStan config
├── .eslintrc.js                 # ESLint config
├── .stylelintrc.json            # Stylelint config
├── .editorconfig                # Editor config
├── .nvmrc                       # Node version
├── .gitignore                   # Git ignore rules
├── .gitattributes               # Git attributes
└── .distignore                  # Distribution ignore
```

### Documentation

```
docs/
├── API-REFERENCE.md             # Complete API docs
├── TESTING.md                   # Testing guide
└── WORDPRESS-ORG.md             # WordPress.org checklist
```

## 🎯 Key Features

### 1. **Automated Setup Script**

The `setup.sh` script automates the entire setup process:
- Collects plugin information
- Generates proper naming conventions
- Performs find-and-replace across all files
- Installs dependencies
- Builds assets
- Removes itself after setup

### 2. **Modern PHP Architecture**

- **PSR-4 Autoloading** - Automatic class loading
- **Namespaces** - Proper code organization
- **Type Declarations** - PHP 7.4+ strict types
- **Singleton Pattern** - For main plugin class
- **Hooks System** - Clean action/filter registration

### 3. **Flexible Settings Framework**

- Filter-based field registration
- Tab and section organization
- Field types: text, textarea, checkbox, select, multiselect, number, email, url
- Custom sanitization callbacks
- Organized by priority

### 4. **Complete Testing Suite**

- **Unit Tests** - Test individual classes
- **Integration Tests** - Test WordPress integration
- **Acceptance Tests** - Test user workflows
- **Codeception** - Modern testing framework
- Pre-configured test structure

### 5. **Build Tooling**

- **Gulp** - Asset compilation
- **SCSS** - Modern CSS with modules
- **ESLint** - JavaScript linting
- **Stylelint** - CSS/SCSS linting
- **PHPCS** - PHP code standards
- **PHPStan** - Static analysis

### 6. **WordPress.org Ready**

- Proper readme.txt format
- .distignore for clean distribution
- License files
- Blueprint support
- Icon and banner placeholders

## 🔧 Template Variables

All these placeholders are automatically replaced by `setup.sh`:

| Variable | Description | Example |
|----------|-------------|---------|
| `MenuPilot` | Full plugin name | `My Awesome Plugin` |
| `Easily import, export, duplicate, backup, and restore WordPress navigation menus. MenuPilot helps you move menus between sites safely with clean imports and reliable structure handling.` | Short description | `A powerful WordPress plugin` |
| `https://mayankmajeji.com/menupilot` | Plugin homepage URL | `https://example.com/my-plugin` |
| `1.0.0` | Version number | `1.0.0` |
| `Mayank Majeji` | Author name | `John Doe` |
| `` | Author website | `https://example.com` |
| `menupilot` | WordPress text domain | `my-awesome-plugin` |
| `MenuPilot` | PHP namespace (PascalCase) | `MyAwesomePlugin` |
| `MenuPilot` | Composer package name | `MyAwesomePlugin` |
| `MENUPILOT` | Constants prefix | `MY_AWESOME_PLUGIN` |
| `menupilot` | Functions prefix | `my_awesome_plugin` |
| `MenuPilot` | Class prefix | `My_Awesome_Plugin` |

## 🚀 Getting Started

### Option 1: Automated (Recommended)

```bash
bash setup.sh
```

### Option 2: Manual

1. Copy files to your plugin directory
2. Find and replace all template variables
3. Rename `plugin-name.php` to `your-plugin-slug.php`
4. Run `composer install`
5. Run `npm install`
6. Run `npm run gulp:styles`

## 📝 Common Tasks

### Add a Custom Setting

```php
add_filter('your_plugin_settings_fields', function($fields) {
    $fields[] = array(
        'field_id' => 'my_setting',
        'type' => 'text',
        'label' => __('My Setting', 'your-plugin'),
        'description' => __('Description here', 'your-plugin'),
        'tab' => 'general',
        'section' => 'default',
        'priority' => 10,
        'default' => '',
    );
    return $fields;
});
```

### Create an Integration

```php
// includes/integrations/forms/class-my-form.php
namespace YourPlugin\Integrations;

class My_Form {
    public function __construct() {
        if (!class_exists('MyFormPlugin')) {
            return;
        }
        $this->init();
    }
    
    private function init() {
        add_action('my_form_hook', array($this, 'handle_form'));
    }
    
    public function handle_form($form_data) {
        // Your logic here
    }
}

// Register it
add_action('your_plugin_init_integrations', function() {
    new \YourPlugin\Integrations\My_Form();
});
```

### Add Custom Admin Page

```php
// In class-settings.php add_admin_menu()
add_submenu_page(
    'your-plugin-settings',
    __('Custom Page', 'your-plugin'),
    __('Custom Page', 'your-plugin'),
    'manage_options',
    'your-plugin-custom',
    array($this, 'render_custom_page')
);
```

### Enqueue Custom Script

```php
// In class-init.php
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'your-plugin-custom',
        YOUR_PLUGIN_PLUGIN_URL . 'assets/js/custom.js',
        array('jquery'),
        YOUR_PLUGIN_VERSION,
        true
    );
});
```

## 🧪 Development Workflow

### 1. Development

```bash
# Watch for SCSS changes
npm run gulp:watch

# Run in separate terminal for live reload
```

### 2. Testing

```bash
# Lint PHP
composer lint

# Fix PHP issues
composer format

# Run PHPStan
composer run test:phpstan

# Lint JavaScript
npm run lint:js

# Lint CSS
npm run lint:css

# All linters
npm run lint:all
```

### 3. Running Tests

```bash
# Unit tests
vendor/bin/codecept run unit

# Integration tests
vendor/bin/codecept run integration

# Acceptance tests
vendor/bin/codecept run acceptance

# All tests
composer test
```

### 4. Building for Release

```bash
# 1. Update version numbers
# 2. Update CHANGELOG.md
# 3. Build assets
npm run gulp:styles

# 4. Test everything
composer test
npm run lint:all

# 5. Create distribution ZIP
zip -r your-plugin.zip . -x@.distignore
```

## 🎨 Customization Points

### Change Menu Icon

Edit `includes/class-settings.php` in `add_admin_menu()`:
```php
'dashicons-admin-generic',  // Change this
```

### Modify Admin Styles

Edit `assets/css/admin.scss` and run:
```bash
npm run gulp:styles
```

### Add More Field Types

Extend the sanitization in `includes/class-settings.php`:
```php
case 'custom_type':
    $sanitized[$id] = your_custom_sanitization($value);
    break;
```

### Hook Into Plugin Lifecycle

```php
// On activation
add_action('your_plugin_activate', function() {
    // Setup code
});

// On deactivation
add_action('your_plugin_deactivate', function() {
    // Cleanup code
});

// After init
add_action('your_plugin_init', function($instance) {
    // Extend functionality
});
```

## 📚 Best Practices

1. **Follow WordPress Coding Standards** - Run `composer lint` regularly
2. **Write Tests** - Aim for good coverage
3. **Document Your Code** - Use PHPDoc blocks
4. **Use Hooks** - Make your plugin extensible
5. **Internationalize** - Wrap all strings in translation functions
6. **Sanitize Input** - Always sanitize user input
7. **Escape Output** - Always escape output
8. **Check Capabilities** - Verify user permissions
9. **Use Nonces** - Protect forms and AJAX calls
10. **Version Control** - Commit regularly with clear messages

## 🔐 Security Checklist

- [ ] All inputs sanitized
- [ ] All outputs escaped
- [ ] Nonces on forms
- [ ] Capability checks
- [ ] ABSPATH checks
- [ ] No SQL injection risks
- [ ] No XSS vulnerabilities
- [ ] No CSRF vulnerabilities
- [ ] Secure file operations
- [ ] Validate file uploads

## 🐛 Troubleshooting

### Common Issues

**"Fatal error: Cannot declare class..."**
- Check for naming conflicts
- Ensure all placeholders were replaced

**"Unexpected token" in JavaScript**
- Check JavaScript syntax
- Ensure jQuery is loaded

**"Undefined function" in PHP**
- Check if required plugin is active
- Verify autoloader is working

**SCSS won't compile**
- Check Node.js version (need 18+)
- Reinstall node_modules

**Tests fail**
- Check test environment setup
- Verify database credentials
- Ensure WordPress test install exists

## 📊 File Overview

### Must Edit
- `plugin-name.php` - Rename and configure
- All files with `{{TEMPLATE_VARIABLES}}`

### Should Customize
- `assets/css/*.scss` - Your styles
- `assets/js/*.js` - Your JavaScript
- `includes/class-init.php` - Your plugin logic
- `readme.txt` - WordPress.org description
- `README.md` - GitHub/project description

### Can Delete (If Not Needed)
- `tests/` - If not writing tests
- `docs/` - If not documenting
- `.wordpress-org/` - If not publishing to WordPress.org

## 🎓 Learning Resources

### WordPress Development
- [Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Common APIs](https://codex.wordpress.org/WordPress_APIs)

### Testing
- [Codeception Docs](https://codeception.com/docs)
- [WordPress Testing](https://make.wordpress.org/core/handbook/testing/)
- [PHPUnit](https://phpunit.de/)

### Tools
- [WP-CLI](https://wp-cli.org/)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/)
- [Gulp](https://gulpjs.com/)

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## 📄 License

This boilerplate is released under the MIT License. See [LICENSE](LICENSE) for details.

Plugins created with this boilerplate can use any license (typically GPL v2+ for WordPress.org).

## 🙌 Credits

Built with best practices from the WordPress community and inspired by leading WordPress plugins.

## 📞 Support

- **Issues**: [GitHub Issues](https://mayankmajeji.com/menupilot/issues)
- **Documentation**: See `docs/` folder
- **Questions**: Open a discussion

---

**Happy Plugin Development! 🚀**

Remember: This is a starting point. Customize it to fit your needs and build something amazing!
