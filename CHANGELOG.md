# Changelog

All notable changes to `filament-advanced-import` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
