# Changelog

All notable changes to MenuPilot will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.5] - 2026-03-15

### Fixed
- Backup tab now correctly shows only backups for the currently selected menu (previously showed all menus' backups combined)
- Backup stats bar now reflects the count for the current menu only, eliminating misleading totals like "15 of 5"
- Restore, delete, and delete-all backup actions now use the plugin's confirm modal instead of the browser's native `confirm()` dialog

### Improved
- Backup list batch-fetches user display names in a single query instead of one query per row (eliminates N+1 database queries)
- Deploy workflow now runs PHPCS, PHPStan, and ESLint + Stylelint checks as gates before deploying to WordPress.org
- Deploy workflow sends a Slack notification on success, failure, or skipped deploy

## [1.0.4] - 2026-03-14

### Changed
- Backups are now stored in a dedicated `wp_menupilot_backups` database table instead of the `wp_options` table, eliminating serialized blob bloat and improving query performance
- Auto-backup on menu save now captures the state **after** WordPress finishes processing all item changes (adds, updates, deletes), so the backup correctly reflects what was saved
- Removed unused frontend stylesheet (`main.css`) and script (`main.js`) — MenuPilot is admin-only and ships no frontend assets

### Fixed
- Restoring an older backup after restoring a newer one now correctly removes items that were deleted between the two saves

### Migration
- Existing backups stored in `wp_options` are automatically migrated to the new database table on the first admin page load after updating; the old option is deleted after migration

## [1.0.0] - 2026-01-23

### Added
- Initial plugin structure
- Settings framework
- Admin interface
- Asset pipeline
- Testing infrastructure
- Documentation

[Unreleased]: https://github.com/mayankmajeji/menupilot/compare/v1.0.5...HEAD
[1.0.5]: https://github.com/mayankmajeji/menupilot/compare/v1.0.4...v1.0.5
[1.0.4]: https://github.com/mayankmajeji/menupilot/compare/v1.0.0...v1.0.4
[1.0.0]: https://github.com/mayankmajeji/menupilot/releases/tag/v1.0.0
