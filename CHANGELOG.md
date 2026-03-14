# Changelog

All notable changes to MenuPilot will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://mayankmajeji.com/menupilot/compare/v1.0.4...HEAD
[1.0.4]: https://mayankmajeji.com/menupilot/compare/v1.0.0...v1.0.4
[1.0.0]: https://mayankmajeji.com/menupilot/releases/tag/v1.0.0
