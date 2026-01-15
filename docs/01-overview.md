# MenuPilot Overview

## What is MenuPilot?

MenuPilot is a WordPress plugin that allows you to safely import and export WordPress menus between sites with preview, intelligent matching, and zero surprises.

## Why MenuPilot?

### The Problem

Moving menus between WordPress sites (staging → production, site migration, etc.) is traditionally difficult:

- Manual recreation is time-consuming
- Copy-paste doesn't preserve structure
- No way to preview what will happen
- Missing pages/posts break menus
- No control over mapping

### The Solution

MenuPilot provides:

- ✅ **One-click export** to JSON
- ✅ **Import preview** before committing
- ✅ **Intelligent auto-matching** of pages/posts/taxonomies
- ✅ **Manual mapping override** for full control
- ✅ **URL normalization** for site migrations
- ✅ **Remove items** you don't want to import
- ✅ **Zero surprises** - see everything before import

## Key Benefits

### 🎯 For Site Migrations

- Move menus from staging to production seamlessly
- Automatically update URLs for new domain
- Preview and adjust before importing

### 🔄 For Development Workflows

- Export menus from production
- Import to staging for testing
- Make changes and re-export
- No manual recreation needed

### 🛡️ For Safety

- Preview exactly what will be imported
- See which items matched and which didn't
- Remove unwanted items before import
- Manual override for perfect control

### ⚡ For Speed

- Export entire menu structure in seconds
- Import with all metadata preserved
- No manual item-by-item recreation

## Use Cases

### 1. Staging to Production

```
Production Site → Export Menu → Import to Staging → Test → Export → Import to Production
```

### 2. Site Migration

```
Old Site → Export Menu → Import to New Site (URLs auto-updated)
```

### 3. Menu Backup

```
Current Menu → Export → Save JSON → Restore Later if Needed
```

### 4. Multi-Site Consistency

```
Site A → Export Menu → Import to Site B, C, D (with adjustments)
```

## What Makes MenuPilot Different?

### 🎨 Preview Before Import

Unlike other plugins, MenuPilot shows you **exactly** what will happen:

- Which items matched automatically
- Which items will be converted to custom links
- What the final menu structure will look like

### 🧠 Intelligent Matching

Automatically matches menu items by:

- Post type + slug
- Taxonomy + slug
- Custom link URL normalization

### 🎛️ Manual Control

Full control over every item:

- Override auto-matches
- Map to different pages/posts
- Remove items you don't want
- Keep as custom links

### 🔒 Safe by Default

- Never overwrites existing menus
- Creates new menus every time
- Shows warnings for missing items
- Validates before import

## Technical Highlights

- **REST API** based (secure, admin-only)
- **WordPress hooks** for extensibility
- **PSR-4 autoloading** (modern PHP)
- **Native WordPress UI** (familiar, no learning curve)
- **Zero database modifications** (uses WordPress APIs)

## Who Is MenuPilot For?

### 👨‍💼 Site Owners

- Need to move menus between sites
- Want to backup menus
- Need to preview before importing

### 👨‍💻 Developers

- Building client sites
- Managing staging/production workflows
- Need extensible menu management

### 🏢 Agencies

- Managing multiple client sites
- Standardizing menu structures
- Quick site migrations

## What's NOT in V1?

MenuPilot V1 focuses on **safe import/export**. These features are planned for future versions:

- ❌ Automatic backups on save
- ❌ Menu duplication
- ❌ Merge/replace existing menus
- ❌ Scheduled backups
- ❌ WP-CLI commands
- ❌ Mega menu support
- ❌ Multisite sync

_See [Roadmap](./08-roadmap.md) for future features_

---

**Next:** [Features](./02-features.md)
