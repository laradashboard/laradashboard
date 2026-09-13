# Squartup module

Isolated `nwidart/laravel-modules` host for the Squartup website migration.

Folder: `modules/squartup`  
Namespace: `Modules\Squartup`  
View namespace: `squartup::`

## Phase 1

This module is a loadable foundation only.

- Health route: [`/squartup`](/squartup) (`squartup.health`)
- Views resolve from `resources/views` as `squartup::*`
- Assets stay module-local (`vite.config.js` → `build-squartup`) with Tailwind prefix `squartup`
- No `/admin` routes, auth routes, catch-all `/{slug}`, or database migrations

See `docs/squartup-migration-phase-0.md` and `docs/squartup-migration-phase-1.md` in the Lara Dashboard root.

## Do not

- Merge Squartup Bootstrap, jQuery, or `restyle.css` into the core Vite/Tailwind build
- Register Filament
- Copy Breeze auth or the Squartup `User` model
- Add `/{slug}`

## Tests

```bash
php artisan test modules/squartup/tests/Feature/SquartupModuleHealthTest.php
```
