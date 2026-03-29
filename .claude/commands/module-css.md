---
description: "Compile module CSS assets or fix Tailwind prefix issues"
allowed-tools: ["Bash", "Read", "Write", "Edit", "Glob", "Grep"]
---

# Module CSS / Asset Management

Compile CSS assets for a module or diagnose Tailwind prefix issues.

## Instructions

Parse arguments: $ARGUMENTS

### Compile Assets
```bash
# Development (with watch)
php artisan module:compile-css {ModuleName} --watch

# Production (minified)
php artisan module:compile-css {ModuleName} --minify

# Distribution build (assets inside module dir)
php artisan module:compile-css {ModuleName} --minify --dist
```

### Diagnose CSS Issues
If classes aren't applying:

1. **Check prefix is configured** in `modules/{module}/resources/assets/css/app.css`:
   ```css
   @import "tailwindcss" prefix({prefix});
   ```

2. **Check Blade views use correct prefix**:
   ```bash
   # Find unprefixed Tailwind classes in module views
   grep -rn 'class="' modules/{module}/resources/views/ | grep -v '{prefix}:'
   ```

3. **Check vite.config.js** has correct paths:
   - Input: `modules/{Module}/resources/assets/css/app.css`
   - buildDirectory: `build-{module}`

4. **Check build output exists**: `ls public/build-{module}/`

5. **Check @vite directive** in module layout Blade file

### Tailwind Prefix Reference
| Module | Prefix |
|--------|--------|
| CRM | `crm` |
| Starter26 | `st` |
| DocForge | `df` |
| Ecom | `ecom` |
| Forum | `forum` |
| Review | `review` |
| Custom Forms | `cf` |
| LaraDashboard | `ld` |
| LaradashboardPro | `laradashboardpro` |

### Important Rules
- Module-specific classes: `{prefix}:utility` (e.g., `crm:py-4`)
- Shared component classes: NO prefix (e.g., `btn btn-primary`, `form-control`)
- Dark mode: `{prefix}:dark:utility` (e.g., `crm:dark:text-white`)
- Dynamic classes must be safelisted: `@source inline("...")`
