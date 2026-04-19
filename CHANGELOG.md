# Changelog

All notable changes to **Lara Dashboard** are documented in this file. This project follows [Semantic Versioning](https://semver.org/).

> **Latest release:** [v1.1.2](https://github.com/laradashboard/laradashboard/releases/tag/v1.1.2) • [View all releases](https://github.com/laradashboard/laradashboard/releases)

---

## [v1.1.2] — 2026-04-19
-  **Fix:** Fixed Module generator command improvement for Windows OS.
-  **Fix:** Fixed CRUD generator command improvement for Windows OS.
-  **Improve:** Added more extendibility support for email templates.
-  **Improve:** Added more extendibility support for authentication pages.

## [v1.1.1] — 2026-04-10
-   **Feat:** Scheduled queue worker — runs every minute via `schedule:run` to process queued jobs (workflow actions, emails) without requiring a long-running worker.
-   **Improve:** Sidebar submenu supports deeper (3rd and 4th level) nesting with proper indentation.
-   **Improve:** Sidebar submenu expands without an inner scrollbar; the sidebar wrapper handles overflow when nav is long.
-   **Fix:** Cleaned up user dropdown styling in the admin header — removed stray borders and uneven top margins for consistent spacing.

## [v1.1.0] — 2026-04-05
-   **Feat:** Account created notification — admin-created users receive an email with login link and password-set URL.
-   **Feat:** Quick-add dropdown in admin header with hook support (`filter.quick_add_dropdown`).
-   **Feat:** Admin menu badge support (`setBadge()` / `setBadgeClass()` on `AdminMenuItem`).
-   **Feat:** Email connection toggle (activate/deactivate) with inline action button.
-   **Feat:** Email connection status filter (active, disabled, connected, failed).
-   **Feat:** Setting image removal endpoint for site logos/icons via AJAX.
-   **Feat:** Send login link action on user edit page.
-   **Improve:** Demo mode restrictions on core upgrade, backup, and restore operations.
-   **Improve:** Seeder defaults for authentication, site tagline, copyright, and contact settings.
-   **Fix:** Renamed "Inactive" to "Disabled" for email connection status labels (consistency).
-   **Fix:** Removed duplicate demo-mode check in module update flow.

## [v1.0.3] — 2026-03-29
-   **Fix:** Fixed marketplace module images not loading (JSON-encoded icon/banner URLs from API were not decoded properly).

## [v1.0.2] — 2026-03-29
-   **Feat:** Marketplace module browser — browse, search, and install modules from the marketplace.
-   **Feat:** Module packaging and distribution commands (`module:zip`, `module:package`, `module:compile-css`).
-   **Feat:** Claude Code agents and commands for faster module development workflow.
-   **Docs:** Added developer documentation for Tailwind CSS prefixing, AI architecture, theme development, permissions, and module packaging.
-   **Fix:** Fixed CRUD generator base controller replacement test for updated nwidart stubs.
-   **Fix:** Assign menu permissions to superadmin migration.

## [v1.0.1] — 2026-03-28
-   **Fix:** Fixed automatic module and core upgrade issue on some servers.
-   **Fix:** Fixed module stubs and CRUD command to generate files properly.

## [v1.0.0] — 2026-03-27
-   **Feat:** Upgraded to Laravel 13 and Livewire 4.
-   **Feat:** Base setup for Starter 26 theme.
-   **Feat:** CRUD Generator (`module:make-crud`) with Model, Datatable, Views, Routes, Menu scaffolding.
-   **Feat:** Inbound/Outbound email connection management.
-   **Feat:** Manual core upgrade system with backup/restore functionality.
-   **Feat:** Social authentication with Laravel Socialite.
-   **Feat:** Cache/performance management section in settings page.
-   **Feat:** Menu architecture for frontend with filter hooks support.
-   **Feat:** Compact file uploader component.
-   **Feat:** Clean all action logs button.
-   **Feat:** AI filter hooks for extensible AI integrations.
-   **Enhancement:** Module CRUD command improved — generates Blade views instead of Livewire components.
-   **Enhancement:** Translation system refactored with chunked data saving.
-   **Enhancement:** Quick links dropdown at navbar.
-   **Enhancement:** Post list updated with Updated column and activity log timestamps.
-   **Enhancement:** Media selector modal responsive improvement.
-   **Refactor:** Unified server-side block rendering for email and page contexts.
-   **Refactor:** Blocks refactored to remove unnecessary padding and standalone page improvement.
-   **Refactor:** Better structure for module installations.
-   **Fix:** Security fixes with sanitization in content rendering.
-   **Fix:** Email verification link, template preview, and campaign tracking fixes.
-   **Fix:** Fixed missing authorization checks.
-   **Fix:** Module installation, upload, and delete bug fixes.
-   **Fix:** Fixed cached permission issues.
-   **Fix:** Numerous test fixes and CI improvements.

## [v0.9.14] — 2026-03-16
-   **Refactor:** Unified server-side block rendering for email and page contexts via `render.php`.
-   **New:** `EmailStyleHelper` PHP utility for email-safe inline CSS from `layoutStyles`.
-   **New:** Server-side `render.php` for 13 blocks (divider, spacer, footer, social, preformatted, accordion, countdown, table, video, text-editor, html, columns, section).
-   **Improvement:** Email blocks now use server-side placeholders for consistent rendering with `layoutStyles` support.
-   **Fix:** Email campaign preview, send, and tracking now process dynamic blocks correctly.

## [v0.9.13] — 2026-03-16
-   **Refactor:** Block improvements for emails.
-   **Fix:** Module approval now syncs updated version assets and metadata.

## [v0.9.12] — 2026-03-14
-   **Fix:** Fixed security issue with sanitization in content rendering.
-   **Improvement:** Refactored code and `<pre>` block for better code snippet support.
-   **Fix:** Fixed email verification link to show valid link.

## [v0.9.11] — 2026-03-09
-   **Fix:** Fixed missing authorization checks.
-   **Fix:** Module CRUD with migration and some other registration logics.
-   **Improvement:** Translation system chunks data saving.

## [v0.9.10] — 2026-02-26
-   **Fix:** Block editor text block, heading block line break and some other issues.
-   **Fix:** Module list page module icon was not consistent.

## [v0.9.9.4] — 2026-02-24
-   **Fix:** Fixed cached permission.

## [v0.9.9.1] — 2026-02-24
-   **Fix:** Upgrade core issue on some servers.
-   **Enhancement:** Added mailer class column to email logs table.

## [v0.9.9] — 2026-02-22
-   **Feat:** Menu management for frontend.
-   **Enhancement:** Module command, module CRUD command improvements.
-   **Enhancement:** Several pages more filter hook supported.
-   **Fix:** Improved several test cases.

## [v0.9.8] — 2026-02-17
-   **Feat:** Social authentication with Laravel Socialite.
-   **Feat:** Cache / performance management section in settings page with cache clear, config cache, route cache, view cache and so on.
-   **Enhancement:** Improved core download and update system with better UI/UX.

## [v0.9.7] — 2026-02-14
-   **Fix:** Demo was not loading the faker.
-   **Enhancement:** Post list updated with Updated column.
-   **Enhancement:** Created date in activity log list page.
-   **Enhancement:** Media library modal responsive improvement.
-   **Fix:** Fixed `module:make-crud` command to generate menus properly.
-   **Feat:** Added clean all logs button in activity log list page.

## [v0.9.6] — 2026-02-13
-   **Feat:** Compact attachment component.
-   **Fix:** Fixed module replace modal scroll issue.
-   **Fix:** Demo app refresh issue.

## [v0.9.5] — 2026-02-12
-   **Fix:** Fixed module installation issue on some servers.

## [v0.9.3] — 2026-02-08
-   **Feat:** Inbound/Outbound email connection management.
-   **Feat:** CRUD Generator (`module:make-crud`) — rapid scaffolding for modules with Model, Datatable, Views, Routes, Menu.
-   **Enhancement:** Fallback queue handling management.
-   **Enhancement:** Quick links dropdown at navbar.
-   **Enhancement:** Updated some stubs for easy module generation following Lara Dashboard.

## [v0.9.2] — 2026-01-11
-   **Feat:** Manual core upgrade system with backup/restore functionality.
-   **Feat:** Production-ready zip distribution with vendor folder support.
-   **Feat:** cPanel/shared hosting support without document root changes.
-   **Enhancement:** Storage directory structure auto-creation during upgrades.

## [v0.9.1] — 2026-01-04
-   **Feat:** AI Agent — agentic CMS assistant to help you create, manage, and optimize content using AI.
-   **Feat:** Beautiful onboarding experience for initial installation.
-   **Enhancement:** Improved AI content generation and fine-tuning options in post/page editor.
-   **Enhancement:** Module improvement stability, upgrade notices.

## [v0.9.0] — 2025-12-19
-   **Chore:** Revamping the versions to v0.9.x.

## [v2.4.0-beta] — 2025-12-19
-   **Feat:** Post / Page Builder — manage posts/pages with visual drag-and-drop builder.
-   **Feat:** Email Management System — email connections, email templates with visual builder.
-   **Feat:** Notifications Management — centralized notification settings and management.
-   **Feat:** Detail Pages — User, Role, Permission, Module detail views with comprehensive information.
-   **Enhancement:** Improved module detail page with better UI/UX.
-   **Enhancement:** Improved role detail page with better UI/UX.
-   **Enhancement:** Module installation process improved with better UI/UX.

## [v2.3.0-beta] — 2025-09-07
-   **Feat:** Datatable integration for Users, Roles, Permissions, Posts, Categories, Tags.
-   **Feat:** Several new components.
-   **Feat:** Observer added for models.
-   **Feat:** Introduce hooks with more managed/documented way.
-   **Enhancement:** Updated code structure, new components, improved code quality.
-   **Fix:** Several small UI bugs.

## [v2.2.0-beta] — 2025-08-17
-   **Feat:** Media library manager.
-   **Feat:** Improved user / profile UI/UX with lots of options.
-   **Feat:** Implement reCAPTCHA and custom admin login integration with configurable page settings.
-   **Enhancement:** Update menu structure.
-   **Fix:** Several small UI bugs.

## [v2.0.1-beta] — 2025-07-27
-   **Feat:** Refactor Lara Dashboard whole Admin UI — icons, accessibility, components, pages, layouts and so on.
-   **Enhancement:** Keep search form design consistent as `form-control` height.
-   **Enhancement:** Global variable for editor script to handle from any module.
-   **Enhancement:** Cleanup many codes to separate service to keep business logics separated.
-   **Enhancement:** Fixed several unit tests.
-   **Fix:** User chart data with SQLite supported.
-   **Fix:** Remove some unnecessary console logs.
-   **Doc:** Added Coding Standard docs.

## [v2.0-beta] — 2025-07-20
-   **Feat:** Refactor Lara Dashboard whole Admin UI — icons, accessibility, components, pages, layouts and so on.
-   **Enhancement:** Improve components, reusability, and code quality.
-   **Fix:** Some random UI fixes.

## [v1.7.0-beta] — 2025-07-13
-   **Feat:** REST API for Lara Dashboard, Scramble API documentation.
-   **Enhancement:** Cleanup class names to use more standard class names.
-   **Fix:** Some random UI fixes.

## [v1.6.0-beta] — 2025-06-21
-   **Enhancement:** Write/Update standard unit tests, Pint, Rector, PHPStan for the project.
-   **Fix:** Potential fix for code scanning alert no. 1: Workflow does not contain permissions.
-   **Fix:** Language switcher if no icon is selected.
-   **Fix:** Non-translated keys keep empty instead of the placeholder.

## [v1.5.0-beta] — 2025-06-01
-   **Feature:** Content Management System (CMS) with Content (Post/Page), Content Category, Content Tag management.
-   **Feature:** Post/Page activity chart in Dashboard.
-   **Feature:** Bulk delete for Users, Roles, Posts, Categories, Tags.
-   **Enhancement:** Sorting features for Users, Roles, Permissions, Posts, Categories, Tags.
-   **Enhancement:** Components — Confirm delete, Success/Error messages, Toast, Text Editor, Breadcrumbs, Action dropdown.
-   **Fix:** System dark mode issues.
-   **Fix:** Modules refactoring in demo mode.

## [v1.3.0-beta] — 2025-05-18
-   **Feature:** Admin Menu architecture with more extendible way.
-   **Feature:** Permission List and detail page.
-   **Enhancement:** Improved module compatibility.

## [v1.2.0-beta] — 2025-05-12
-   **Feature — Translation Management:** Added translation management system supporting 21 languages by default and possibility to add any in seconds.
-   **Enhancement — Dashboard Redesign:** Dashboard redesigned with new cards, user history chart, several more design improvements.
-   **Enhancement:** Role list page, user list page to add links of users list sorting by role and role edit page linkings.
-   **Enhancement:** Cleanup codebase to use services, requests more, use SOLID whenever needed.
-   **Fix:** Fixed #109 Submenu dropdown icon doesn't change on open/close submenu of a menu item.
-   **Fix:** Fixed #105 Sidebar icon not working good if collapsed.
-   **Fix:** Fixed #93 Theme primary color, secondary color was not working.
-   **Fix:** Fixed #99 Superadmin role shouldn't be edited.
-   **Fix:** Fixed mobile responsive has some issues.
-   **Fix:** Fixed sidebar toggle was not persistent issue.
-   **Fix:** Fixed role create — selecting permission group can't check the permissions in that group checkboxes automatically.

## [v1.0.0-beta] — 2025-04-21
-   **Feature — Forget Password Management:** Enhanced the forget password functionality for better reliability and user experience.
-   **Feature — Settings Management:** Added comprehensive settings management features, including API support.
-   **Enhancement — Role-Based Access Control (RBAC) Improvements:** Improved authorization mechanisms and role-based access control.
-   **Feature — Admin Impersonation:** Administrators can now log in as other users and switch back to their original accounts seamlessly.
-   **Enhancement — UI/UX Enhancements:** Updated the role create/edit form for a more intuitive and user-friendly experience.
-   **Enhancement — User Profile and Management Enhancements:** Refactored user-related operations to utilize `UserService` and `RolesService` for better separation of concerns and maintainability.
-   **Docs — Documentation and Configuration Updates:**
    -   Updated `.env.example` to include a `GITHUB_LINK` variable for improved project visibility.

---

## Previous Laravel-version releases

<details>
<summary>View older Laravel-version tags</summary>

-   **Laravel 7.x & PHP 7.x**
    -   Tag — https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel7.x
    -   Branch — https://github.com/ManiruzzamanAkash/laravel-role/tree/Laravel7.x

-   **Laravel 9.7 & PHP 8.x**
    -   Tag — https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel9.x

-   **Laravel 11.x**
    -   Tag — https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v11.x-main

-   **Laravel 12.x & PHP >= 8.3**
    -   Tag — https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel12.x

-   **Laravel 12.x & TailAdmin template integration**
    -   Tag — https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel12.x-tailadmin

-   **Laravel 12.x & Module & Action Log integration**
    -   Tag — https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel12.x-module-logs

-   **v1.0.0** — Settings, Forget Password and lots of refactoring
    -   Tag — https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v1.0.0
-   **v1.2.0** — Translation Management, Dashboard Redesign, Role/User List improvements
    -   Tag — https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v1.2.0
-   **v1.3.0** — Admin Menu architecture, Permission List and detail page
    -   Tag — https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v1.3.0
-   **v1.5.0** — https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v1.5.0
-   **v1.6.0** — https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v1.6.0
-   **v1.7.0** — https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v1.7.0

More release tags — https://github.com/laradashboard/laradashboard/releases

</details>
