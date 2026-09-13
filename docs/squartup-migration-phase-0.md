# Squartup → Laradashboard Migration — Phase 0 Host & Compatibility Audit

**Date:** 14 September 2026
**Branch:** `feature/squartup-module`
**Squartup source inspected:** `e:\Web Developments\LARAVEL\squartup-web`
**Architecture report:** `SQUARTUP_ARCHITECTURE.md` was produced in a prior Squartup-web analysis session (14 September 2026). The file is no longer present on disk; this audit re-inspected that source and the Laradashboard repository rather than relying on the missing report alone.

This document is an audit only. Phase 1 creates an isolated module skeleton. No Squartup domain code is migrated here.

---

## Pre-existing working tree

Before this work, `git status` already showed an unrelated local change:

| File | Change | Action taken |
| --- | --- | --- |
| `modules_statuses.json` | `laradashboard` flipped from `true` to `false` | Preserved. Not reverted. |

Do not treat that disable as part of the Squartup migration.

---

## Environment

```text
Squartup Laravel: 12.58.0 (composer.lock) / ^12.0 (composer.json)
Squartup PHP: ^8.2
Squartup Livewire: 3.7.15 (composer.lock; pulled in by Filament)
Squartup Tailwind: ^3.1.0 plus @tailwindcss/vite ^4.0.9 (mixed Tailwind 3 config + Tailwind 4 Vite plugin)
Squartup Vite: ^6.1.0 (package.json)
Squartup frontend stack: Blade marketing site + Bootstrap CSS + jQuery plugins + public/css/restyle.css + Alpine.js ^3.4.2 + one Livewire 3 contact form. Not Inertia/React/Vue. Filament 4.11.1 admin. Laravel Breeze ^2.3 auth. stripe/stripe-php ^16.5. LaRecipe ^2.9 for product docs.
```

```text
Laradashboard Laravel: 13.19.0 (`php artisan about`) / ^13.0 (composer.json)
Laradashboard PHP: ^8.3|^8.4 (runtime during audit: 8.4.25)
Laradashboard Livewire: 4.2.1 (`php artisan about`) / ^4.0 (composer.json)
Laradashboard Tailwind: ^4.0.15 (@tailwindcss/vite + tailwindcss)
Laradashboard Vite: ^7.3.5
```

Additional Laradashboard host facts (from the running application, lockfiles, and config — not README):

- App version: `1.4.2` (`version.json`, `package.json`)
- `nwidart/laravel-modules`: `v13.0.0`
- Alpine.js: `^3.14.9` (bundled with Livewire; also a direct dependency)
- Admin UI: Tailwind 4 + Alpine + Livewire 4. Bootstrap is **not** the admin CSS framework. `bootstrap-icons` is present as an icon set only.
- jQuery: not a Laradashboard admin runtime dependency.

---

## Laradashboard host readiness

Laradashboard is ready to **host** a Squartup module. It is not ready to receive a lift-and-shift of the Squartup application.

### How `/admin` is registered

Core `routes/web.php` registers the admin area as:

- prefix: `admin`
- name prefix: `admin.`
- middleware: `auth`, `verified`
- dashboard: `GET /admin` → `admin.dashboard`

`RouteServiceProvider::ADMIN_DASHBOARD` is `/admin`. Modules conventionally mount under `admin/{module}` (CRM uses `admin/crm`). They must not replace `/admin`.

### Authentication

Owned by core:

- `routes/auth.php` — login, register (setting-gated), password reset (setting-gated), email verification, logout, social login
- `App\Models\User` with Spatie roles/permissions
- Profile at `/profile/edit` (`profile.*`)

Squartup Breeze (`/login`, `/dashboard`, `/profile`) and Filament login (`/admin/login`) must not be copied.

### Users, roles, permissions

Core owns:

- Users CRUD under `/admin/users`
- Roles and permissions under `/admin/roles` and `/admin/permissions`
- `spatie/laravel-permission` ^6.4
- Module permission groups via `PermissionFilterHook::PERMISSION_GROUPS`

