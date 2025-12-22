# Maverick Plugin Boilerplate

**Version:** 1.0.0  
**Created:** December 2024  
**License:** MIT

## 🎉 What You Have

A complete, production-ready WordPress plugin boilerplate with:

✅ **Modern PHP architecture** (PSR-4, namespaces, type declarations)  
✅ **Automated setup script** (one command to create your plugin)  
✅ **Complete testing suite** (Unit, Integration, Acceptance)  
✅ **Build tooling** (Gulp, SCSS, ESLint, PHPCS, PHPStan)  
✅ **Settings framework** (Flexible, extensible, tab-based)  
✅ **WordPress.org ready** (readme.txt, assets, .distignore)  
✅ **Comprehensive documentation** (API, Testing, Usage examples)  
✅ **Integration structure** (Forms, E-commerce, Community plugins)

## 📂 What's Inside

```
maverick-plugin-boilerplate/
├── 📄 setup.sh                    ⭐ Run this to create your plugin!
├── 📄 plugin-name.php             Main plugin file (rename after setup)
├── 📄 uninstall.php               Clean uninstall handler
│
├── 📁 includes/                   Core PHP classes
│   ├── class-init.php             Main initialization
│   ├── class-loader.php           PSR-4 autoloader
│   ├── class-settings.php         Settings framework
│   ├── functions-common.php       Utility functions
│   ├── admin/                     Admin UI templates
│   ├── settings/                  Settings system classes
│   └── integrations/              Plugin integrations
│       ├── core/                  WordPress core
│       ├── forms/                 Form plugins
│       ├── ecommerce/             E-commerce plugins
│       └── ...
│
├── 📁 assets/                     Frontend assets
│   ├── css/                       SCSS stylesheets
│   │   ├── main.scss             Frontend styles
│   │   ├── admin.scss            Admin styles
│   │   └── modules/              Modular SCSS files
│   ├── js/                        JavaScript files
│   └── images/                    Images and icons
│
├── 📁 tests/                      Complete test suite
│   ├── unit/                      Unit tests
│   ├── integration/               Integration tests
│   ├── acceptance/                Acceptance tests
│   └── _support/                  Test helpers
│
├── 📁 docs/                       Documentation
│   ├── API-REFERENCE.md          Complete API docs
│   ├── TESTING.md                Testing guide
│   ├── USAGE-EXAMPLES.md         Code examples
│   └── WORDPRESS-ORG.md          Publishing checklist
│
├── 📁 i18n/                       Internationalization
│   └── languages/                 Translation files
│
├── 📄 Configuration Files
│   ├── composer.json              PHP dependencies
│   ├── package.json               Node.js dependencies
│   ├── gulpfile.js                Gulp build tasks
│   ├── codeception.yml            Testing configuration
│   ├── phpcs.xml.dist             PHP CodeSniffer rules
│   ├── phpstan.neon               PHPStan configuration
│   ├── .eslintrc.js               JavaScript linting
│   ├── .stylelintrc.json          CSS linting
│   ├── .editorconfig              Editor settings
│   ├── .nvmrc                     Node.js version
│   ├── .gitignore                 Git ignore rules
│   ├── .gitattributes             Git attributes
│   └── .distignore                Distribution ignore
│
└── 📄 Documentation
    ├── README.md                   Main documentation
    ├── QUICKSTART.md               Quick start guide
    ├── BOILERPLATE-OVERVIEW.md     Complete overview
    ├── CHANGELOG.md                Version history
    ├── CONTRIBUTING.md             Contribution guide
    ├── LICENSE                     MIT License
    └── readme.txt                  WordPress.org readme
```

## 🚀 Quick Start

### Option 1: Automated Setup (Recommended)

```bash
cd maverick-plugin-boilerplate
bash setup.sh
```

The script will:
1. Ask for your plugin details
2. Replace all template variables
3. Rename files appropriately
4. Install dependencies
5. Build assets
6. Clean up after itself

### Option 2: Manual Setup

1. Copy to plugins directory
2. Find & replace template variables (see QUICKSTART.md)
3. Rename `plugin-name.php`
4. Run `composer install && npm install`
5. Build assets: `npm run gulp:styles`

## 🎯 Template Variables

All these are replaced by `setup.sh`:

| Variable | Example |
|----------|---------|
| `MenuPilot` | My Awesome Plugin |
| `menupilot` | my-awesome-plugin |
| `MenuPilot` | MyAwesomePlugin |
| `MENUPILOT` | MY_AWESOME_PLUGIN |
| `menupilot` | my_awesome_plugin |
| And more... |

## 📚 Documentation

Start with these files:

1. **[QUICKSTART.md](QUICKSTART.md)** - Get started in 5 minutes
2. **[BOILERPLATE-OVERVIEW.md](BOILERPLATE-OVERVIEW.md)** - Complete feature overview
3. **[docs/USAGE-EXAMPLES.md](docs/USAGE-EXAMPLES.md)** - Code examples
4. **[docs/API-REFERENCE.md](docs/API-REFERENCE.md)** - API documentation
5. **[docs/TESTING.md](docs/TESTING.md)** - Testing guide
6. **[docs/WORDPRESS-ORG.md](docs/WORDPRESS-ORG.md)** - Publishing checklist

