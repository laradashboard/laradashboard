# Module Development Guide

This guide covers everything you need to know about developing, building, and distributing modules for Lara Dashboard.

## Table of Contents

-   [Overview](#overview)
-   [Getting Started](#getting-started)
-   [Module Structure](#module-structure)
-   [Creating a New Module](#creating-a-new-module)
-   [Module Configuration](#module-configuration)
-   [Module Dependencies](#module-dependencies)
-   [Frontend Assets (CSS/JS)](#frontend-assets-cssjs)
-   [Building for Distribution](#building-for-distribution)
-   [Installing Modules](#installing-modules)
-   [Module Commands Reference](#module-commands-reference)
-   [Best Practices](#best-practices)
-   [Troubleshooting](#troubleshooting)

---

## Overview

Lara Dashboard uses [nwidart/laravel-modules](https://laravelmodules.com/) for modular architecture. Modules are self-contained packages that extend the dashboard functionality.

**Key Features:**

-   Self-contained modules with their own controllers, models, views, and assets
-   **Independent dependencies** - Each module can have its own composer packages
-   Pre-compiled CSS/JS support (no npm required on server)
-   Easy installation via ZIP upload or CLI
-   Automatic asset publishing on module upload

---

## Getting Started

### Prerequisites

-   Lara Dashboard installed and running
-   Node.js (for development/building only)
-   Composer

### Quick Start

```bash
# Create a new module
php artisan module:make YourModule

# Install module dependencies (if module has composer.json)
php artisan module:install-deps YourModule

# Enable the module
php artisan module:enable YourModule

# Run module migrations
php artisan module:migrate YourModule

# Seed module data (if seeder exists)
php artisan module:seed YourModule
```

---

## Module Structure

A typical module follows this structure:

```
modules/YourModule/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Providers/
│   │   └── YourModuleServiceProvider.php
│   └── Services/
├── config/
│   └── config.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── assets/
│   │   ├── css/
│   │   │   └── app.css          # Tailwind CSS entry point
│   │   └── js/
│   │       └── app.js           # JavaScript entry point
│   ├── lang/
│   │   ├── en.json
│   │   └── bn.json
│   └── views/
│       ├── components/
│       ├── layouts/
│       └── livewire/
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   └── Feature/
├── dist/                         # Pre-compiled assets (for distribution)
│   └── build-yourmodule/
├── vendor/                       # Module-specific dependencies (git-ignored)
├── composer.json                 # Module dependencies & autoload config
├── composer.lock                 # Locked dependency versions (git-ignored)
├── description.md                # Module description (required, supports Markdown)
├── module.json                   # Module metadata
├── vite.config.js               # Vite configuration
└── README.md
```

**Important:** Each module can have its own `vendor/` directory with independent dependencies. This keeps the core application clean and allows modules to be truly self-contained.

---

## Creating a New Module

### Using Artisan Command

```bash
# Basic module creation
php artisan module:make Blog

# Create with specific features
php artisan module:make Blog --api      # Include API routes
php artisan module:make Blog --plain    # Minimal structure
```

### Manual Setup

1. Create the module directory structure
2. Create `module.json` with required metadata
3. Register the service provider

---

## Module Configuration

### module.json

Every module requires a `module.json` file:

```json
{
    "name": "blog",
    "title": "Blog",
    "keywords": ["blog", "posts", "articles"],
    "category": "content",
    "priority": 10,
    "providers": ["Modules\\Blog\\Providers\\BlogServiceProvider"],
    "files": [],
    "icon": "lucide:book-open",
    "logo_image": null,
    "banner_image": null,
    "version": "1.0.0",
    "author": "Your Name",
    "author_url": "https://yourwebsite.com",
    "documentation_url": "https://docs.yourwebsite.com/blog"
}
```

| Field               | Description                                                        | Required |
| ------------------- | ------------------------------------------------------------------ | -------- |
| `name`              | Module identifier (lowercase, slug format: `a-z`, `0-9`, `-`)      | Yes      |
| `title`             | Display name shown in UI                                           | Yes      |
| `keywords`          | Search keywords for module discovery                               | No       |
| `category`          | Module category                                                    | No       |
| `priority`          | Load order (lower = earlier)                                       | No       |
| `providers`         | Service provider classes                                           | Yes      |
| `icon`              | Iconify icon class (e.g., `lucide:book-open`, `bi:journal-text`)   | No       |
| `logo_image`        | Path to logo image (relative to module assets or absolute URL)     | No       |
| `banner_image`      | Path to banner image (relative to module assets or absolute URL)   | No       |
| `version`           | Semantic version (e.g., `1.0.0`)                                   | Yes      |
| `author`            | Module author name                                                 | No       |
| `author_url`        | Link to author's website or profile                                | No       |
| `documentation_url` | Link to module documentation                                       | No       |
| `pricing`           | Pricing type: `free`, `paid`, or `both` (default: `free`)          | No       |

### description.md (Required)

Every module must include a `description.md` file in the module root. This file contains the module's description in Markdown format and is displayed on the marketplace.

```markdown
# Blog Module

A comprehensive blog management module for creating and managing posts, categories, and comments.

## Features

- Create and edit blog posts with rich text editor
- Organize posts with categories and tags
- Comment system with moderation
- SEO-friendly URLs
- RSS feed support

## Requirements

- Lara Dashboard 1.0.0 or higher
- PHP 8.2+

## Installation

1. Upload the module ZIP file
2. Enable the module from the admin panel
3. Run migrations: `php artisan module:migrate Blog`
```

**Important Notes:**
- The `description.md` file is **required** for marketplace submission
- Markdown formatting is supported and rendered as HTML
- To update your module's description, upload a new version with the updated `description.md`
- The description cannot be edited from the marketplace UI - it must be included in your module ZIP

### Module Images (Logo & Banner)

You can add branding images to your module that will be displayed in the module list and detail pages.

**Logo Image (`logo_image`):**
- Displayed in the module list (40x40px) and detail page (96x96px)
- Falls back to the `icon` if not provided
- Recommended size: 256x256px (square)

**Banner Image (`banner_image`):**
- Displayed at the top of the module detail page
- Recommended size: 1200x400px

**Image Paths:**
- **Relative path**: Relative to module's built assets (e.g., `images/logo.png` will resolve to `public/build-{module}/images/logo.png`)
- **Absolute URL**: External URLs are supported (e.g., `https://example.com/logo.png`)

**Example with images:**
```json
{
    "name": "crm",
    "title": "CRM",
    "icon": "lucide:users",
    "logo_image": "images/crm-logo.png",
    "banner_image": "images/crm-banner.jpg",
    "version": "1.0.0"
}
```

To include images in your distribution:
1. Place images in `modules/YourModule/dist/build-yourmodule/images/`
2. Reference them in `module.json` as `images/filename.png`
3. Images will be published to `public/build-yourmodule/images/` on module upload

### Module Pricing

You can define your module's pricing type in `module.json`. This determines whether users need to activate a license to use the module.

**Pricing Values:**

| Value    | Description                                                    |
| -------- | -------------------------------------------------------------- |
| `free`   | Module is completely free, no license required                 |
| `paid`   | Module requires a license to use                               |
| `both`   | Module has free and paid versions                              |

**Examples:**
```json
{
    "name": "blog",
    "pricing": "free"
}
```

```json
{
    "name": "advanced-crm",
    "pricing": "paid"
}
```

```json
{
    "name": "email-marketing",
    "pricing": "both"
}
```

**License Activation:**

For `paid` and `both` modules, users will see an "Activate License" option in the module actions menu. This allows them to enter their license key purchased from the marketplace.

**Note:** Detailed pricing plans (tiers, features, etc.) are managed on the Lara Dashboard marketplace when uploading the module, not in the module.json file.

**Important Notes:**

-   `name` must be **lowercase** and match the folder name when possible
-   `name` is used for status tracking, routes, and internal identification
-   `title` is what users see in the admin panel

### Service Provider

Register your module's components in the service provider:

```php
<?php

namespace Modules\Blog\Providers;

use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'blog');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'blog');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'blog');
    }
}
```

---

## Module Dependencies

Modules can have their own Composer dependencies, completely independent from the core application. This ensures:

- **Clean core** - The main `composer.lock` stays focused on core dependencies
- **True modularity** - Modules are self-contained and portable
- **No conflicts** - Different modules can use different package versions

### composer.json Structure

Every module should have a `composer.json` file that defines its namespace autoloading and dependencies:

```json
{
    "name": "yourvendor/yourmodule",
    "description": "Your module description",
    "require": {
        "stripe/stripe-php": "^13.0",
        "some/package": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "Modules\\YourModule\\": ["", "app/"],
            "Modules\\YourModule\\Database\\Factories\\": "database/factories/",
            "Modules\\YourModule\\Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Modules\\YourModule\\Tests\\": "tests/"
        }
    }
}
```

### PSR-4 Autoloading

The `autoload.psr-4` section tells Composer where to find your module's classes. You can map to multiple directories using an array:

```json
"Modules\\YourModule\\": ["", "app/"]
```

This allows classes in both the root directory and `app/` directory:
- `modules/YourModule/Models/Post.php` → `Modules\YourModule\Models\Post`
- `modules/YourModule/app/Http/Controllers/PostController.php` → `Modules\YourModule\Http\Controllers\PostController`

### Managing Dependencies

Use the built-in artisan commands to manage module dependencies:

```bash
# Install dependencies for a specific module
php artisan module:install-deps Blog

# Install dependencies for ALL modules
php artisan module:install-deps

# Update dependencies for a specific module
php artisan module:update-deps Blog

# Update dependencies for ALL modules
php artisan module:update-deps

# Run any composer command in a module directory
php artisan module:composer Blog require stripe/stripe-php
php artisan module:composer Blog remove some/package
```

### How It Works

1. **Bootstrap autoloading** - Module vendor autoloaders are registered at boot time via `bootstrap/modules.php`
2. **PSR-4 namespaces** - The module's `composer.json` defines how classes are autoloaded
3. **Independent vendor** - Each module has its own `vendor/` directory (git-ignored)
4. **Isolated lock files** - Each module has its own `composer.lock` (git-ignored)

### Adding a New Dependency

```bash
# Add a package to your module
php artisan module:composer Blog require laravel/cashier

# Or manually edit composer.json and run install
php artisan module:install-deps Blog
```

### Git Ignore

Module `vendor/` and `composer.lock` are automatically git-ignored. When distributing your module, users will run `php artisan module:install-deps YourModule` to install dependencies.

---

## Frontend Assets (CSS/JS)

### Tailwind CSS Setup

Create `resources/assets/css/app.css`:

```css
/* Use prefix to avoid conflicts with core styles */
@import "tailwindcss" prefix(blog);

/* Tell Tailwind where to scan for classes */
@source '../../views/**/*.blade.php';
@source '../js/**/*.js';

/* Optional: Include Alpine.js dynamic classes */
@source inline("
    x-cloak x-show x-transition
    blog-hidden blog-block blog-flex
");

/* Custom module styles */
@layer components {
    .blog-card {
        @apply blog-rounded-lg blog-bg-white blog-shadow-md blog-p-6;
    }
}
```

### Vite Configuration

Create `vite.config.js` in your module root:

```javascript
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Support both development and distribution builds
const isDistBuild = process.env.MODULE_DIST_BUILD === "true";

const distOutDir = path.resolve(__dirname, "dist/build-blog");
const devOutDir = path.resolve(__dirname, "../../public/build-blog");

export default defineConfig({
    build: {
        outDir: isDistBuild ? distOutDir : devOutDir,
        emptyOutDir: true,
        manifest: "manifest.json",
    },
    plugins: [
        laravel({
            publicDirectory: isDistBuild
                ? path.resolve(__dirname, "dist")
                : path.resolve(__dirname, "../../public"),
            buildDirectory: "build-blog",
            input: [
                path.resolve(__dirname, "resources/assets/css/app.css"),
                path.resolve(__dirname, "resources/assets/js/app.js"),
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

### Loading Assets in Blade

In your layout or view files:

```blade
@push('styles')
    @vite(['modules/blog/resources/assets/css/app.css'], 'build-blog')
@endpush

@push('scripts')
    @vite(['modules/blog/resources/assets/js/app.js'], 'build-blog')
@endpush
```

### Using Core Components

Lara Dashboard provides reusable CSS components. Use them in your module:

```blade
{{-- Form components --}}
<input type="text" class="form-control" />
<label class="form-label">Name</label>

{{-- Buttons --}}
<button class="btn btn-primary">Save</button>
<button class="btn btn-default">Cancel</button>

{{-- Badges --}}
<span class="badge-success">Active</span>
<span class="badge-danger">Inactive</span>

{{-- Tables --}}
<table class="table-default">...</table>
```

See `resources/css/components.css` for all available components.

---

## Building for Distribution

### Development Build

For local development with hot reload:

```bash
# Build to public/ directory
php artisan module:compile-css Blog

# Watch mode for development
php artisan module:compile-css Blog --watch
```

### Distribution Build

For creating a self-contained module ZIP:

```bash
# Step 1: Build assets inside module directory
php artisan module:compile-css Blog --dist

# Step 2: Package the module
php artisan module:package Blog

# Or combine both steps
php artisan module:package Blog --compile
```

### Package Options

```bash
# Exclude vendor directory (smaller ZIP)
php artisan module:package Blog --no-vendor

# Minify assets
php artisan module:package Blog --compile --minify

# Custom output path
php artisan module:package Blog --output=/path/to/output.zip
```

### Output Structure

The packaged ZIP contains:

```
Blog-v1.0.0.zip
└── Blog/
    ├── app/
    ├── config/
    ├── database/
    ├── resources/
    ├── routes/
    ├── dist/                    # Pre-compiled assets
    │   └── build-blog/
    │       ├── manifest.json
    │       └── assets/
    │           ├── app-xxx.css
    │           └── app-xxx.js
    ├── module.json
    └── vite.config.js
```

---

## Installing Modules

### Via Web Interface

1. Navigate to **Admin → Modules → Install Module**
2. Upload the module ZIP file
3. Assets are published automatically
4. Enable the module from the module list

### Via CLI

```bash
# Extract module to modules/ directory
unzip Blog-v1.0.0.zip -d modules/

# Install module dependencies (if module has composer.json with requirements)
php artisan module:install-deps Blog

# Publish pre-built assets (usually automatic)
php artisan module:publish-assets Blog

# Run migrations
php artisan module:migrate Blog

# Enable the module
php artisan module:enable Blog
```

### Asset Publishing

When a module with pre-built assets is uploaded:

1. ZIP is extracted to `modules/`
2. Assets from `modules/Blog/dist/build-blog/` are copied to `public/build-blog/`
3. Module is ready to use (no npm required)

---

## Module Commands Reference

### Core Module Commands

| Command                             | Description           |
| ----------------------------------- | --------------------- |
| `php artisan module:make {name}`    | Create a new module   |
| `php artisan module:enable {name}`  | Enable a module       |
| `php artisan module:disable {name}` | Disable a module      |
| `php artisan module:migrate {name}` | Run module migrations |
| `php artisan module:seed {name}`    | Run module seeders    |
| `php artisan module:list`           | List all modules      |

### Dependency Commands

| Command                                              | Description                              |
| ---------------------------------------------------- | ---------------------------------------- |
| `php artisan module:install-deps {name?}`            | Install module composer dependencies     |
| `php artisan module:update-deps {name?}`             | Update module composer dependencies      |
| `php artisan module:composer {name} {command}`       | Run any composer command in module dir   |
| `php artisan module:composer Blog require pkg/name`  | Add a package to a module                |
| `php artisan module:composer Blog remove pkg/name`   | Remove a package from a module           |

### Asset Commands

| Command                                         | Description                   |
| ----------------------------------------------- | ----------------------------- |
| `php artisan module:compile-css {name}`         | Build assets for development  |
| `php artisan module:compile-css {name} --dist`  | Build assets for distribution |
| `php artisan module:compile-css {name} --watch` | Watch mode                    |
| `php artisan module:publish-assets {name}`      | Publish pre-built assets      |
| `php artisan module:publish-assets`             | Publish all module assets     |

### Packaging Commands

| Command                                         | Description              |
| ----------------------------------------------- | ------------------------ |
| `php artisan module:package {name}`             | Create distributable ZIP |
| `php artisan module:package {name} --compile`   | Compile and package      |
| `php artisan module:package {name} --no-vendor` | Exclude vendor directory |

### Generator Commands

```bash
php artisan module:make-controller PostController Blog
php artisan module:make-model Post Blog
php artisan module:make-migration create_posts_table Blog
php artisan module:make-seeder PostSeeder Blog
php artisan module:make-request StorePostRequest Blog
php artisan module:make-resource PostResource Blog
php artisan module:make-test PostTest Blog
```

---

## Best Practices

### Code Organization

1. **Follow SOLID principles** - Keep classes focused and maintainable
2. **Use Services** - Put business logic in service classes, not controllers
3. **Form Requests** - Always validate input with dedicated request classes
4. **Factories** - Create factories for all models for testing

### Naming Conventions

| Item                            | Format                      | Example                              |
| ------------------------------- | --------------------------- | ------------------------------------ |
| Module `name` (in module.json)  | `lowercase` or `kebab-case` | `crm`, `task-manager`                |
| Module `title` (in module.json) | Human-readable              | `CRM`, `Task Manager`                |
| Folder name                     | Same as `name`              | `crm`, `task-manager`                |
| Namespace                       | `PascalCase`                | `Modules\Crm`, `Modules\TaskManager` |
| CSS prefix                      | `lowercase`                 | `crm-`, `taskmanager-`               |

**Example:**

```
Folder: modules/task-manager/
module.json:
  "name": "task-manager"
  "title": "Task Manager"
Namespace: Modules\TaskManager
CSS prefix: taskmanager-
```

### CSS Class Prefixing

Always prefix your Tailwind classes to avoid conflicts:

```css
/* Good - prefixed */
@import "tailwindcss" prefix(blog);
/* Usage: blog-flex, blog-text-lg, blog-bg-white */

/* Bad - no prefix */
@import "tailwindcss";
/* Will conflict with core styles */
```

### Translations

Support multiple languages:

```json
// resources/lang/en.json
{
    "blog.posts": "Posts",
    "blog.create_post": "Create Post"
}
```

```blade
{{ __('blog::blog.posts') }}
```

### Permissions

Register permissions for your module:

```php
// In your seeder or migration
Permission::create(['name' => 'blog.view', 'guard_name' => 'web']);
Permission::create(['name' => 'blog.create', 'guard_name' => 'web']);
Permission::create(['name' => 'blog.edit', 'guard_name' => 'web']);
Permission::create(['name' => 'blog.delete', 'guard_name' => 'web']);
```

```php
// In controller
$this->authorize('blog.create');
```

### Testing

Write tests for your module:

```bash
# Create a feature test
php artisan module:make-test PostControllerTest Blog

# Run module tests
php artisan test modules/Blog/tests/
```

---

## Troubleshooting

### "Vite manifest not found"

**Cause:** Assets not built or manifest in wrong location.

**Solution:**

```bash
# Rebuild assets
php artisan module:compile-css YourModule

# Or for installed modules
php artisan module:publish-assets YourModule
```

### "Class not found" after upload

**Cause:** Module autoloader or dependencies not set up correctly.

**Solution:**

```bash
# First, install module dependencies
php artisan module:install-deps YourModule

# If still not working, regenerate core autoloader
composer dump-autoload
```

### Module dependency class not found

**Cause:** Module's vendor dependencies not installed.

**Solution:**

```bash
# Install the module's dependencies
php artisan module:install-deps YourModule

# Verify the vendor directory exists
ls modules/YourModule/vendor/
```

### PSR-4 autoload not working

**Cause:** Module's `composer.json` has incorrect namespace mapping.

**Solution:**

Ensure your module's `composer.json` maps all directories where classes exist:

```json
{
    "autoload": {
        "psr-4": {
            "Modules\\YourModule\\": ["", "app/"]
        }
    }
}
```

The array `["", "app/"]` allows classes in both the module root and `app/` subdirectory.

### CSS classes not working

**Cause:** Tailwind classes not compiled or prefix mismatch.

**Solution:**

1. Check your `app.css` has correct `@source` directives
2. Rebuild assets: `php artisan module:compile-css YourModule`
3. Ensure you're using the correct prefix in your blade files

### Module not appearing in list

**Cause:** Invalid `module.json` or service provider error.

**Solution:**

1. Validate `module.json` syntax
2. Check Laravel logs: `storage/logs/laravel.log`
3. Ensure service provider class exists and is correct

---

## Marketplace (Coming Soon)

We're building a module marketplace where you can:

-   Browse and discover modules
-   One-click install from the dashboard
-   Publish your own modules
-   Manage updates and versioning

Stay tuned for updates!

---

## Resources

-   [Laravel Modules Documentation](https://laravelmodules.com/docs)
-   [Tailwind CSS v4 Documentation](https://tailwindcss.com/docs)
-   [Lara Dashboard Documentation](https://laradashboard.com/docs/)
-   [Example Modules](https://github.com/laradashboard)

---

## Need Help?

-   [GitHub Issues](https://github.com/laradashboard/laradashboard/issues)
-   [Facebook Community](https://www.facebook.com/groups/laradashboard)
-   [Discord Server](https://discord.gg/laradashboard)
