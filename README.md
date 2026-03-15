# MenuPilot – Preview-First Menu Import & Export

Safely import and export WordPress navigation menus with a preview-first workflow. Review and map menus before importing.

[![Try it live](https://img.shields.io/badge/Try%20it%20live-WordPress%20Playground-3858e9?logo=wordpress)](https://playground.wordpress.net/?blueprint-url=https://wordpress.org/plugins/wp-json/plugins/v1/plugin/menupilot/blueprint.json)

## Description

MenuPilot is a focused menu import and export plugin designed to help you move **one menu at a time** between WordPress sites with confidence.

Unlike basic import tools, MenuPilot shows you exactly what will happen **before anything is imported**. You can review the menu structure, see which items match existing content, resolve missing items, and proceed only when everything is clear.

This makes MenuPilot ideal for moving menus from **staging to production**, local development to live sites, or between similar environments — without overwriting existing menus or breaking links silently.

### What MenuPilot Does

- Exports a single navigation menu as a JSON file
- Imports a menu as a **new menu** (never overwrites existing menus)
- Provides a full **preview and mapping screen before import**
- Intelligently matches menu items to existing content
- Preserves menu hierarchy, order, and metadata
- Replaces source site URLs with destination site URLs automatically
- Creates automatic backups before menu updates and imports
- Logs all import and export actions for accountability

## Features

* Backup & Restore:
  * Automatic backups before every menu update and import
  * Manual backup creation from the Menus page
  * One-click restore to any previous backup
  * Export backups as JSON for portability
  * Configurable backup limit (Backup Settings tab)
* Import/Export History Logs:
  * Dedicated History page under MenuPilot menu
  * Logs who, when, and what for every import/export
  * Download logs as JSON or plain text
  * Clear history with confirmation
* Export individual menus (JSON format)
* Import one menu at a time as a new menu
* Preview screen before import showing:
  * Menu name
  * Total menu items
  * Source site URL
  * Export timestamp
  * Matched and unmatched items
* Intelligent auto-matching of menu items using:
  * Post type + slug
  * Taxonomy + slug
  * Normalized custom links
* Manual mapping override per menu item
* Clear warnings for:
  * Missing pages, posts, or taxonomies
  * Items converted to custom links
  * Theme location availability
* Automatic home URL replacement during import
* Preserves:
  * Menu hierarchy
  * Menu order
  * CSS classes
  * Link attributes (target, rel)
  * Descriptions
* Visual import progress and clear success/error messages
* Native WordPress admin UI (no custom frameworks)

## Requirements

* WordPress 5.8 or higher
* PHP 7.4 or higher
* Classic WordPress menu system

## Installation

1. Upload the plugin files to `/wp-content/plugins/menupilot`, or install via the WordPress Plugins screen.
2. Activate the plugin through the "Plugins" screen.
3. Go to **MenuPilot** in the WordPress admin menu to access the plugin.

## Configuration

MenuPilot requires no configuration to start using. Once activated, you can immediately export and import menus.

Optional: Go to **MenuPilot → Settings** and open the **Backup** tab to configure the maximum number of backups to keep per menu (default: 5).

## Usage

### Backup & Restore

1. When editing a menu, scroll to the **MenuPilot Backup** section below the menu form.
2. Use the **Backup** tab to create manual backups, restore previous versions, or export backups as JSON.
3. Use the **Import** tab to import a previously exported backup JSON file.
4. Backups are also created automatically before every menu save and before every import.

### History Logs

1. Navigate to **MenuPilot → History** in the WordPress admin.
2. View all import and export actions with user, timestamp, menu name, and outcome.
3. Use the **Filter** button to narrow results by date range or user.
4. Download logs as JSON or plain text for record-keeping or auditing.
5. Use **Clear History** to remove all log entries (with confirmation). Export first if you need a backup.

### Exporting a Menu

1. Navigate to **MenuPilot → Export Menu** in the WordPress admin.
2. Select the menu you want to export from the dropdown.
3. Click the "Export Menu" button.
4. A JSON file will be downloaded to your computer.
5. Save this file for importing to another site.

### Importing a Menu

1. Navigate to **MenuPilot → Import Menu** in the WordPress admin.
2. Click "Choose File" and select a previously exported JSON file.
3. Click "Upload & Preview" to see what will be imported.
4. Review the preview screen:
   * Check menu name (you can edit it)
   * Review matched and unmatched items
   * Manually map items if needed
   * Remove items you don't want to import
   * Optionally assign the menu to a theme location
5. Click "Import Menu" to complete the import.
6. The menu will be created as a new menu (existing menus are never overwritten).

### Important Notes

* MenuPilot always imports menus as **new menus** - it never overwrites existing menus.
* Always review the preview screen before importing to ensure items are matched correctly.
* Unmatched items will be converted to custom links automatically.
* You can manually map items to different content using the "Map To" dropdown in the preview.

## Frequently Asked Questions

### Does MenuPilot overwrite existing menus?

No. MenuPilot always imports menus as new menus. Existing menus are never overwritten.

### Can I preview changes before importing?

Yes. MenuPilot shows a detailed preview screen before import, including matched and unmatched items. Nothing is imported until you confirm.

### What happens if a menu item cannot be matched?

Unmatched items are clearly shown in the preview. You can manually map them to existing content or explicitly keep them as custom links.

### Does MenuPilot support multisite?

Multisite support is not currently available, but may be added in future versions.

### Does MenuPilot support XML imports or bulk exports?

No. MenuPilot supports JSON format only and exports one menu at a time.

### When are backups created?

Backups are created automatically before every menu update (including native menu edits) and before every import. You can also create manual backups from the Backup section on the Menus page.

### Where can I view import and export history?

Go to **MenuPilot → History** to view all import and export actions. You can filter by date and user, and download logs as JSON or plain text.

## Changelog

### 1.0.3
* Backup & Restore: automatic backups before menu updates and imports
* Manual backup creation, one-click restore, and export backups as JSON
* Backup Settings tab to configure maximum backups per menu
* Import/Export History Logs: dedicated History page with filter and download (JSON/plain text)
* Backup UI: brand styling for buttons, active tab, and consistent button sizing
* Code quality: PHPCS, ESLint, and Stylelint configuration

### 1.0.2
* UI improvements and bug fixes

### 1.0.0
* Initial release
* Menu export (JSON)
* Preview-first menu import with intelligent matching
* Manual mapping and clear import feedback

## Upgrade Notice

### 1.0.3
Backup & Restore, Import/Export History Logs, and UI improvements.

### 1.0.0
Initial release.

## License

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html

## Credits

**Contributors:** mayankmajeji

**Tags:** menus, navigation, import export, migration

**Requires at least:** WordPress 5.8

**Tested up to:** WordPress 6.9

**Requires PHP:** 7.4

**Stable tag:** 1.0.3
