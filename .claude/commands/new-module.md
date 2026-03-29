---
description: "Create a new LaraDashboard module with proper structure, CSS prefixing, hooks, menu, and CLAUDE.md"
allowed-tools: ["Bash", "Read", "Write", "Edit", "Glob", "Grep"]
---

# Create New Module

Create a new LaraDashboard module using the `module:make` artisan command, then set up all required patterns.

## Instructions

1. Ask the user for the module name if not provided as argument: $ARGUMENTS
2. Also ask:
   - **Module type**: feature (like CRM), theme (like Starter26), addon, or tools?
   - **CSS prefix**: Short abbreviation (2-5 chars, e.g., `crm`, `st`, `df`)
   - **Category**: core, theme, addon, or tools?

3. Run `php artisan module:make {ModuleName} --no-interaction` to scaffold the module

4. After scaffolding, set up additional patterns:

### Tailwind CSS Prefixing
Create/update `modules/{module}/resources/assets/css/app.css`:
```css
@import "tailwindcss" prefix({prefix});

@source inline("border-primary border-2 border-gray-200 bg-primary bg-primary/5 bg-white");

@layer base {
  :root, :host {
    --{prefix}-color-primary: var(--color-primary, #635bff);
  }
}
```

### Vite Config
Create/update `modules/{module}/vite.config.js` with correct build paths and Tailwind plugin.

### Module.json
Update `module.json` with:
- `"category"`: based on user choice (core, theme, addon, tools)
- `"theme": true`: **REQUIRED** if this is a theme module (this is how the system detects themes)
- `"icon"`: suggest an appropriate `lucide:` icon
- `"min_laradashboard_required"`: current app version

### Hook Enums (for feature modules)
Create `app/Enums/Hooks/{Module}ActionHook.php` and `{Module}FilterHook.php` stubs.

### Menu Service (for feature modules)
Create `app/Services/{Module}MenuService.php` and register via `AdminFilterHook::ADMIN_MENU`.

5. Verify the module was created:
   - `ls -la modules/{modulename}/`
   - Check `modules_statuses.json` is updated
   - `php artisan module:list`

6. Suggest next steps:
   - Create CRUD: `php artisan module:make-crud {ModuleName} --model=ModelName --fields="field:type,..."`
   - Compile CSS: `php artisan module:compile-css {ModuleName} --watch`
   - Run tests: `php artisan test --filter={ModuleName}`

## Module Naming Convention
- Module folder: lowercase (e.g., `crm`, `docforge`)
- Namespace: PascalCase (e.g., `Modules\Crm`, `Modules\DocForge`)
- CSS prefix: short unique abbreviation

## Important
- Always use `--no-interaction` flag
- The module:make command automatically generates CLAUDE.md and .gitignore
- Reference CRM module for feature patterns, Starter26 for theme patterns
- ALL Tailwind classes in module views MUST use the chosen prefix
