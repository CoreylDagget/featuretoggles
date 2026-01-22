# Feature Toggle Package Types

This monorepo ships three complementary package types. Each type targets a specific
framework integration level while sharing the same underlying feature-toggle
behavior (defaults, environment scoping, and DB overrides).

## 1) Core (framework-agnostic)

**Package:** `packages/core` (`acme/feature-toggles-core`)

**When to use it**
- You want a plain PHP implementation without framework bindings.
- You plan to wire the core into a custom framework or service container.

**What it provides**
- `FeatureManager` to evaluate toggles with environment scoping.
- Repository contract and a PDO-backed implementation for persistence.
- Default resolver (array-backed), PSR-16 cache support, and optional PSR-3 logging.

**Quick usage**
```php
$repo = new PdoToggleRepository($pdo);
$defaults = new ArrayDefaultResolver([
  'checkout.new_flow' => false,
]);

$features = new FeatureManager(
  repository: $repo,
  defaults: $defaults,
  environment: 'prod'
);

if ($features->isEnabled('checkout.new_flow')) { /* ... */ }
```

## 2) Laravel bridge

**Package:** `packages/laravel` (`acme/feature-toggles-laravel`)

**When to use it**
- You are working inside a Laravel application.
- You want the framework-friendly service provider, config publishing,
  and migrations baked in.

**What it provides**
- Laravel service provider that wires the core into the container.
- Config and database migrations for a standard Laravel setup.
- Convenience facade usage through `\Feature`.

**Quick usage**
```php
if (\Feature::isEnabled('checkout.new_flow')) { /* ... */ }
```

## 3) TYPO3 bridge (skeleton)

**Package:** `packages/typo3` (`acme/feature-toggles-typo3`)

**When to use it**
- You are building a TYPO3 extension and want to integrate feature toggles.
- You need a starting point for TYPO3 DI integration and configuration.

**What it provides**
- TYPO3 extension skeleton that wires the core into TYPO3 DI.
- SQL schema via `ext_tables.sql`.
- Extension configuration hooks (to be adapted to project conventions).

**Quick usage**
- Install via Composer and run the TYPO3 DB compare for `ext_tables.sql`.
- Configure defaults/environment in TYPO3 extension configuration.

## How to choose

- **Use Core** when you need a lightweight, framework-agnostic implementation.
- **Use Laravel** when you need a batteries-included Laravel integration.
- **Use TYPO3** when you need a TYPO3 integration starting point and are ready
  to tailor configuration to your TYPO3 project conventions.
