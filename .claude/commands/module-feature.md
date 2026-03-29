---
description: "Add a new feature (model + service + controller + views + routes + tests) to an existing module"
allowed-tools: ["Bash", "Read", "Write", "Edit", "Glob", "Grep"]
---

# Add Feature to Module

Add a complete new feature to an existing LaraDashboard module, following the CRM architecture patterns.

## Instructions

1. Parse arguments: $ARGUMENTS
   - Expected: `{ModuleName} {FeatureName} {field1:type,field2:type,...}`
   - Example: `Crm Task title:string,description:text,status:select:Open|InProgress|Done,assignee_id:integer,due_date:date`

2. Research the target module:
   - Read the module's existing controllers, services, models for patterns
   - Check existing route structure in `routes/web.php`
   - Check MenuService for menu registration pattern
   - Read the module's ServiceProvider for registration pattern

3. Generate all files following CRM patterns:
   - **Migration**: `modules/{module}/database/migrations/` with `{module}_` table prefix
   - **Model**: with fillable, casts, relationships
   - **Service**: CRUD methods (paginate, create, update, delete)
   - **Controller**: Thin, delegates to service, uses FormRequest
   - **FormRequest**: Validation rules for all fields
   - **Policy**: Authorization for all CRUD actions
   - **Views**: index (with datatable), create, edit, show, form partial
   - **Livewire Datatable**: For the listing page
   - **Feature Test**: CRUD operations, auth, validation

4. Register everything:
   - Add routes to `routes/web.php` using `Route::resource()`
   - Register Policy in ServiceProvider via `Gate::policy()`
   - Register Livewire component in LivewireServiceProvider
   - Add menu item in MenuService

5. Post-generation:
   - Run `php artisan migrate`
   - Run `vendor/bin/pint --dirty`
   - Run tests: `php artisan test --filter={Feature}`
   - Show route list for the module

## Alternative: Use module:make-crud
If the feature is a straightforward CRUD, suggest using:
```bash
php artisan module:make-crud {Module} --model={Feature} --fields="{fields}"
```
This is faster for simple CRUDs. Use manual generation only for complex features.
