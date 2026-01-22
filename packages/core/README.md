# acme/feature-toggles-core

Framework-agnostic feature toggles with:
- DB overrides
- environment scoping (dev/staging/prod)
- optional PSR-16 caching + PSR-3 logging

## Schema

See `resources/sql/schema.mysql.sql`.

## Usage

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

if ($features->isEnabled('checkout.new_flow')) { ... }
```
