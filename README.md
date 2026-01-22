# Feature Toggles (Monorepo)

This zip contains a monorepo layout with 3 composer packages:

- `packages/core` — framework-agnostic core
- `packages/laravel` — Laravel bridge
- `packages/typo3` — TYPO3 extension bridge (skeleton)

## Quick start (Core)

```bash
cd packages/core
composer install
```

## Quick start (Laravel)

Add to your main project `composer.json`:

```json
{
  "repositories": [
    { "type": "path", "url": "path/to/feature-toggles/packages/core" },
    { "type": "path", "url": "path/to/feature-toggles/packages/laravel" }
  ],
  "require": {
    "acme/feature-toggles-laravel": "*"
  }
}
```

Publish config/migrations:

```bash
php artisan vendor:publish --tag=feature-toggles-config
php artisan vendor:publish --tag=feature-toggles-migrations
php artisan migrate
```

## Notes

- Defaults live in config/code; DB provides environment-specific overrides.
- Snapshot caching per environment is enabled by default (PSR-16).
