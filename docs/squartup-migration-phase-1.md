# Squartup → Laradashboard Migration — Phase 1 Foundation

**Date:** 14 September 2026
**Branch:** `feature/squartup-module`
**Success criterion:** Laradashboard continues to work as before, and an isolated Squartup module loads without route or auth conflicts.

Phase 1 stops here. No domain, public-site, admin, blog, or checkout migration was started.

---

## Completed

- Host and Squartup compatibility audit written to `docs/squartup-migration-phase-0.md`.
- Native module created with `php artisan module:make Squartup --no-interaction`.
- nwidart v13 placed the module at `modules/squartup` (lowercase folder, namespace `Modules\Squartup`).
- Module is enabled (`Squartup: true` in `modules_statuses.json`).
- Service provider loads module routes, views (`squartup::`), config (`config('squartup')`), and migrations path (empty).
- Isolated asset strategy is in place: module `vite.config.js` → `build-squartup`, Tailwind `@import "tailwindcss" prefix(squartup)`.
- Single public health route: `GET /squartup` → `squartup.health` → `squartup::health`.
- Default `module:make` admin routes (`/admin/squartup`) were **removed** so Phase 1 does not add `/admin` routes.
- Admin menu / permission hooks are not registered.
- API file is empty (no `/api/user`, no `apiResource`).
- Feature tests cover load, reserved-route preservation, and the absence of a catch-all.
- Root `.gitignore` now tracks `modules/squartup` (same exception pattern as `Laradashboard`).

`composer dump-autoload` was started by `module:make` and timed out after 60s. That is not required for this host: `bootstrap/modules.php` registers the module PSR-4 map at boot. No `composer update` / `npm update` was run.

---

## Existing Laradashboard functionality discovered

Reuse these later instead of recreating them inside Squartup:

- Users, roles, permissions (Spatie)
- Login, logout, register, password reset, profile
- `/admin` dashboard and core admin CRUD
- CMS posts (`post`) and pages (`page`)
- Taxonomies `category` and `tag`
- Media library
- Settings and menus
- Custom Forms module
- CRM module (`crm_contacts`, deals, tickets, CRM products)
- Translations, email templates, themes, module marketplace
- Host already includes Laravel Cashier (evaluate separately from Squartup Stripe checkout)

---

## Conflicts discovered

Documented in Phase 0. The ones that shaped Phase 1:

| Conflict | Phase 1 handling |
| --- | --- |
| Both apps use `/admin` | No Squartup `/admin` routes |
| Auth / users / `/profile` / `/dashboard` | Not migrated |
| Catch-all `/{slug}` | Not registered |
| `/docs`, `/api/user`, sitemap, robots | Not registered |
| Tailwind 3 + Bootstrap/jQuery vs Tailwind 4 admin | Isolated module Vite only |
| Filament resources | Left in the Squartup app; not installed here |
| `laradashboard` theme locally disabled | Pre-existing working-tree change; preserved |

Runtime check after Phase 1 (`php artisan route:list` + HTTP against `http://laradashboard.test`):

| Path | Result |
| --- | --- |
| `/squartup` | 200, module health view |
| `/admin` | 302 → `/admin/login` → `/login` (core admin intact) |
| `/login` | 200 |
| `/register` | 200 |
| `/profile/edit` | 302 → `/login` |
| `/admin/squartup` | 404 |
| `/dashboard` | 404 |
| Catch-all `/{slug}` from Squartup | None |

The only `{slug}` route still present is the pre-existing CustomForm `forms/{slug}`.

---

## Files created

### Documentation

- `docs/squartup-migration-phase-0.md`
- `docs/squartup-migration-phase-1.md`

### Module (`modules/squartup/`)

Created by `module:make` and then adjusted for Phase 1:

