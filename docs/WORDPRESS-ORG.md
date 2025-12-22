# WordPress.org Submission Checklist

This checklist helps ensure your plugin is ready for submission to the WordPress.org plugin directory.

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
- [ ] Tested on multiple WordPress versions
- [ ] Unit tests passing
- [ ] Integration tests passing
- [ ] Manual testing completed
- [ ] Tested with common themes
- [ ] Tested with common plugins
- [ ] Performance tested

### Legal & Licensing

- [ ] GPL v2 or later license
- [ ] License headers in all files
- [ ] No proprietary code
- [ ] Third-party libraries properly licensed
- [ ] Attribution for borrowed code

## readme.txt Requirements

Your `readme.txt` must include:

```
=== Plugin Name ===
Contributors: username
Tags: tag1, tag2
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Short description

== Description ==
Long description

== Installation ==
Installation steps

== Frequently Asked Questions ==
FAQs

== Screenshots ==
1. Screenshot description

== Changelog ==
Version history

== Upgrade Notice ==
Upgrade notes
```

## Plugin Header Requirements

```php
/**
 * Plugin Name: Plugin Name
 * Plugin URI: https://example.com/plugin
 * Description: Description
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Author Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: plugin-slug
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
