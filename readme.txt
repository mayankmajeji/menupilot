=== MenuPilot – Preview-First Menu Import & Export ===
Contributors: mayankmajeji
Tags: menus, navigation, import export, migration
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Safely import and export WordPress navigation menus with a preview-first workflow. Review and map menus before importing.

== Description ==

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

== Features ==

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

== Requirements ==

* WordPress 5.8 or higher
* PHP 7.4 or higher
* Classic WordPress menu system

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/menupilot`, or install via the WordPress Plugins screen.
2. Activate the plugin through the “Plugins” screen.
3. Go to **MenuPilot** in the WordPress admin menu to access the plugin.

== Configuration ==

MenuPilot requires no configuration to start using. Once activated, you can immediately export and import menus.

== Usage ==

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

== Frequently Asked Questions ==

= Does MenuPilot overwrite existing menus? =

No. MenuPilot always imports menus as new menus. Existing menus are never overwritten.

= Can I preview changes before importing? =

Yes. MenuPilot shows a detailed preview screen before import, including matched and unmatched items. Nothing is imported until you confirm.

= What happens if a menu item cannot be matched? =

Unmatched items are clearly shown in the preview. You can manually map them to existing content or explicitly keep them as custom links.

= Does MenuPilot support multisite? =

No. Multisite support is intentionally not included in v1.

= Does MenuPilot support XML imports or bulk exports? =

No. MenuPilot v1 supports JSON format only and exports one menu at a time.

== Screenshots ==

1. Menu export screen
2. Import preview and mapping screen
3. Warning panel for missing or unmatched items
4. Import progress and completion notice

== Changelog ==

= 1.0.0 =
* Initial release
* Menu export (JSON)
* Preview-first menu import with intelligent matching
* Manual mapping and clear import feedback

== Upgrade Notice ==

= 1.0.0 =
Initial release.
