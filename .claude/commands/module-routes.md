---
description: "Show all registered routes for a module"
allowed-tools: ["Bash", "Read", "Grep"]
---

# Show Module Routes

Display all registered routes for a LaraDashboard module.

## Instructions

1. Parse arguments: $ARGUMENTS (module name)
2. Run: `php artisan route:list --name={module_lowercase}`
3. If no routes found, also try: `php artisan route:list --path=admin/{module_lowercase}`
4. Show the routes in a clean format
5. If no routes found at all, check:
   - Is the module enabled in `modules_statuses.json`?
   - Does `modules/{module}/routes/web.php` exist and have routes?
   - Is RouteServiceProvider loading routes correctly?