Squartup `User` implements `FilamentUser` and has no Spatie roles. Do not replace the Laradashboard user model.

### How modules register admin functionality

Established convention (CRM / CustomForm / `module:make` stubs):

1. `module.json` lists the module service provider
2. Provider loads views (`{alias}::`), config (`config('{alias}')`), translations, migrations
3. `RouteServiceProvider` loads `routes/web.php` and `routes/api.php`
4. Admin UI is typically `auth` + `verified` + `admin/{alias}`
5. Sidebar items are added with `AdminFilterHook::ADMIN_MENU_GROUPS_BEFORE_SORTING`
6. Assets compile through the module’s own `vite.config.js` to `public/build-{alias}` or `dist/build-{alias}`, then load with `<x-module-styles>`

### How module routes, views, and assets load

- Routes: module `RouteServiceProvider` + `web` / `api` middleware. No catch-all in core.
- Views: `loadViewsFrom(..., '{alias}')` — e.g. `squartup::health`
- Assets: isolated Vite build. Core `vite.config` must not ingest Squartup theme CSS/JS
- Autoload: module `composer.json` PSR-4 (`Modules\Squartup\`) via the module autoloader

### Existing Laradashboard functionality (reuse, do not recreate)

| Capability | Owner | Notes |
| --- | --- | --- |
| Users | Core | `users` table, admin CRUD, impersonation |
| Roles / permissions | Core | Spatie |
| Authentication / profile | Core | Breeze-like Laravel UI auth, `/profile` |
| Posts | Core CMS | Post type `post` (blog entries) |
| Pages | Core CMS | Post type `page` |
| Categories / tags | Core CMS | Taxonomies `category` and `tag` on `terms` / `taxonomies` |
| Media | Core | Spatie Media Library, `/admin/media` |
| Settings | Core | `/admin/settings` |
| Menus | Core | `/admin/menus` |
| Forms | `customform` module | Form builder + public `/forms/{slug}` |
| CRM | `crm` module | Contacts live in `crm_contacts`, not `contacts` |
| Translations | Core | |
| Email templates / connections | Core | |
| Themes | Core + theme modules | `Laradashboard` and `Starter26` are theme modules |
| Modules marketplace | Core | |
| Payments (host) | Core has `laravel/cashier` ^16.5 | Does **not** replace Squartup Stripe checkout; evaluate later |
| E-commerce experiments | On-disk `modules/ecom` | Not listed as enabled in committed `modules_statuses.json` |

On-disk modules at audit time: `crm` (enabled), `CustomForm` (enabled), `ecom`, `library`, `Laradashboard` (locally disabled in the pre-existing statuses edit). `DocForge` and `Starter26` are listed in statuses but were not present in `modules/` during this audit.

---

## Major conflicts

| Area | Squartup | Laradashboard | Severity |
| --- | --- | --- | --- |
| `/admin` | Filament panel `path('admin')` | Core admin dashboard | **Critical.** Do not install Filament. Rewrite admin later as module screens under a non-conflicting prefix such as `admin/squartup`. |
| Authentication | Breeze + Filament login | Core Laravel UI auth | **Critical.** LD owns login/logout/password/register. |
| `/dashboard` | Breeze `view('dashboard')` | Not a public LD route; admin dashboard is `/admin` | Do not add `/dashboard`. |
| `/profile` | Breeze profile CRUD | Core `/profile/edit` | Do not copy Breeze profile. |
| Users | `users` + `FilamentUser` | `users` + Spatie | Same table name. Never migrate Squartup user schema onto LD users. |
| Roles / permissions | None (Filament access only) | Spatie roles/permissions | LD remains the ACL. |
| Categories | `categories` table + `Category` model | Taxonomy `category` on `terms` | Name collision. Prefix Squartup tables (`squartup_categories`) or merge into LD terms later. |
| Contacts | `contacts` table + Livewire form | CRM `crm_contacts`; CustomForm submissions | Conceptual overlap. Keep Squartup leads in a prefixed table if they remain a marketing inbox. |
| Blog / posts | `blog_posts` + Filament `BlogPostResource` | CMS `posts` (`post_type = post`) | Evaluate MERGE vs prefixed `squartup_blog_posts`. Do not drop LD posts. |
| Settings | `config/*.php` + `config/seo.php` etc. | `settings` table + admin UI | LD owns platform settings. Squartup marketing config stays module-local. |
| Media | Filament file uploads on models | Spatie Media Library | Evaluate later. Do not replace LD media. |
| Menus | Hardcoded Blade nav | Admin menu builder | Public Squartup nav is Blade; do not overwrite LD menus. |
| `/docs` | Product docs via LaRecipe-style routes under `/products/{slug}/docs` and legacy `/{slug}/docs` | No core `/docs` | Isolate later. Do not register `/docs` in Phase 1. |
| `/api/user` | Sanctum default (typical Breeze) | `GET /api/auth/user` (Sanctum, prefixed) | Do not add `/api/user`. |
| Sitemap / robots | `/sitemap.xml`, `/robots.txt` routes | SEO meta helpers + `public/robots.txt` as a file | Route collision if Squartup routes are copied. Defer. |
| Catch-all `/{slug}` | Legacy product redirects | Would shadow `/admin`, `/login`, `/profile`, theme routes | **Forbidden in Phase 1.** Must stay deferred and carefully ordered later. |
| Laravel major | 12 | 13 | Controllers/models must be ported, not copied blindly. |
| Livewire major | 3 | 4 | `ContactForm` must be rewritten for Livewire 4 later. |
| Tailwind major | 3 (+ partial 4 plugin) | 4 | Do not merge Squartup Tailwind config into the core build. |
| Vite major | 6 | 7 | Use the module Vite config, not the root config. |
| PHP | ^8.2 | ^8.3\|^8.4 | Host is stricter; fine if the runtime is 8.3+. |
| Cashier vs Stripe PHP | `stripe/stripe-php` | `laravel/cashier` | Checkout migration is a later, separate design. |

---

## Filament resources to rewrite later

Do **not** remove Filament from Squartup in this phase. Eventual native module admin replacements:

| Filament resource | Model | Eventual destination |
| --- | --- | --- |
| `UserResource` | `User` | **Do not rewrite.** Use Laradashboard users. |
| `ProductsResource` | `Product` (+ images, pricing, tax, units) | Module admin CRUD later |
| `CategoryResource` | `Category` | MERGE into LD terms **or** prefixed module taxonomy |
| `BlogPostResource` | `BlogPost` (+ stats/chart widgets) | MERGE into LD Posts **or** module blog |
| `TeamResource` | `Team` | Module admin CRUD later |
| `JobPostResource` | `JobPost` | Module admin CRUD later |
| `ContactResource` | `Contact` | Module inbox **or** CRM/CustomForm later |
| `Pages\Dashboard` | Filament widgets | Optional module dashboard later |
| `Pages\Auth\Login` | Filament login | **Discard.** LD owns auth. |

No other Filament resources were found.

---

## Configuration that should eventually move

Squartup-specific (module-local later, not in Phase 1):

- `config/seo.php`
- `config/assets.php`
- `config/homepage.php`
- `config/page-heroes.php`
- `config/about.php`
- `config/enterprise.php`
- `config/services-page.php`
- `config/trusted-partners.php`
- Marketing Support classes that read those configs

Keep on the host / do not copy:

- `config/app.php`, `auth.php`, `database.php`, `session.php`, `cache.php`, `filesystems.php`, `mail.php`, `queue.php`, `logging.php`, `cors.php`, `sanctum.php`, `hashing.php`, `broadcasting.php`, `view.php`
- `config/filament.php`
- `config/larecipe.php` (evaluate isolation later)
- Stripe keys belong in module config later, not in core `.env` blindly

---

## Public frontend isolation (intended approach)

Squartup’s public site is a **legacy Bootstrap + jQuery theme**, not a Tailwind 4 admin skin.

**Do not:**

- Merge Squartup Tailwind 3 config into Laradashboard Tailwind 4
- Globally load `restyle.css`, Bootstrap CSS/JS, or jQuery
- Overwrite root `vite.config.*`
- Compile Squartup theme files through the core `resources/` pipeline

**Do (later phases):**

1. Keep a module-local Vite app (`modules/Squartup/vite.config.js`) that emits `public/build-squartup` / `dist/build-squartup` with `@import "tailwindcss" prefix(squartup)`.
2. Load that bundle only from Squartup views via `<x-module-styles>` / `<x-module-scripts>`.
3. Serve the existing Bootstrap/jQuery/`restyle.css` theme as **module-owned static assets** (module `resources/assets` or published `public/modules/squartup/...`), referenced only by Squartup public layouts.
4. Rewrite the Livewire contact form to Livewire 4 inside the module when the public site is ported.

Phase 1 only establishes the isolated Vite/Tailwind prefix files. It does not copy theme assets.

---

## Database (Phase 1)

No Squartup tables are created in Phase 1.

Squartup tables that must not be created yet:

`products`, `product_images`, `pricings`, `orders`, `order_items`, `teams`, `job_posts`, `case_studies`, `case_study_categories`, `contacts`, `blog_posts`, `categories`, `tax_rates`, `units`

Laradashboard tables that must not be altered:

`users`, `roles`, `permissions`, `posts`, `pages` (post type), `categories` (taxonomy terms), `media`, `settings`, `menus`

When tables are added later, prefix them (`squartup_*`) unless an explicit MERGE into a core/CMS table is approved.

---

## Authentication migration strategy (deferred)

1. Leave Laradashboard auth, users, roles, and `/admin` untouched.
2. Do not copy Squartup `User`, Breeze routes/controllers, or Filament auth.
3. Map Squartup operators onto existing LD users + roles when admin CRUD is built.
4. Public visitors do not need LD accounts. Contact form stays guest-facing.
5. Checkout customers (orders) are not LD users unless a later commerce design says otherwise.

---

## Migration decisions

| Squartup feature | Action | Reason |
| --- | --- | --- |
| Authentication | DEFER | Laradashboard should own auth |
| Users | DEFER | Laradashboard owns users |
| Roles/permissions | DEFER | Laradashboard owns permissions |
| Filament admin | DEFER | Rewrite later as native module admin |
| Breeze | DEFER | Remove only after Filament/auth are unused in the source app; do not uninstall anything here |
| Blog | DEFER/MERGE | Evaluate Laradashboard Posts |
| Categories | DEFER/MERGE | Avoid collision with LD taxonomy |
| Media | DEFER/MERGE | Evaluate Laradashboard Media |
| Settings | DEFER/MERGE | Laradashboard owns platform settings; marketing config is module-local later |
| Menus | DEFER | LD owns admin menus; Squartup public nav stays in module Blade later |
| Public frontend | DEFER | Preserve Squartup frontend later, isolated |
| Checkout / Stripe | DEFER | Requires a separate commerce migration; host already has Cashier |
| Docs / LaRecipe | DEFER | Evaluate isolation later |
| Products / pricing / orders | DEFER | Domain migration, not foundation |
| Team / jobs / case studies | DEFER | Domain migration |
| Contact form | DEFER | Livewire 3 → 4 rewrite; table prefix vs CRM/CustomForm |
| Sitemap / robots | DEFER | Route and file collisions |
| Catch-all `/{slug}` | DEFER | Unsafe until every reserved path is excluded |
| SEO config / Support classes | DEFER | Move with public pages, not the skeleton |
| CRM contacts | DO NOT RECREATE | CRM already provides `crm_contacts` |
| Custom forms | DO NOT RECREATE | CustomForm module already exists |
| Core CMS posts/pages | DO NOT RECREATE | Reuse or merge later |

---

## Phase 1 scope (what follows this audit)

Create `modules/Squartup` with a manifest, provider, isolated assets, a non-conflicting `/squartup` health route, and documentation. No domain routes, no `/admin` routes, no auth routes, no migrations, no Filament, no dependency upgrades.
