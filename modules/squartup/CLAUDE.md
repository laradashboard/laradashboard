# CLAUDE.md — Squartup Module

**Namespace**: `Modules\Squartup\`  
**Alias**: `squartup`  
**Path**: `modules/squartup/`

This module is the isolated host for migrating the private Squartup website into Lara Dashboard.

Phase 1 is foundation-only. Do not migrate public pages, Filament, Breeze, products, checkout, blog, or catch-all `/{slug}` unless a later phase explicitly asks for that work.

## Rules

- Lara Dashboard owns `/admin`, auth, users, roles, permissions, CMS posts/pages, media, settings, and menus.
- Module views use the `squartup::` namespace.
- Assets compile through this module's `vite.config.js` (`build-squartup`) with Tailwind prefix `squartup`. Never merge Bootstrap, jQuery, or `restyle.css` into the core Vite build.
- Future tables should be prefixed `squartup_*` unless an approved MERGE into a core table is documented.

## Commands

```bash
php artisan test modules/squartup/tests/Feature/SquartupModuleHealthTest.php
php artisan module:enable Squartup
php artisan route:list --path=squartup
vendor/bin/pint --dirty
```

See `docs/squartup-migration-phase-0.md` and `docs/squartup-migration-phase-1.md`.
