# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] - 2026-03-19

### Added
- **Media Library abilities**: List, read details, upload (from URL), update metadata, delete, set featured image, bulk update metadata
- Media library section in the settings page with 7 new toggles
- Security-first defaults: read-only abilities active by default, writing abilities disabled

### Changed
- `gw_mcp_is_ability_active()` now uses a whitelist for default-active abilities instead of enabling everything
- Plugin header updated with complete metadata (Author, License, Plugin URI, etc.)

## [1.2.0] - 2026-03-18

### Added
- Custom Post Types abilities: list CPTs, read CPT posts, create CPT posts
- Metadata abilities: read post/page metadata with optional hidden fields
- Taxonomy abilities: read taxonomies, read terms
- Page abilities: read pages, create pages, duplicate pages (with metadata)
- Post abilities: create posts, update posts/pages/CPTs
- Settings page with modern toggle UI for enabling/disabling individual abilities
- Ability registration system via `GW_MCP_Registrator` class

## [1.1.0] - 2026-03-17

### Added
- Read post details ability
- Basic settings page

## [1.0.0] - 2026-03-16

### Added
- Initial release
- Read posts ability
