# WordPress.org Submission Checklist

This checklist helps ensure MenuPilot is ready for submission to the WordPress.org plugin directory.

## Pre-Submission Checklist

### Code Quality

- [ ] All code follows [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [ ] No PHP errors or warnings
- [ ] No JavaScript console errors
- [ ] Passes PHP CodeSniffer checks
- [ ] Passes PHPStan analysis
- [ ] All functions are properly prefixed
- [ ] Text domain matches plugin slug
- [ ] All strings are internationalized

### Security

- [ ] All user inputs are sanitized
- [ ] All outputs are escaped
- [ ] Nonce verification on all forms
- [ ] Capability checks on all admin functions
- [ ] No direct file access (ABSPATH check)
- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities
- [ ] No CSRF vulnerabilities

### Functionality

- [ ] Plugin activates without errors
- [ ] Plugin deactivates cleanly
- [ ] Uninstall removes all data (if applicable)
- [ ] No fatal errors on PHP 7.4+
- [ ] Works with latest WordPress version
- [ ] Works with WordPress 5.8+
- [ ] Responsive admin interface
- [ ] Accessible (WCAG 2.0 AA)

### Files & Structure

- [ ] readme.txt properly formatted
- [ ] Valid plugin header in main file
- [ ] LICENSE file included
- [ ] .distignore configured
- [ ] No vendor/node_modules in distribution
- [ ] Assets optimized (minified CSS/JS)
- [ ] No unnecessary files

### Documentation

- [ ] Clear installation instructions
- [ ] Usage documentation
- [ ] FAQ section
- [ ] Screenshots (if applicable)
- [ ] Changelog maintained
- [ ] API documentation (for developers)

### WordPress.org Assets

- [ ] Icon (256x256 and 128x128)
- [ ] Banner (1544x500 and 772x250)
- [ ] Screenshots
- [ ] .wordpress-org directory structure

### Testing

- [ ] Tested on multiple PHP versions (7.4, 8.0, 8.1, 8.2)
- [ ] Tested on multiple WordPress versions (5.8, 6.0, 6.1, 6.2+)
- [ ] Manual testing completed
  - [ ] Export menu functionality
  - [ ] Import menu functionality
  - [ ] Preview and manual mapping
  - [ ] Remove items from import
  - [ ] URL normalization
- [ ] Tested with common themes (Twenty Twenty-Four, GeneratePress, etc.)
- [ ] Tested with common plugins (WooCommerce, etc.)
- [ ] Performance tested (large menus with 100+ items)
- [ ] REST API endpoints tested
- [ ] Security tested (authentication, authorization, nonces)

### Legal & Licensing

- [ ] GPL v2 or later license
- [ ] License headers in all files
- [ ] No proprietary code
- [ ] Third-party libraries properly licensed
- [ ] Attribution for borrowed code

## readme.txt Requirements

Your `readme.txt` must include:

```
=== MenuPilot ===
Contributors: your-username
Tags: menu, import, export, navigation, backup, migration
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Safely import and export WordPress menus between sites with preview, intelligent matching, and zero surprises.

== Description ==
MenuPilot allows you to safely import and export WordPress menus between sites with preview, intelligent matching, and zero surprises.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/menupilot` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to MenuPilot → Settings to start exporting/importing menus

== Frequently Asked Questions ==
= How do I export a menu? =
Go to MenuPilot → Settings → Export Menu tab, select a menu, and click "Export Menu".

= How do I import a menu? =
Go to MenuPilot → Settings → Import Menu tab, upload a JSON file, preview the import, and click "Import Menu".

= Can I preview before importing? =
Yes! MenuPilot shows a detailed preview with auto-matching status and allows manual mapping before import.

= Does it work with custom post types? =
Yes, MenuPilot supports Pages, Posts, Custom Post Types, Taxonomies, and Custom Links.

== Screenshots ==
1. Export menu interface
2. Import preview with manual mapping
3. Menu items mapping table

== Changelog ==
= 1.0.0 =
* Initial release
* Menu export to JSON
* Menu import with preview
* Intelligent auto-matching
* Manual mapping override
* URL normalization
* Remove items from import

== Upgrade Notice ==
= 1.0.0 =
Initial release of MenuPilot.
```

## Plugin Header Requirements

```php
/**
 * Plugin Name: MenuPilot
 * Plugin URI: https://menupilot.com
 * Description: Safely import and export WordPress menus between sites with preview, intelligent matching, and zero surprises.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: menupilot
 * Domain Path: /i18n/languages
 */
```

## Assets for WordPress.org

### Icon

- **Size:** 256x256 and 128x128
- **Format:** PNG or JPG
- **Filename:** `icon-256x256.png` and `icon-128x128.png`
- **Location:** `.wordpress-org/` directory

### Banner

- **Size:** 1544x500 (retina: 3088x1000)
- **Format:** PNG or JPG
- **Filename:** `banner-1544x500.png` (retina: `banner-3088x1000.png`)
- **Alternative:** 772x250 (retina: 1544x500)
- **Location:** `.wordpress-org/` directory

### Screenshots

- **Format:** PNG or JPG
- **Filename:** `screenshot-1.png`, `screenshot-2.png`, etc.
- **Location:** `.wordpress-org/` directory
- Reference in readme.txt

## Submission Process

1. **Create WordPress.org Account**
   - Go to [wordpress.org/support/register.php](https://wordpress.org/support/register.php)

2. **Submit Plugin**
   - Go to [wordpress.org/plugins/developers/add/](https://wordpress.org/plugins/developers/add/)
   - Upload ZIP file (must contain only plugin files)
   - Wait for review (typically 3-14 days)

3. **Setup SVN Access**
   - After approval, you'll receive SVN credentials
   - Checkout: `svn co https://plugins.svn.wordpress.org/your-plugin your-plugin`

4. **Deploy to WordPress.org**
   ```bash
   # Add files
   svn add trunk/*
   
   # Add assets
   svn add assets/*
   
   # Commit
   svn ci -m "Initial commit"
   
   # Tag release
   svn cp trunk tags/1.0.0
   svn ci -m "Tagging version 1.0.0"
   ```

## Common Rejection Reasons

- **Security issues** - Unsanitized input, unescaped output
- **Trademark violations** - Using trademarked names
- **Obfuscated code** - Encoded or obfuscated PHP
- **Calling external resources** - Without user consent
- **Phoning home** - Tracking without permission
- **Spam/SEO** - Links in plugin for SEO purposes

## After Approval

- [ ] Setup SVN repository
- [ ] Commit initial version
- [ ] Upload assets
- [ ] Create first tag
- [ ] Test installation from WordPress.org
- [ ] Monitor support forum
- [ ] Respond to reviews

## Resources

- [Plugin Developer Handbook](https://developer.wordpress.org/plugins/)
- [Plugin Handbook: Detailed Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)
- [Plugin Review Team Guidelines](https://make.wordpress.org/plugins/handbook/)
- [Common Mistakes](https://developer.wordpress.org/plugins/wordpress-org/common-issues/)

## Support After Release

1. **Monitor Support Forum**
   - Respond to user questions
   - Address bug reports
   - Consider feature requests

2. **Regular Updates**
   - Test with new WordPress versions
   - Update "Tested up to" version
   - Fix security issues promptly
   - Add requested features

3. **Documentation**
   - Keep readme.txt updated
   - Maintain changelog
   - Update screenshots when UI changes

4. **Communication**
   - Be responsive to users
   - Acknowledge issues
   - Set expectations for fixes
