# Changelog

All notable changes to `filament-advanced-import` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-04-22

### Changed

- Bump `spatie/laravel-package-tools` to `^1.92` for Laravel 13 support (`illuminate/contracts ^13.0`)
- Bump dev dependency `pestphp/pest` to `^3.0|^4.0` (Pest 2 is EOL and incompatible with Laravel 13 / PHPUnit 11+)

### Compatibility

- Verified against Laravel 13.6, Filament 5.6, `maatwebsite/excel` 3.1.68 and `orchestra/testbench` 11.1
- Still supports Laravel 11/12 and Filament 4 via existing OR constraints

## [1.0.0] - 2025-01-01

### Added

- Initial release
- `BaseImportPage` - Abstract base page for Excel imports with preview and processing
- `ImportProcessor` interface - Contract for custom import processors
- `ProcessesImport` trait - Base trait with batch processing logic
- `Importacao` model - Audit model for tracking imports
- `ImportacaoDetalhe` model - Detail model for individual record tracking
- `ImportacaoResource` - Pre-built Filament resource for viewing import history
- `DetalhesRelationManager` - Relation manager for viewing import details
- Artisan commands:
  - `import:install` - Install package
  - `import:page` - Generate import page
  - `import:processor` - Generate import processor
  - `import:publish` - Publish assets
- Support classes:
  - `ExcelReader` - Wrapper for Maatwebsite Excel
  - `NumberParser` - Parse numbers from various formats
  - `ImportConfig` - Configuration helper
- Concerns:
  - `HasImportActions` - Header actions (upload, process, toggle)
  - `HasImportTable` - Table configuration for preview/results
  - `HasExcelParsing` - Excel reading and parsing helpers
  - `HasImportNotifications` - Notification methods
- English and Portuguese translations
- Stubs for code generation
- Configuration file with customizable options
- Database migrations for audit tables

### Dependencies

- PHP 8.2+
- Laravel 11+
- Filament 4.0+
- Maatwebsite Excel 3.1+
- Spatie Laravel Package Tools 1.9+

## [0.1.0] - 2024-12-XX

### Added

- Initial development version
- Core functionality for Excel import with preview
