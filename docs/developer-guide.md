# Developer Guide

This guide is for contributors working on the Feature Toggles monorepo.

## Repository layout

```
packages/
  core/     Framework-agnostic feature toggle implementation
  laravel/  Laravel bridge and integration helpers
  typo3/    TYPO3 extension skeleton
```

## Local setup

1. **Clone and install dependencies**
   - Each package is a standalone Composer package. Install dependencies in the
     package you are working on.

```bash
cd packages/core
composer install
```

2. **Laravel package (optional)**
   - Use the Laravel bridge by adding `packages/core` and `packages/laravel` as
     path repositories in your Laravel app and requiring
     `acme/feature-toggles-laravel`.

3. **TYPO3 package (optional)**
   - Install via Composer and run the DB compare to apply `ext_tables.sql`.

## Development workflow

- **Core first**: implement feature behavior in `packages/core`, then wire or
  expose it in framework bridges.
- **Backwards compatibility**: avoid breaking public APIs in the core package.
- **Environment parity**: feature defaults live in code; DB overrides provide
  environment-specific updates.

## Key components (core)

- `FeatureManager`: resolves feature state per environment with optional cache.
- `ToggleRepositoryInterface`: persistence contract for feature definitions.
- `DefaultResolverInterface`: resolves defaults from code (e.g., array-backed).
- `PdoToggleRepository`: PDO-backed repository implementation.

## Database schema

- The core package ships the SQL schema at `packages/core/resources/sql/schema.mysql.sql`.
- Laravel publishes migrations for the same schema.

## Tests

There are no automated tests in this repository yet. If you add tests, prefer
co-locating them in the package they cover (e.g., `packages/core/tests`).

## Releasing

- Bump versions in each package `composer.json` as needed.
- Ensure all bridges depend on compatible versions of the core package.
- Tag releases per package if publishing to a registry.
