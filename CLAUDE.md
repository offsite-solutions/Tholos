# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Rules

- **Planning docs must be placed in `Base/docs/plans/`** -- all design specs, implementation plans, and planning documents go in this directory. Never place them elsewhere.

## Project Overview

Tholos is a PHP GUI framework built on top of the **Eisodos** framework (`offsite-solutions/eisodos`). It provides a component-based architecture for building server-rendered HTML applications with database-driven component definitions.

- **Package**: `offsite-solutions/tholos` (Composer library)
- **PHP**: >= 8.4
- **Autoloading**: PSR-0 under `src/Tholos/`
- **Namespace**: `Tholos\`

## Commands

```bash
# Install production dependencies
composer install --no-interaction --prefer-dist --no-dev --no-ansi --optimize-autoloader

# Update production dependencies
composer update --no-interaction --prefer-dist --no-dev --no-ansi --optimize-autoloader

# Update dev dependencies (uses local symlink to Eisodos at ../../_eisodos/Base)
COMPOSER=composer.dev.json composer update --no-interaction --prefer-dist --no-dev --no-ansi --optimize-autoloader
```

There is no test suite or linter configured in this repository.

## Development Setup

Two composer configs exist:
- `composer.json` — production; pulls Eisodos from Packagist (`@dev`)
- `composer.dev.json` — development; symlinks Eisodos from `../../_eisodos/Base` via a `path` repository

## Architecture

### Bootstrap Chain

`Tholos` (singleton) → initializes `TholosApplication` and `TholosLogger`. Access the app via `Tholos::$app` and logger via `Tholos::$logger`.

### Request Flow

1. **TRoute** — determines the current route from the `tholos_route` parameter; optionally runs an `InitSessionProvider` data provider
2. **TAction** — determines the action within a route from `tholos_action` (default: `index`); renders content into a TPage layout
3. **Render tree** — `TholosApplication::render()` walks child components, calling each component's `render()` method which returns HTML

### Component System

All UI components extend **TComponent**, which holds:
- **Properties** — typed key-value pairs (`STRING`, `NUMBER`, `BOOLEAN`, `JSON`, `ARRAY`, `TEXT`, `TEMPLATE`, `PARAMETER`, `COMPONENT`)
- **Events** — named handlers (`GUI` or `PHP` type) that can reference methods on other components
- Component definitions are loaded from `.tcd` files (compiled by Tholos Builder Compiler)

The authoritative reference for all component types — including their properties, events, methods, inheritance, and allowed parent types — is `docs/Tholos_Component_Types.md` (sourced from `docs/Tholos_Component_Types.html`). Always consult this file when you need component-level details.

Key component classes:
- `TPage` / `TTemplate` — layout wrappers that delegate to Eisodos template engine
- `TGrid` / `TGridColumn` / `TGridFilter` / `TGridRowActions` — data grid with filtering, sorting, and Excel export (via PhpSpreadsheet)
- `TDataProvider` / `TStoredProcedure` / `TQuery` — data layer; propagates results to `TDBField` children
- `TFormControl` / `TDBField` / `TDBParam` — form binding and database field mapping
- `TWizard` / `TTabs` / `TPartial` / `TIterator` — composite UI components
- `TRoleManager` — role-based access control checked during rendering
- `TholosCallback` — template callback functions (`_eq`, `_neq`, `_case`, etc.) used in Eisodos templates
- `TPDFPage` — PDF rendering via mPDF
- `TAPIPost` — external API integration
- `TMap` / `TMapSource` — map components
- `TPAdES` — PAdES digital signature support

### Eisodos Dependency

Tholos relies heavily on Eisodos for:
- Template engine (`Eisodos::$templateEngine`)
- Parameter handling (`Eisodos::$parameterHandler`)
- Database connections and utilities
- Session management

### Frontend Assets

- `assets/js/TholosApplication.js` — client-side application logic
- `assets/js/TGrid.js` — client-side grid component
- `assets/css/` — grid and help stylesheets (includes SASS sources)
- `assets/templates/tholos/` — Eisodos template files for component rendering

### Naming Conventions

- All PHP classes are prefixed with `T` (e.g., `TComponent`, `TGrid`, `TAction`)
- Constructor parameters use trailing underscore: `$componentType_`, `$id_`, `$parent_id_`
- Component property/event keys are lowercased internally
- Component definitions use compact JSON keys: `n` (name), `t` (type), `v` (value), `c` (component_id), `d` (nodata)