## 🛠️ Development Commands

### Build & Watch
```bash
npm run gulp:styles        # Compile SCSS
npm run gulp:watch         # Watch for changes
```

### Code Quality
```bash
composer lint              # PHP CodeSniffer check
composer format            # Auto-fix PHP issues
npm run lint:js            # JavaScript linting
npm run lint:css           # CSS/SCSS linting
npm run lint:all           # All linters
```

### Testing
```bash
composer test              # All PHP tests
vendor/bin/codecept run unit       # Unit tests
vendor/bin/codecept run integration # Integration tests
vendor/bin/codecept run acceptance  # Acceptance tests
```

### Analysis
```bash
composer run test:phpstan  # Static analysis
```

## ✨ Key Features

### 1. Flexible Settings Framework

```php
add_filter('your_plugin_settings_fields', function($fields) {
    $fields[] = array(
        'field_id' => 'api_key',
        'type' => 'text',
        'label' => __('API Key', 'your-plugin'),
        'tab' => 'general',
        'section' => 'api',
    );
    return $fields;
});
```

### 2. Clean Hook System

```php
add_action('your_plugin_init', function($instance) {
    // Extend plugin functionality
});

add_action('your_plugin_init_integrations', function() {
    // Register integrations
});
```

### 3. PSR-4 Autoloading

```php
namespace YourPlugin;

class MyClass {
    // Automatically loaded from includes/class-myclass.php
}
```

### 4. Comprehensive Testing

- Unit tests for isolated testing
- Integration tests for WordPress integration
- Acceptance tests for user workflows
- Pre-configured with Codeception

## 📦 What Makes This Special

### Compared to Other Boilerplates

✅ **Automated setup** - One command, done  
✅ **Modern PHP** - Type declarations, PSR-4, namespaces  
✅ **Complete testing** - Not just structure, real examples  
✅ **Settings framework** - Flexible, extensible, production-ready  
✅ **Build tooling** - Gulp, SCSS, modern CSS architecture  
✅ **Code quality** - PHPCS, PHPStan, ESLint, Stylelint  
✅ **Documentation** - Comprehensive guides and examples  
✅ **Integration structure** - Ready for form, e-commerce integrations  
✅ **WordPress.org ready** - All files and formats included  

## 🎓 Learning Path

### Beginner
1. Run `setup.sh` to create your plugin
2. Read `QUICKSTART.md`
3. Try adding a simple setting
4. Modify the admin CSS

### Intermediate
1. Review `docs/USAGE-EXAMPLES.md`
2. Add a custom post type
3. Create a REST API endpoint
4. Add AJAX functionality

### Advanced
1. Study `docs/API-REFERENCE.md`
2. Write unit tests for your code
3. Create custom integrations
4. Build complex settings tabs

## 🔒 Security Built-In

✅ Nonce verification  
✅ Capability checks  
✅ Input sanitization  
✅ Output escaping  
✅ ABSPATH checks  
✅ No direct file access  

## 🌐 Internationalization Ready

✅ Text domain properly set  
✅ All strings wrapped in translation functions  
✅ POT file template included  
✅ Domain path configured  

## 📊 Code Quality

- Follows WordPress Coding Standards
- PSR-4 autoloading
- Type declarations (PHP 7.4+)
- PHPDoc comments
- Modern JavaScript (ES6+)
- SCSS with module system
- Automated linting

## 🎨 Customization

Everything is customizable:
- Modify any template file
- Add/remove features
- Change structure as needed
- Extend with your own classes
- Use provided hooks and filters

## 🤝 Contributing

Found an issue? Have a suggestion?

1. Open an issue
2. Submit a pull request
3. Share your improvements

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## 📄 License

This boilerplate: **MIT License**

Plugins you create: **Your choice** (typically GPL v2+ for WordPress.org)

## 🙏 Credits

Built with ❤️ based on:
- WordPress best practices
- Community feedback
- Real-world plugin development experience
- Leading WordPress plugins' architecture

## 🆘 Support

- **Documentation**: Check the `docs/` folder
- **Examples**: See `docs/USAGE-EXAMPLES.md`
- **Issues**: Open a GitHub issue
- **Questions**: Start a discussion

## 🎯 What's Next?

1. **Run the setup**: `bash setup.sh`
2. **Read the docs**: Start with `QUICKSTART.md`
3. **Start coding**: Build something amazing!
4. **Test everything**: Use the included test suite
5. **Share it**: Publish to WordPress.org

---

## 🚀 Ready to Build?

```bash
cd maverick-plugin-boilerplate
bash setup.sh
```

**That's it!** Your professional WordPress plugin foundation is ready.

Now go build something incredible! 🌟

---

**Made with ❤️ for the WordPress community**
