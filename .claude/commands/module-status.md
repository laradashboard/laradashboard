---
description: "Show status of all modules or a specific module (enabled/disabled, version, routes, migrations)"
allowed-tools: ["Bash", "Read", "Grep", "Glob"]
---

# Module Status Check

Show comprehensive status of LaraDashboard modules.

## Instructions

1. Parse arguments: $ARGUMENTS (optional module name)

2. If specific module given:
   - Read `modules/{module}/module.json` for metadata
   - Check `modules_statuses.json` for enabled/disabled status
   - Check `storage/framework/modules_auto_disabled.json` for auto-disable reasons
   - Run `php artisan route:list --name={module}` for route count
   - Run `php artisan migrate:status` and filter for module migrations
   - List key files: controllers, models, services, views
   - Show version info

3. If no module specified:
   - Read `modules_statuses.json` for all module statuses
   - List all module directories with their versions from `module.json`
   - Show which are enabled vs disabled
   - Show auto-disabled modules and reasons

4. Present as a clean summary table
