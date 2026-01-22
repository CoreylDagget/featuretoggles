# acme/feature-toggles-laravel

Laravel integration for `acme/feature-toggles-core`.

## Install (path repo)

In your project:

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

Publish:

```bash
php artisan vendor:publish --tag=feature-toggles-config
php artisan vendor:publish --tag=feature-toggles-migrations
php artisan migrate
```

Usage:

```php
if (\Feature::isEnabled('checkout.new_flow')) { ... }
```
