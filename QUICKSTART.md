# Quick Start Guide

Get your plugin up and running in minutes with the **Maverick Plugin Boilerplate**.

## 🚀 One-Command Setup

```bash
bash setup.sh
```

The setup script will guide you through:
1. Plugin naming and branding
2. Author information
3. Automatic file generation
4. Dependency installation
5. Asset compilation

## 📝 Manual Setup (Alternative)

If you prefer manual setup:

### 1. Copy Files

```bash
cp -r maverick-plugin-boilerplate /path/to/wordpress/wp-content/plugins/your-plugin
cd /path/to/wordpress/wp-content/plugins/your-plugin
```

### 2. Find & Replace

Replace these placeholders throughout all files:

| Find | Replace With | Example |
|------|-------------|---------|
| `MenuPilot` | Your Plugin Name | `My Awesome Plugin` |
| `Easily import, export, duplicate, backup, and restore WordPress navigation menus. MenuPilot helps you move menus between sites safely with clean imports and reliable structure handling.` | Short description | `A powerful WordPress plugin` |
| `https://mayankmajeji.com/menupilot` | Plugin homepage | `https://example.com/my-plugin` |
| `1.0.0` | Initial version | `1.0.0` |
| `Mayank Majeji` | Your name | `John Doe` |
| `` | Your website | `https://example.com` |
| `menupilot` | Plugin slug | `my-awesome-plugin` |
| `MenuPilot` | PHP namespace | `MyAwesomePlugin` |
| `MenuPilot` | Package name | `MyAwesomePlugin` |
| `MENUPILOT` | Constants prefix | `MY_AWESOME_PLUGIN` |
| `menupilot` | Functions prefix | `my_awesome_plugin` |
| `MenuPilot` | Class prefix | `My_Awesome_Plugin` |

### 3. Rename Main File

```bash
mv plugin-name.php your-plugin-slug.php
```

### 4. Install Dependencies

```bash
composer install
npm install
```

### 5. Build Assets

```bash
npm run gulp:styles
```

## 🎯 What's Next?

### Start Building

1. **Add your logic** in `includes/class-init.php`
2. **Create settings** using the settings framework
3. **Add integrations** in `includes/integrations/`
4. **Customize admin UI** in `includes/admin/templates/`

### Example: Add a Custom Setting

```php
add_filter('your_plugin_settings_fields', function($fields) {
    $fields[] = array(
        'field_id' => 'api_key',
        'type' => 'text',
        'label' => 'API Key',
        'description' => 'Enter your API key',
        'tab' => 'general',
        'section' => 'api_settings',
        'priority' => 10,
    );
    return $fields;
});
```

### Example: Create an Integration

```php
// includes/integrations/forms/class-contact-form-7.php
namespace YourPlugin\Integrations;

class Contact_Form_7 {
    public function __construct() {
        if (!defined('WPCF7_VERSION')) {
            return;
        }
        add_action('wpcf7_init', array($this, 'init'));
    }
    
    public function init() {
        // Your integration logic
    }
}
```

## 🧪 Testing

```bash
# Run all tests
composer test

# PHP linting
composer lint

# JavaScript linting
npm run lint:js

# All linters
npm run lint:all
```

## 📦 Build for Distribution

1. Update version in:
   - `plugin-name.php`
   - `readme.txt`
   - `package.json`
   - `CHANGELOG.md`

2. Build assets:
   ```bash
   npm run gulp:styles
   ```

3. Create ZIP (excluding dev files via `.distignore`):
   ```bash
   zip -r your-plugin.zip . -x@.distignore
   ```

## 🎨 Customize

### Change Admin Icon

Replace the menu icon in `includes/class-settings.php`:

```php
add_menu_page(
    __('Your Plugin', 'your-plugin'),
    __('Your Plugin', 'your-plugin'),
    'manage_options',
    'your-plugin-settings',
    array($this, 'render_settings_page'),
    'dashicons-admin-generic', // Change this icon
    65
);
```

### Add Custom CSS

Edit `assets/css/admin.scss` or `assets/css/main.scss`:

```scss
.your-plugin-wrapper {
    .custom-element {
        background: #f0f0f0;
        padding: 20px;
    }
}
```

Then compile:
```bash
npm run gulp:styles
```

### Add Custom JavaScript

Edit `assets/js/admin.js` or `assets/js/main.js`:

```javascript
(function ($) {
    'use strict';
    
    $(document).ready(function () {
        // Your code here
    });
})(jQuery);
```

## 🆘 Troubleshooting

### Composer Install Fails

```bash
# Try clearing cache
composer clear-cache
composer install
```

### NPM Install Fails

```bash
# Clear cache and retry
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

### SCSS Won't Compile

```bash
# Check Node version
node --version  # Should be 18+

# Reinstall Gulp
npm install --save-dev gulp gulp-sass sass
```

### Plugin Activation Error

Check for:
1. PHP syntax errors: `php -l plugin-name.php`
2. Missing dependencies: `composer install`
3. File permissions

## 📚 Resources

- **Documentation**: Check the `docs/` folder
- **API Reference**: `docs/API-REFERENCE.md`
- **Testing Guide**: `docs/TESTING.md`
- **WordPress.org**: `docs/WORDPRESS-ORG.md`

## 💡 Tips

1. **Use the hooks**: The boilerplate provides many action/filter hooks
2. **Follow WP standards**: Run `composer lint` regularly
3. **Write tests**: Start with unit tests for your main classes
4. **Document your code**: Add PHPDoc blocks to all functions
5. **Version control**: Commit after each feature
6. **Keep it modular**: Separate concerns into different classes

## 🎉 You're Ready!

Your plugin foundation is set. Start building something amazing!

Questions? Check the [CONTRIBUTING.md](CONTRIBUTING.md) or open an issue.

Happy coding! 🚀
