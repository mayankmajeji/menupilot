# User Guide

Complete guide to using MenuPilot for menu import and export.

## Table of Contents

1. [Exporting Menus](#exporting-menus)
2. [Importing Menus](#importing-menus)
3. [Understanding the Preview](#understanding-the-preview)
4. [Manual Mapping](#manual-mapping)
5. [Removing Items](#removing-items)
6. [URL Normalization](#url-normalization)
7. [Best Practices](#best-practices)
8. [Common Workflows](#common-workflows)

---

## Exporting Menus

### Step 1: Navigate to Export Page

1. Go to **MenuPilot → Settings**
2. Click the **"Export Menu"** tab (default)

### Step 2: Select Menu

1. In the **"Select Menu"** dropdown, choose the menu you want to export
2. Available menus are listed by name

### Step 3: Export

1. Click the **"Export Menu"** button
2. Your browser will download a JSON file
3. File name format: `menu-[menu-slug]-[date]-[time].json`

### Step 4: Save the File

- Save the JSON file to a safe location
- You can rename it if needed
- Keep it for backup or import to another site

### Export File Contents

The exported JSON file contains:
- Menu name and slug
- All menu items with hierarchy
- Item types (Page, Post, Custom Link, etc.)
- URLs and metadata
- CSS classes and attributes
- Theme locations

---

## Importing Menus

### Step 1: Navigate to Import Page

1. Go to **MenuPilot → Settings**
2. Click the **"Import Menu"** tab

### Step 2: Upload JSON File

1. Click **"Choose File"** or drag-and-drop
2. Select the exported JSON file
3. File must be valid JSON format
4. Click **"Upload & Preview"** button

### Step 3: Review Preview

The preview modal shows:

#### Menu Details
- Menu name (editable)
- Total items
- Source site URL
- Destination site URL
- Export timestamp

#### Menu Items Table
- **Title:** Menu item name with hierarchy
- **Type:** Page, Post, Custom Link, etc.
- **Auto Status:** Matched, Not Found, or Custom Link
- **Map To:** Dropdown to change mapping
- **Remove:** Button to exclude item

### Step 4: Configure Import

1. **Menu Name:** Edit if needed (defaults to exported name)
2. **Assign to Location:** Choose theme location (optional)
3. **Review Items:** Check auto-matches and adjust if needed

### Step 5: Adjust Mappings (Optional)

See [Manual Mapping](#manual-mapping) section below.

### Step 6: Remove Items (Optional)

See [Removing Items](#removing-items) section below.

### Step 7: Import

1. Click **"Import Menu"** button
2. Wait for import to complete
3. Success message appears with link to edit menu
4. Menu is created and ready to use

---

## Understanding the Preview

### Menu Details Section

Shows high-level information:
- **Menu Name:** What the menu will be called
- **Total Items:** Number of items in menu
- **Exported From:** Source site URL
- **Destination Site:** Where menu will be imported
- **Exported At:** When menu was exported

### Menu Items Mapping Table

Each row represents one menu item:

#### Column 1: Title
- Menu item title
- Indented with "—" for child items
- Shows hierarchy structure

#### Column 2: Type
- **Page:** WordPress page
- **Post:** WordPress post
- **Category:** Taxonomy term
- **Custom Link:** External or custom URL

#### Column 3: Auto Status
Shows matching status:

**✅ Matched**
- Green checkmark icon
- Shows matched item name and ID
- Example: "Matched: Sample Page (ID: 2)"

**⚠️ Not Found**
- Warning icon
- Will be converted to custom link
- Example: "Will be converted to custom link"

**🔗 Custom Link**
- Link icon
- Will be imported as-is
- Example: "Custom link - will be imported as-is"

#### Column 4: Map To
Dropdown to manually change mapping:
- **Keep as Custom Link:** Default for unmatched items
- **Posts:** List of all posts
- **Pages:** List of all pages
- **Categories:** List of all categories

#### Column 5: Remove
- Trash icon button
- Click to remove item from import
- Can undo removal

### Warning Messages

Preview may show warnings:
- ⚠️ Missing pages/posts/taxonomies
- ⚠️ Items that will be converted to custom links
- ⚠️ Theme location availability

---

## Manual Mapping

### When to Use Manual Mapping

Use manual mapping when:
- Auto-match found wrong item
- Want to map to different content
- Source content doesn't exist on destination
- Need to change item type

### How to Change Mapping

1. Find the item in the preview table
2. Click the **"Map To"** dropdown
3. Select desired content:
   - Choose from Pages, Posts, or Categories
   - Or keep as Custom Link
4. Selection is saved automatically

### Mapping Examples

#### Example 1: Wrong Auto-Match
- Auto-match: "About Us" → Matched to wrong page
- Solution: Use dropdown to select correct "About Us" page

#### Example 2: Missing Page
- Status: "Not Found" (page doesn't exist)
- Solution: Map to similar page or keep as custom link

#### Example 3: Change Type
- Original: Page
- Need: Custom Link with different URL
- Solution: Select "Keep as Custom Link" then edit URL after import

### Mapping Tips

- ✅ Preview updates immediately
- ✅ Can change multiple items
- ✅ Can override auto-matches
- ✅ Changes apply on import

---

## Removing Items

### Why Remove Items?

- Item not needed on destination site
- Item references content that doesn't exist
- Want to simplify menu structure
- Testing different menu configurations

### How to Remove

1. Find item in preview table
2. Click **trash icon** in Remove column
3. Confirm removal in modal
4. Item is grayed out with strikethrough

### Undo Removal

1. Click **undo icon** (green arrow)
2. Item is restored immediately
3. Can remove/undo multiple times

### What Happens When Removed?

- Item is excluded from import
- Child items become top-level (if parent removed)
- No orphaned items
- Clean menu structure maintained

---

## URL Normalization

### What is URL Normalization?

Automatic replacement of source site URLs with destination site URLs.

### When It Happens

- Importing menu from different site
- Source URL ≠ Destination URL
- Applies to custom links and internal links

### Example

**Source Site:** `https://staging.example.com`  
**Destination Site:** `https://example.com`

**Before:**
```
https://staging.example.com/about-us/
```

**After:**
```
https://example.com/about-us/
```

### What Gets Normalized

- ✅ Custom links pointing to source site
- ✅ Internal links (same domain)
- ❌ External links (different domain)
- ❌ Query strings and fragments (preserved)

### When It Doesn't Happen

- Source and destination URLs are the same
- Link is external (different domain)
- Link is relative path

---

## Best Practices

### Before Export

1. ✅ **Review menu structure** - Make sure it's correct
2. ✅ **Check all links** - Verify URLs work
3. ✅ **Test menu** - Ensure it displays correctly
4. ✅ **Note theme locations** - Remember which locations are assigned

### Before Import

1. ✅ **Backup destination site** - Always backup first
2. ✅ **Review preview carefully** - Check all matches
3. ✅ **Adjust mappings** - Fix any incorrect matches
4. ✅ **Remove unwanted items** - Clean up before import
5. ✅ **Check theme locations** - Verify locations exist

### After Import

1. ✅ **Test menu** - View on frontend
2. ✅ **Check all links** - Verify URLs work
3. ✅ **Test navigation** - Click through menu items
4. ✅ **Assign to location** - If not done during import
5. ✅ **Save menu** - Ensure changes are saved

### File Management

- ✅ **Name files clearly** - Include date/site name
- ✅ **Keep backups** - Don't delete exported files
- ✅ **Version control** - Track menu versions
- ✅ **Document changes** - Note what changed and why

---

## Common Workflows

### Workflow 1: Staging to Production

```
1. Export menu from production site
2. Save JSON file
3. Import to staging site
4. Test and make changes
5. Export updated menu from staging
6. Import back to production
```

### Workflow 2: Site Migration

```
1. Export all menus from old site
2. Save JSON files
3. Set up new site
4. Import menus one by one
5. Review and adjust mappings
6. Assign to theme locations
```

### Workflow 3: Menu Backup

```
1. Export menu before making changes
2. Save JSON file with date
3. Make changes to menu
4. If something breaks, import backup
5. Adjust as needed
```

### Workflow 4: Multi-Site Consistency

```
1. Create menu on Site A
2. Export menu
3. Import to Site B, C, D
4. Adjust mappings for each site
5. All sites have consistent structure
```

### Workflow 5: Testing Different Configurations

```
1. Export current menu
2. Import with different name
3. Make changes to test menu
4. Compare both menus
5. Export preferred version
6. Replace original if needed
```

---

## Troubleshooting

### Export Issues

**Problem:** Export button doesn't work  
**Solution:** Check browser console, refresh page, verify menu selected

**Problem:** File doesn't download  
**Solution:** Check browser download settings, try different browser

### Import Issues

**Problem:** Preview doesn't show  
**Solution:** Check JSON file is valid, check browser console

**Problem:** Import fails  
**Solution:** Review error message, check file size, verify permissions

**Problem:** Items don't match correctly  
**Solution:** Use manual mapping to fix matches

### Mapping Issues

**Problem:** Can't find item in dropdown  
**Solution:** Item may not exist, use custom link instead

**Problem:** Wrong item matched  
**Solution:** Use dropdown to select correct item

---

**Next:** [Developer Guide](./05-developer-guide.md)

