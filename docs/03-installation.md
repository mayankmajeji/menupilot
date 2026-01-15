# Installation & Setup

## Requirements

### Minimum Requirements

- **WordPress:** 5.8 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.6 or higher (or MariaDB 10.0+)

### Recommended

- **WordPress:** 6.0 or higher
- **PHP:** 8.0 or higher
- **Memory Limit:** 128MB or higher

### Server Requirements

- PHP extensions: `json`, `mbstring`
- Write permissions for plugin directory
- REST API enabled (default in WordPress)

## Installation Methods

### Method 1: WordPress Admin (Recommended)

1. **Log in** to your WordPress admin dashboard
2. Navigate to **Plugins → Add New**
3. Search for **"MenuPilot"**
4. Click **"Install Now"**
5. Click **"Activate"**

### Method 2: Manual Upload

1. **Download** MenuPilot ZIP file
2. **Log in** to WordPress admin
3. Navigate to **Plugins → Add New**
4. Click **"Upload Plugin"**
5. Choose the ZIP file
6. Click **"Install Now"**
7. Click **"Activate Plugin"**

### Method 3: FTP Upload

1. **Extract** the ZIP file
2. **Upload** the `menupilot` folder to `/wp-content/plugins/`
3. **Log in** to WordPress admin
4. Navigate to **Plugins**
5. Find **MenuPilot** and click **"Activate"**

## Post-Installation

### Verify Installation

After activation, you should see:

1. **New Menu Item:** "MenuPilot" in WordPress admin sidebar
2. **Icon:** Menu icon (dashicons-menu-alt)
3. **Submenus:** Settings, Tools, Help

### First Steps

1. **Navigate** to `MenuPilot → Settings`
2. **Test Export:** Export an existing menu
3. **Test Import:** Import the exported menu (with different name)

## Configuration

### No Configuration Required

MenuPilot works out of the box with default settings. However, you can customize:

#### Export Settings (Tools → Export Settings)

- Default export format (JSON)
- Include/exclude metadata options
- Export file naming convention

#### Import Settings (Tools → Import Settings)

- Default import behavior
- Auto-match sensitivity
- URL normalization rules

### Permissions

MenuPilot requires:

- **Capability:** `manage_options` (Administrator)
- **REST API:** Must be enabled (default)
- **File Upload:** For importing JSON files

## Troubleshooting

### Plugin Not Appearing

**Problem:** MenuPilot menu doesn't appear in admin

**Solutions:**

1. Check user has `manage_options` capability
2. Clear browser cache
3. Check for plugin conflicts
4. Verify plugin is activated

### Export Not Working

**Problem:** Export button does nothing

**Solutions:**

1. Check browser console for JavaScript errors
2. Verify REST API is enabled: `Settings → Permalinks → Save`
3. Check user permissions
4. Verify nonce is valid (refresh page)

### Import Fails

**Problem:** Import shows error or doesn't work

**Solutions:**

1. Check JSON file is valid (use JSON validator)
2. Verify file size isn't too large
3. Check PHP `upload_max_filesize` setting
4. Check REST API is accessible
5. Review error message for specific issue

### REST API Issues

**Problem:** "Failed to fetch" or 401/403 errors

**Solutions:**

1. Verify REST API: Visit `/wp-json/`
2. Check authentication (must be logged in)
3. Verify nonce in request headers
4. Check for security plugins blocking REST API
5. Review `.htaccess` rules

### Preview Not Showing

**Problem:** Import preview modal doesn't appear

**Solutions:**

1. Check browser console for errors
2. Verify JavaScript is enabled
3. Check for jQuery conflicts
4. Verify file was uploaded successfully
5. Check JSON structure is valid

## Uninstallation

### Standard Uninstallation

1. Navigate to **Plugins**
2. Find **MenuPilot**
3. Click **"Deactivate"**
4. Click **"Delete"**

### Data Cleanup

MenuPilot does **not** modify database structure. Uninstallation:

- ✅ Removes plugin files
- ✅ Removes plugin options (if any)
- ✅ Does NOT delete menus (WordPress core data)
- ✅ Does NOT delete exported JSON files

### Manual Cleanup (If Needed)

If you want to remove all MenuPilot data:

1. **Delete Options:**

   ```sql
   DELETE FROM wp_options WHERE option_name LIKE 'menupilot_%';
   ```

2. **Delete Transients:**
   ```sql
   DELETE FROM wp_options WHERE option_name LIKE '_transient_menupilot_%';
   DELETE FROM wp_options WHERE option_name LIKE '_transient_timeout_menupilot_%';
   ```

**Note:** This is usually not necessary. Standard uninstall is sufficient.

## Updating

### Automatic Updates

If installed from WordPress.org:

1. WordPress will notify you of updates
2. Click **"Update Now"** when available
3. Plugin updates automatically

### Manual Updates

1. **Backup** your site (always recommended)
2. **Deactivate** MenuPilot
3. **Delete** old plugin files
4. **Upload** new version
5. **Activate** MenuPilot

### Update Notes

- ✅ Settings are preserved
- ✅ No database migrations needed
- ✅ Backward compatible with existing exports
- ⚠️ Always backup before major updates

## Multi-Site Installation

### Network Activation

MenuPilot can be network-activated:

1. Navigate to **Network Admin → Plugins**
2. Find **MenuPilot**
3. Click **"Network Activate"**

**Note:** V1 does not include multisite-specific features. Each site manages menus independently.

## Development Installation

### From Git

```bash
cd wp-content/plugins
git clone https://github.com/yourusername/menupilot.git
cd menupilot
composer install
npm install
npm run build
```

### Development Dependencies

- **Composer:** For PHP dependencies (if any)
- **npm:** For JavaScript/CSS build tools
- **Node.js:** 14+ for build tools

---

**Next:** [User Guide](./04-user-guide.md)
