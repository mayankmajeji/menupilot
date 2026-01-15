# MenuPilot Features

Complete list of features in MenuPilot V1.0.0

## Core Features

### 📤 Menu Export

#### Export Individual Menus

- Export any WordPress menu to JSON format
- One-click export from admin interface
- Includes complete menu structure

#### Export Format: JSON Only

- Human-readable JSON format
- Easy to edit manually if needed
- Standard format for portability

#### Exported Data Includes:

- ✅ Menu structure (parent/child hierarchy)
- ✅ Menu item order
- ✅ Menu item types:
  - Pages
  - Posts
  - Custom Post Types
  - Taxonomies (Categories, Tags, etc.)
  - Custom Links
- ✅ Menu item metadata:
  - CSS classes
  - Link relationship (rel)
  - Link target (\_blank)
  - Description
- ✅ Theme locations (assigned locations)
- ✅ Export context:
  - Source site URL
  - WordPress version
  - Theme name
  - Export timestamp
  - Exported by (username)

### 📥 Menu Import

#### Import One Menu at a Time

- Upload JSON file
- Preview before importing
- Create as new menu (never overwrites)

#### Import Options:

- ✅ Set custom menu name
- ✅ Assign to theme location (optional)
- ✅ Preview all items before import

#### Import Process:

1. Upload JSON file
2. Preview menu structure
3. Review auto-matches
4. Adjust mappings if needed
5. Remove unwanted items
6. Import with confidence

### 🔍 Import Preview Screen

#### Menu Details Shown:

- Menu name
- Total menu items
- Source site URL
- Destination site URL
- Export timestamp
- Matched/unmatched count

#### Warning Panel:

- ⚠️ Missing pages/posts/taxonomies
- ⚠️ Items converted to custom links
- ⚠️ Theme location availability

#### Clear Actions:

- "Import Menu" button (with selected mappings)
- "Cancel" button

### 🧠 Intelligent Auto-Matching

#### Matching Logic:

- **Post Type + Slug:** Matches pages/posts by slug
- **Taxonomy + Slug:** Matches categories/tags by slug
- **Custom Link URL:** Normalizes URLs automatically

#### Match Status Display:

- ✅ **Matched:** Shows matched item name and ID
- ⚠️ **Not Found:** Will be converted to custom link
- 🔗 **Custom Link:** Will be imported as-is

#### Visual Indicators:

- Green checkmark for matched items
- Warning icon for unmatched items
- Link icon for custom links

### 🎛️ Manual Mapping Override

#### Per-Item Control:

- Dropdown to map each item to:
  - Pages
  - Posts
  - Categories
  - Custom Link (keep as-is)

#### Override Auto-Matches:

- Change any auto-matched item
- Map unmatched items to specific content
- Keep items as custom links

#### Validation:

- Shows which items are resolved
- Blocks import if required items unresolved (optional)
- Allows explicit "keep as custom link"

### 🔗 URL Normalization

#### Automatic URL Replacement:

- Detects source site URL from export
- Replaces with destination site URL during import
- Applies to:
  - Custom links
  - Internal links

#### Smart Detection:

- Only replaces if source ≠ destination
- Preserves external URLs
- Maintains query strings and fragments

### 📐 Structure Preservation

#### Preserved Elements:

- ✅ Parent/child hierarchy
- ✅ Menu item order
- ✅ CSS classes
- ✅ Link attributes (target, rel)
- ✅ Description

#### Missing Object Handling:

- Converts missing objects to custom links
- Preserves hierarchy (no broken structure)
- Maintains item order

### 🗑️ Remove Items from Import

#### Item Removal:

- Remove unwanted items before import
- Visual feedback (grayed out, strikethrough)
- Undo removal option

#### Smart Hierarchy:

- If parent removed, children become top-level
- No orphaned items
- Clean structure maintained

### 📊 Import Progress & Feedback

#### Visual Indicators:

- Progress spinner during import
- Success confirmation message
- Clear error messages
- Link to edit imported menu

#### No Silent Failures:

- All errors displayed clearly
- Success messages with menu ID
- Direct link to menu editor

## User Interface Features

### 🎨 Native WordPress Design

- Uses WordPress admin styles
- Familiar interface (no learning curve)
- Responsive design
- Accessible (WCAG compliant)

### 📱 Admin Menu Structure

```
MenuPilot (Top Level)
├── Settings (Export Menu / Import Menu tabs)
├── Tools (Import Settings / Export Settings / Reset Settings tabs)
└── Help (Support / FAQs / System Info tabs)
```

### 🔐 Security Features

- Admin-only access (manage_options capability)
- REST API authentication required
- Nonce verification
- Input sanitization
- Output escaping

## Technical Features

### 🔌 Extensibility

- 15 WordPress hooks for extending
- Column Manager for custom columns
- JSON extensions field for Pro data
- Filter/action system throughout

### 🚀 Performance

- Efficient REST API endpoints
- Client-side preview generation
- No unnecessary database queries
- Optimized for large menus

### 🛡️ Compatibility

- WordPress 5.8+
- PHP 7.4+
- Classic menu system
- Works with:
  - Pages
  - Posts
  - WooCommerce products & categories
  - Custom Post Types
  - Custom Taxonomies

## What's NOT Included (V1 Scope)

These features are explicitly **not** in V1:

- ❌ Multisite support
- ❌ Backup & restore (automatic)
- ❌ Menu duplication
- ❌ Merge/replace existing menus
- ❌ Scheduled backups
- ❌ WP-CLI commands
- ❌ Mega menu support
- ❌ Conditional visibility
- ❌ Multiple import formats (XML, CSV)
- ❌ Automation/syncing

_See [Roadmap](./08-roadmap.md) for future versions_

---

**Next:** [Installation](./03-installation.md)