- `module.json`
- `composer.json`
- `package.json`
- `vite.config.js`
- `description.md`
- `README.md`
- `CLAUDE.md`
- `.gitignore`
- `config/config.php`
- `routes/web.php`
- `routes/api.php`
- `app/Providers/SquartupServiceProvider.php`
- `app/Providers/RouteServiceProvider.php`
- `app/Providers/EventServiceProvider.php`
- `app/Providers/LivewireServiceProvider.php`
- `app/Http/Controllers/SquartupController.php`
- `app/Services/ModuleService.php`
- `app/Services/MenuService.php`
- `app/Livewire/Admin/Dashboard.php` (scaffold only; **not routed**)
- `database/seeders/SquartupDatabaseSeeder.php` (empty scaffold; not run)
- `resources/views/health.blade.php`
- `resources/views/index.blade.php` (unused scaffold)
- `resources/views/layouts/master.blade.php` (unused scaffold)
- `resources/views/layouts/admin.blade.php` (unused scaffold)
- `resources/views/livewire/admin/dashboard.blade.php` (unused scaffold)
- `resources/assets/css/app.css`
- `resources/assets/js/app.js`
- `resources/assets/sass/app.scss`
- `tests/Feature/SquartupModuleHealthTest.php`
- assorted `.gitkeep` files under `app/`, `config/`, `database/`, `resources/`, `routes/`, `tests/`

---

## Files modified

| File | Why |
| --- | --- |
| `.gitignore` | Track `modules/squartup` so the foundation can be committed |
| `modules_statuses.json` | `Squartup: true` added by `module:make` |

`modules_statuses.json` also still contains the **pre-existing** local change `laradashboard: true` → `false`. That disable was already in the working tree before this task and was not reverted.

---

## Files deleted

None.

---

## Deferred work

- Public pages (home, about, services, enterprise, contact, team)
- Public routes (including `/blog`, `/products`, `/careers`, `/case-studies`)
- SEO (`config/seo.php`, sitemap, robots)
- Models and migrations
- Products, commerce, checkout, Stripe
- Blog and categories (MERGE vs prefixed tables)
- Team, jobs, case studies
- Contact form (Livewire 3 → 4)
- Docs / LaRecipe isolation
- Filament removal (source app only; never installed here)
- Breeze removal (source app only)
- Authentication migration
- Admin CRUD rewrite
- Media migration
- Catch-all `/{slug}` (only after reserved paths are excluded)

---

## Risks

- `module:make`’s `composer dump-autoload` timed out. Boot-time PSR-4 registration works; a later successful dump-autoload is still worthwhile.
- Pre-existing `laradashboard: false` means the tracked theme module is disabled in this working tree. That is unrelated to Squartup but affects the public frontend host.
- On-disk `ecom` / `library` modules exist and are disabled. Not part of this work.
- Unused `module:make` admin Livewire/layout files remain on disk. They are not routed. A later admin phase should replace them deliberately rather than “turning them on”.
- Laravel 12 → 13 and Livewire 3 → 4 will force rewrites when domain code is ported.
- Squartup table names (`contacts`, `categories`, `blog_posts`) will collide conceptually with LD/CRM if created unprefixed.
- Squartup theme CSS/JS must stay module-local; a future merge into root Vite would break Tailwind 4 isolation.
- Browser MCP was unavailable in this session. HTTP verification used `http://laradashboard.test`. Authenticated `/admin` dashboard HTML was not clicked through after login.

---

## Recommended next phase

**Do not start this automatically.**

Phase 2 should port **config-driven public marketing pages only**, still inside the Squartup module:

1. Move Squartup-specific configs (`seo`, `homepage`, `about`, …) into the module.
2. Add a module public layout that loads **module-owned** theme assets (Bootstrap/jQuery/`restyle.css` under a module prefix), not the core admin build.
3. Port `/`, `/about-us`, `/services`, `/enterprise`, `/contact-us` behind explicit named routes.
4. Keep `/admin`, auth, `/dashboard`, `/profile`, `/docs`, and `/{slug}` out of the module.
5. Rewrite `ContactForm` to Livewire 4 only when the contact page is ported.
6. Continue to leave Filament, Breeze, products, checkout, blog, and database tables untouched.

Stop after that slice and re-run the route-collision audit before any commerce or CMS merge.
