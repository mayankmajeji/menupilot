# Maverick Plugin Boilerplate

A modern, production-ready WordPress plugin boilerplate with enterprise-grade architecture, comprehensive testing suite, and automated tooling.

## 🚀 Features

- **Modern PHP Architecture** - PSR-4 autoloading, namespaces, type declarations
- **Complete Testing Suite** - Codeception with unit, integration, and acceptance tests
- **Build Tooling** - Gulp for asset compilation, Composer for PHP dependencies
- **Code Quality** - ESLint, PHPCS, PHPStan, Stylelint pre-configured
- **WordPress.org Ready** - Includes blueprint, readme.txt, proper i18n setup
- **Asset Pipeline** - SCSS compilation with modern CSS architecture
- **Developer-Friendly** - Comprehensive documentation and API reference structure
- **Settings Framework** - Flexible tab-based settings system with field renderer
- **Admin UI Templates** - Modular admin interface with icon system
- **Integration Ready** - Structure for plugin integrations (forms, e-commerce, etc.)

## 📋 Requirements

- WordPress 5.8+
- PHP 7.4+
- Node.js 18+
- Composer 2.0+

## 🎯 Quick Start

### Option 1: Automated Setup (Recommended)

```bash
# Run the setup script
bash setup.sh

# Follow the prompts to configure your plugin
```

### Option 2: Manual Setup

1. **Copy the boilerplate**
   ```bash
   cp -r maverick-plugin-boilerplate /path/to/wordpress/wp-content/plugins/your-plugin-name
   cd /path/to/wordpress/wp-content/plugins/your-plugin-name
   ```

2. **Find and replace the following placeholders:**

   | Placeholder | Example | Description |
   |------------|---------|-------------|
   | `MenuPilot` | `My Awesome Plugin` | Full plugin name |
   | `https://mayankmajeji.com/menupilot` | `https://example.com/my-plugin` | Plugin homepage URL |
   | `Easily import, export, duplicate, backup, and restore WordPress navigation menus. MenuPilot helps you move menus between sites safely with clean imports and reliable structure handling.` | `A powerful WordPress plugin` | Short description |
   | `1.0.0` | `1.0.0` | Initial version |
   | `Mayank Majeji` | `John Doe` | Your name |
   | `` | `https://example.com` | Your website |
   | `menupilot` | `my-plugin` | WordPress text domain (slug) |
   | `MenuPilot` | `MyPlugin` | PHP package name (PascalCase) |
   | `MenuPilot` | `MyPlugin` | PHP namespace |
   | `MENUPILOT` | `MY_PLUGIN` | Constant prefix (UPPER_SNAKE_CASE) |
   | `menupilot` | `my_plugin` | Function prefix (snake_case) |
   | `MenuPilot` | `My_Plugin` | Class prefix (Pascal_Snake_Case) |

3. **Rename the main plugin file**
   ```bash
   mv plugin-name.php your-plugin-slug.php
   ```

4. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

5. **Build assets**
   ```bash
   npm run gulp:styles
   ```

6. **Activate the plugin** in WordPress admin

## 📁 Directory Structure

```
your-plugin/
├── plugin-name.php          # Main plugin file
├── uninstall.php            # Cleanup on uninstall
├── includes/                # Core plugin classes
│   ├── class-init.php       # Main initialization class
│   ├── class-loader.php     # Autoloader
│   ├── class-settings.php   # Settings API
│   ├── functions-common.php # Helper functions
│   ├── admin/               # Admin-specific code
│   │   ├── templates/       # Admin templates
│   │   └── views/           # Admin view files
│   ├── settings/            # Settings framework
│   │   ├── class-settings-page.php
│   │   ├── class-tab.php
│   │   └── field-renderer.php
│   └── integrations/        # Third-party integrations
│       ├── core/            # WordPress core integrations
│       ├── forms/           # Form plugin integrations
│       └── ecommerce/       # E-commerce integrations
├── assets/                  # Frontend assets
│   ├── css/                 # Compiled CSS
│   ├── js/                  # JavaScript files
│   └── images/              # Images and icons
├── i18n/                    # Internationalization
│   └── languages/           # Translation files
├── tests/                   # Test suites
│   ├── unit/                # Unit tests
│   ├── integration/         # Integration tests
│   └── acceptance/          # Acceptance tests
├── docs/                    # Documentation
├── bin/                     # Build scripts
├── .wordpress-org/          # WordPress.org assets
├── composer.json            # PHP dependencies
├── package.json             # Node dependencies
├── gulpfile.js              # Gulp configuration
├── codeception.yml          # Codeception config
├── phpcs.xml.dist           # PHP CodeSniffer rules
├── phpstan.neon             # PHPStan configuration
├── .eslintrc.js             # ESLint configuration
└── README.md                # This file
```

