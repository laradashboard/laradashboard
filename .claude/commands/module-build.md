---
description: "Build and package a module for distribution (compile assets + zip)"
allowed-tools: ["Bash", "Read", "Glob", "Grep"]
---

# Build & Package Module

Build a LaraDashboard module for distribution using `module:zip`.

## Instructions

1. Parse arguments: $ARGUMENTS (module name required)

2. Pre-build checks:
   - Verify module exists: `ls modules/{module}/module.json`
   - Check module.json has correct version, name, icon, etc.
   - Verify composer.json exists if module has dependencies
   - Check for uncommitted changes in the module

3. Run the build:
   ```bash
   php artisan module:zip {ModuleName} --no-interaction
   ```

4. If build fails, diagnose:
   - Missing dependencies: `php artisan module:install-deps {ModuleName}`
   - Asset compilation issues: Check `vite.config.js` in the module
   - Missing npm packages: Run `npm install` in module directory

5. Report the output ZIP location and size

## Build Options
- `--skip-composer` — Skip composer install step
- `--skip-npm` — Skip npm install step
- `--skip-compile` — Skip asset compilation
- `--no-minify` — Don't minify assets
- `--no-vendor` — Exclude vendor directory from ZIP
- `--output=path` — Custom output path

## Quick Asset Compilation Only
If user just wants to compile CSS/JS without full build:
```bash
php artisan module:compile-css {ModuleName} --watch    # Development
php artisan module:compile-css {ModuleName} --minify   # Production
```
