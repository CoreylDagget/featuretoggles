# Feature Toggles Monorepo — Presentation Doc

## 1. Purpose

Provide a shared, framework-agnostic feature toggle system with framework
bridges for Laravel and TYPO3. This allows teams to maintain consistent feature
behavior while integrating with their preferred application stack.

## 2. Elevator pitch

A single core implementation, three delivery formats:
- **Core** for pure PHP or custom frameworks
- **Laravel** for out-of-the-box integration
- **TYPO3** as an extension skeleton

## 3. Key capabilities

- **Environment scoping** (dev/staging/prod)
- **Defaults in code** with **DB overrides** for runtime control
- **Optional caching** (PSR-16) and **logging** (PSR-3)

## 4. Architecture overview

```
                 +---------------------+
                 |  FeatureManager     |
                 |  (core)             |
                 +----------+----------+
                            |
                +-----------+-----------+
                |                       |
     +----------+----------+  +---------+---------+
     | DefaultResolver     |  | ToggleRepository  |
     | (code defaults)     |  | (DB overrides)    |
     +---------------------+  +-------------------+
                |
   +------------+------------+
   | Framework Bridges       |
   | - Laravel               |
   | - TYPO3                 |
   +-------------------------+
```

## 5. Package breakdown

| Package | Purpose | Typical use |
| --- | --- | --- |
| `packages/core` | Framework-agnostic implementation | CLI tools, custom apps, other frameworks |
| `packages/laravel` | Laravel bridge | Laravel apps needing config + migrations |
| `packages/typo3` | TYPO3 extension skeleton | TYPO3 integrations needing DI wiring |

## 6. Demo flow (suggested)

1. Introduce the three packages and when to use each.
2. Show a simple toggle default in core.
3. Show DB override toggling the same flag at runtime.
4. Show the Laravel facade usage (`\Feature::isEnabled`).

## 7. Adoption guidance

- Start with **core** to validate business rules and toggle behavior.
- Add the **Laravel** or **TYPO3** bridge depending on your runtime.
- Keep defaults in code and use DB overrides for environment-specific changes.

## 8. Future roadmap ideas

- Additional framework bridges (Symfony, Slim).
- Admin UI for toggle management.
- Enhanced evaluation rules (percent rollout, user targeting).
