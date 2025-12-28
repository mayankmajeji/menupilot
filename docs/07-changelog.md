# Changelog

All notable changes to MenuPilot will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-28

### Added
- Initial release of MenuPilot
- Menu export functionality
  - Export individual menus to JSON
  - Include menu structure, hierarchy, and metadata
  - Export context (site URL, WordPress version, theme, timestamp, user)
  - Support for Pages, Posts, Custom Post Types, Taxonomies, and Custom Links
- Menu import functionality
  - Import menus from JSON files
  - Create new menus (never overwrites existing)
  - Assign to theme locations
  - Preview before import
- Import preview screen
  - Menu details (name, items count, source/destination URLs)
  - Menu items mapping table
  - Auto-match status indicators
  - Manual mapping dropdowns
  - Remove items functionality
- Intelligent auto-matching
  - Match by post type + slug
  - Match by taxonomy + slug
  - Custom link URL normalization
  - Visual status indicators (Matched, Not Found, Custom Link)
- Manual mapping override
  - Per-item dropdown to map to Pages, Posts, Categories
  - Override auto-matched items
  - Keep items as custom links
- URL normalization
  - Automatic replacement of source URLs with destination URLs
  - Only applies when source ≠ destination
  - Preserves external URLs and query strings
- Structure preservation
  - Preserve parent/child hierarchy
  - Preserve menu item order
  - Preserve CSS classes, link attributes, descriptions
  - Convert missing objects to custom links without breaking hierarchy
- Remove items from import
  - Remove unwanted items before import
  - Visual feedback (grayed out, strikethrough)
  - Undo removal option
  - Smart hierarchy handling (children become top-level if parent removed)
- Custom confirmation modals
  - Remove item confirmation modal
  - Professional WordPress-styled modals
  - Keyboard shortcuts (ESC to close)
- REST API endpoints
  - `POST /menupilot/v1/menus/export` - Export menu
  - `POST /menupilot/v1/menus/import` - Import menu
  - `GET /menupilot/v1/menus/mapping-options` - Get mapping options
  - Admin-only access with authentication
- Admin interface
  - MenuPilot top-level menu
  - Settings page (Export Menu / Import Menu tabs)
  - Tools page (Import Settings / Export Settings / Reset Settings tabs)
  - Help page (Support / FAQs / System Info tabs)
  - Native WordPress styling
  - Responsive design
- Extensibility system
  - 15 WordPress hooks for extending functionality
  - Column Manager for custom preview columns
  - JSON extensions field for Pro data
  - Filter/action system throughout
- Documentation
  - Complete user guide
  - Developer guide with examples
  - Architecture documentation
  - Hook reference

### Security
- Admin-only access (`manage_options` capability)
- REST API authentication required
- Nonce verification on all requests
- Input sanitization
- Output escaping
- SQL injection prevention (prepared statements)

### Performance
- Client-side preview generation
- Efficient REST API endpoints
- Minimal database queries
- Optimized for large menus

### Compatibility
- WordPress 5.8+
- PHP 7.4+
- Classic menu system
- Works with Pages, Posts, WooCommerce products & categories, Custom Post Types

---

## [Unreleased]

### Planned for 1.1.0
- Quick export metabox on individual menu pages
- Export/import settings UI
- Bulk export (multiple menus)
- Export history/logging

### Planned for 2.0.0 (Pro)
- Automatic backups on menu save
- Restore from backup
- Menu duplication
- Merge/replace existing menus
- Scheduled backups
- WP-CLI commands
- Mega menu support
- Conditional visibility
- SEO metadata per item
- Menu icons
- A/B testing

---

## Version History

### 1.0.0 (2025-12-28)
- Initial release
- Core import/export functionality
- Preview and manual mapping
- Extensibility hooks

---

## Upgrade Notes

### From Pre-1.0.0 (Development)

If upgrading from a development version:

1. **Backup your site** before upgrading
2. **Deactivate** old version
3. **Delete** old plugin files
4. **Upload** new version
5. **Activate** MenuPilot
6. **Test** export/import functionality

No database migrations required. Settings (if any) are preserved.

---

## Breaking Changes

### None in 1.0.0

Version 1.0.0 is the initial release, so there are no breaking changes.

Future versions will maintain backward compatibility for:
- JSON export format (schema version 1.0)
- REST API endpoints
- WordPress hooks

---

## Deprecations

### None in 1.0.0

No deprecated features in initial release.

---

## Known Issues

### None Currently

If you encounter any issues, please report them via:
- GitHub Issues (if public)
- Email: support@menupilot.com

---

## Credits

### Development Team
- Initial development: 2025
- Architecture: WordPress best practices
- Design: Native WordPress admin UI

### Technologies Used
- WordPress Core APIs
- REST API
- jQuery (WordPress bundled)
- SCSS/CSS
- PHP 7.4+

---

**Next:** [Roadmap](./08-roadmap.md)

