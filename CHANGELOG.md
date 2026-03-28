# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2026-03-28

### Fixed
- Fix crash on install caused by stale metadata files in release archive ([#1](https://github.com/sashd3/ak-language-switcher/issues/1))

## [1.0.0] - 2026-03-04

### Added
- Language switcher dropdown in the Nextcloud header bar
- Support for authenticated users (saves language preference via API)
- Support for public/share pages (cookie-based language override)
- Cookie-to-Accept-Language override for server-side language detection
- L10N Factory cache reset via Reflection for reliable language switching
- Admin settings panel with:
  - Icon style selection (6 different icons)
  - Icon size and stroke width configuration
  - Icon color customization
  - Allowed languages filter (checkbox list)
  - Save button with visual feedback
  - Help dialog with usage instructions
- Translations for 100+ languages
- Compatible with Nextcloud 27-33

[1.0.1]: https://github.com/sashd3/ak-language-switcher/releases/tag/v1.0.1
[1.0.0]: https://github.com/sashd3/ak-language-switcher/releases/tag/v1.0.0