## 🛠️ Development

### Build Tools

```bash
# Compile SCSS to CSS
npm run gulp:styles

# Watch for changes (auto-compile)
npm run gulp:watch

# Migrate SCSS @import to @use
npm run gulp:migrate-scss
```

### Code Quality

```bash
# Run all tests
composer test

# PHP linting
composer lint
composer format      # Auto-fix issues

# JavaScript linting
npm run lint:js
npm run fix:js       # Auto-fix issues

# CSS linting
npm run lint:css
npm run fix:css      # Auto-fix issues

# Run all linters
npm run lint:all
```

### Testing

```bash
# Run all tests
npm test

# Unit tests
npm run test:unit
codecept run unit

# Integration tests
npm run test:integration
composer run test:integration

# Acceptance tests
npm run test:acceptance
composer run test:acceptance

# Start test environment
composer run test:start

# Stop test environment
composer run test:stop

# Destroy test environment
composer run test:destroy
```

### PHPStan Analysis

```bash
composer run test:phpstan
```

## 🏗️ Architecture

### Singleton Pattern

The main `Init` class uses the singleton pattern:

```php
\MenuPilot\Init::get_instance()->init();
```

### PSR-4 Autoloading

Classes are autoloaded based on namespace:

```php
namespace MenuPilot;

class Example {
    // Automatically loaded from includes/class-example.php
}
```

### Settings Framework

The boilerplate includes a flexible settings system:

1. Create a settings tab by extending `Settings\Tab`
2. Define fields in your tab class
3. Register the tab with the settings page
4. Fields are automatically rendered and saved

### Hook System

Use the loader class to register hooks cleanly:

```php
$this->loader->add_action('init', $this, 'method_name');
$this->loader->add_filter('the_content', $this, 'filter_content');
```

## 🎨 Asset Management

### SCSS Architecture

```scss
assets/css/
├── main.scss                # Main stylesheet
├── admin.scss               # Admin styles
└── modules/                 # Modular SCSS
    ├── _variables.scss      # Variables
    ├── _mixins.scss         # Mixins
    ├── _base.scss           # Base styles
    └── _*.scss              # Feature modules
```

### JavaScript

- Place JS files in `assets/js/`
- Enqueue via `class-init.php` or respective admin classes
- Use `wp_localize_script()` for passing data from PHP to JS

## 📚 Documentation

After setup, customize these documentation files:

- `docs/API-REFERENCE.md` - Document your plugin's API, hooks, and filters
- `docs/TESTING.md` - Update testing guidelines for your plugin
- `docs/CONTRIBUTING.md` - Customize contribution guidelines
- `docs/WORDPRESS-ORG.md` - WordPress.org submission checklist
- `CHANGELOG.md` - Document version changes

## 🔌 Integrations

The boilerplate includes structure for common integrations:

### Core WordPress
- Login/Registration forms
- Comment forms
- Password reset

### Form Plugins
- Contact Form 7
- WPForms
- Ninja Forms
- Forminator
- Gravity Forms (add as needed)

### E-commerce
- WooCommerce
- Easy Digital Downloads (add as needed)

Add your own integrations in `includes/integrations/`

## 🚢 Deployment

### WordPress.org

1. Update `readme.txt` with your plugin details
2. Add screenshots to `.wordpress-org/`
3. Follow the checklist in `docs/WORDPRESS-ORG.md`
4. Use `.distignore` to exclude dev files from distribution

### Version Bumping

```bash
npm run bump:patch  # 1.0.0 -> 1.0.1
npm run bump:minor  # 1.0.0 -> 1.1.0
npm run bump:major  # 1.0.0 -> 2.0.0
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Follow the coding standards
4. Add tests for new features
5. Submit a pull request

## 📝 License

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html

## 🙏 Credits

Built with love by the WordPress community.

Based on modern WordPress development best practices and inspired by leading WordPress plugins.

---

**Ready to build something amazing?** 🚀

For questions or support, please open an issue or visit our documentation.
