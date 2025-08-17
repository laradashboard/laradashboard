# Data Table Integration Documentation

This document describes the implementation of livewire-powergrid data tables in Lara Dashboard, following KISS and DRY principles while maintaining extensibility.

## Overview

The data table integration provides:
- Advanced filtering, sorting, and pagination
- Export functionality (Excel, CSV)
- Bulk actions
- Responsive design
- Extensibility through hooks and filters
- Consistent styling with existing dashboard theme

## Package Used

**livewire-powergrid** - https://livewire-powergrid.com/

This package provides powerful data table functionality built on top of Livewire 3.

## Installation

### 1. Install the Package

```bash
composer require power-components/livewire-powergrid
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag=livewire-powergrid-config
php artisan vendor:publish --tag=livewire-powergrid-lang
```

### 3. Compile Assets

```bash
npm install
npm run build
```

## Architecture

### Base Trait: `HasDataTableFeatures`

Located at `app/Concerns/HasDataTableFeatures.php`, this trait provides:

- Common data table setup and configuration
- Hook integration for extensibility
- Standard column formatters
- Bulk action handling
- Authorization checks
- Common styling rules

### Data Table Components

Each entity has its own PowerGrid component:

- `app/Livewire/UserTable.php` - User management
- `app/Livewire/RoleTable.php` - Role management
- `app/Livewire/PermissionTable.php` - Permission management
- `app/Livewire/ActionLogTable.php` - Action logs
- `app/Livewire/PostTable.php` - Post management
- `app/Livewire/TermTable.php` - Term/taxonomy management
- `app/Livewire/TranslationTable.php` - Translation management
- `app/Livewire/MediaTable.php` - Media management (with grid view)

## Creating a New Data Table

### 1. Create the PowerGrid Component

```bash
php artisan make:livewire YourEntityTable
```

### 2. Extend the Base Structure

```php
<?php

namespace App\Livewire;

use App\Concerns\HasDataTableFeatures;
use App\Models\YourModel;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

class YourEntityTable extends PowerGridComponent
{
    use HasDataTableFeatures, WithExport;

    public string $tableName = 'your-entity-table';

    public function datasource(): Builder
    {
        $query = YourModel::query()
            ->with(['relations'])
            ->select('your_models.*');

        return $this->applyQueryFilters($query);
    }

    public function columns(): array
    {
        return $this->getExtensibleColumns();
    }

    public function filters(): array
    {
        return $this->getExtensibleFilters();
    }

    public function actions(): array
    {
        return $this->getExtensibleActions();
    }

    // Implement abstract methods
    protected function getBaseColumns(): array
    {
        return [
            $this->formatIdColumn(),
            // Your columns here
            $this->formatCreatedAtColumn(),
        ];
    }

    protected function getBaseActions(): array
    {
        return [
            // Your actions here
        ];
    }

    protected function getBaseFilters(): array
    {
        return [
            // Your filters here
        ];
    }

    protected function applyBaseQueryFilters($query)
    {
        // Your query filters here
        return $query;
    }

    protected function getHookPrefix(): string
    {
        return 'your_entity';
    }

    protected function getRouteName(): string
    {
        return 'your-entities';
    }

    protected function getModelClass(): string
    {
        return YourModel::class;
    }

    protected function getPermissionPrefix(): string
    {
        return 'your_entity';
    }
}
```

### 3. Create the Blade View

Create `resources/views/livewire/your-entity-table.blade.php`:

```blade
<div>
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <x-breadcrumbs :breadcrumbs="['title' => __('Your Entities')]" />

        <div class="space-y-6">
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="table-td sm:py-5 flex flex-col md:flex-row justify-between items-center gap-3">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Your Entities') }}</h2>
                    
                    @if (auth()->user()->can('your_entity.create'))
                    <a href="{{ route('admin.your-entities.create') }}" class="btn-primary flex items-center gap-2">
                        <iconify-icon icon="feather:plus" height="16"></iconify-icon>
                        {{ __('New Entity') }}
                    </a>
                    @endif
                </div>

                <div class="p-4">
                    <livewire:power-grid:your-entity-table />
                </div>
            </div>
        </div>
    </div>
</div>
```

### 4. Update the Controller

```php
public function index(): Renderable
{
    $this->checkAuthorization(Auth::user(), ['your_entity.view']);

    // Check if PowerGrid is available, fallback to original implementation
    if (class_exists(\App\Livewire\YourEntityTable::class)) {
        return view('livewire.your-entity-table');
    }

    // Fallback to original implementation
    return view('backend.pages.your-entities.index', [
        // Original data
    ]);
}
```

## Extensibility Features

### Hook Integration

The data tables integrate with the existing hook system:

#### Available Hooks

- `{entity}_table_columns` - Modify columns
- `{entity}_table_actions` - Modify actions
- `{entity}_table_filters` - Modify filters
- `{entity}_table_query` - Modify query
- `{entity}_before_delete` - Before item deletion
- `{entity}_after_delete` - After item deletion

#### Example Usage

```php
// In a module or custom code
add_filter('user_table_columns', function ($columns) {
    $columns[] = Column::make('Custom Field', 'custom_field')
        ->sortable()
        ->searchable();
    
    return $columns;
});

add_filter('user_table_actions', function ($actions) {
    $actions[] = Button::make('custom-action', 'Custom Action')
        ->class('btn-sm btn-info')
        ->method('customAction');
    
    return $actions;
});
```

### Extending Functionality

#### Adding Custom Columns

```php
protected function getBaseColumns(): array
{
    $columns = [
        $this->formatIdColumn(),
        
        Column::make('Name', 'name')
            ->sortable()
            ->searchable(),
            
        // Custom column with complex formatting
        Column::make('Status', 'status')
            ->sortable()
            ->format(function ($value) {
                $colors = [
                    'active' => 'bg-green-100 text-green-800',
                    'inactive' => 'bg-red-100 text-red-800',
                ];
                
                $colorClass = $colors[$value] ?? 'bg-gray-100 text-gray-800';
                
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colorClass . '">' . ucfirst($value) . '</span>';
            }),
            
        $this->formatCreatedAtColumn(),
    ];
    
    return $columns;
}
```

#### Adding Custom Filters

```php
protected function getBaseFilters(): array
{
    return [
        Filter::inputText('search')
            ->placeholder('Search...'),
            
        Filter::select('status')
            ->dataSource([
                ['name' => 'Active', 'value' => 'active'],
                ['name' => 'Inactive', 'value' => 'inactive'],
            ])
            ->optionLabel('name')
            ->optionValue('value')
            ->placeholder('Filter by status'),
            
        Filter::datepicker('created_at')
            ->label('Created Date'),
            
        Filter::dateTimePicker('date_range')
            ->label('Date Range'),
    ];
}
```

#### Adding Custom Actions

```php
protected function getBaseActions(): array
{
    $actions = [];

    if ($this->canPerformAction('edit')) {
        $actions[] = Button::make('edit', 'Edit')
            ->class('btn-sm btn-secondary')
            ->icon('heroicon-o-pencil')
            ->route('admin.entities.edit', fn ($row) => $row->id);
    }

    if ($this->canPerformAction('custom')) {
        $actions[] = Button::make('custom', 'Custom Action')
            ->class('btn-sm btn-info')
            ->icon('heroicon-o-star')
            ->method('customAction')
            ->confirm('Are you sure?');
    }

    return $actions;
}

public function customAction(int $id): void
{
    $entity = YourModel::findOrFail($id);
    
    // Your custom logic here
    
    $this->dispatchBrowserEvent('show-message', [
        'type' => 'success',
        'message' => __('Custom action completed successfully.')
    ]);
}
```

## Features Included

### Export Functionality

All tables include export functionality:
- Excel (.xlsx)
- CSV (.csv)
- Customizable export data

### Bulk Actions

- Bulk delete with confirmation
- Extensible for custom bulk actions
- Authorization checks

### Search and Filtering

- Global search across multiple columns
- Column-specific filters
- Date range filtering
- Select dropdown filters

### Responsive Design

- Mobile-friendly layout
- Collapsible columns on small screens
- Touch-friendly controls

### Dark Mode Support

- Automatic dark mode detection
- Consistent styling with dashboard theme

## Styling and Theming

The data tables use Tailwind CSS classes consistent with the existing dashboard theme:

- **Primary buttons**: `btn-primary`
- **Secondary buttons**: `btn-secondary`
- **Danger buttons**: `btn-danger`
- **Info buttons**: `btn-info`
- **Success buttons**: `btn-success`

### Custom Styling

```php
// In your component
protected function getStylingRules(): array
{
    return [
        Rule::rows()
            ->when(fn ($row) => $row->status === 'inactive')
            ->setAttribute('class', 'bg-red-50 dark:bg-red-900/20'),
            
        Rule::rows()
            ->when(fn ($row) => $row->featured)
            ->setAttribute('class', 'bg-blue-50 dark:bg-blue-900/20'),
    ];
}
```

## Performance Considerations

### Pagination

- Default pagination: 15 items per page
- Configurable per-page options
- Efficient query pagination

### Eager Loading

```php
public function datasource(): Builder
{
    return YourModel::query()
        ->with(['relation1', 'relation2']) // Prevent N+1 queries
        ->select('your_models.*');
}
```

### Indexing

Ensure database indexes on frequently filtered/sorted columns:

```php
// In your migration
$table->index(['status', 'created_at']);
$table->index('name');
```

## Security

### Authorization

All actions include authorization checks:

```php
protected function canPerformAction(string $action, $item = null): bool
{
    $permission = $this->getPermissionPrefix() . '.' . $action;
    
    return auth()->user()->can($permission);
}
```

### CSRF Protection

- Automatic CSRF protection via Livewire
- Secure form submissions

### Input Validation

```php
public function customAction(int $id): void
{
    $this->validate([
        'checkboxValues' => 'required|array|min:1',
    ]);
    
    // Action logic
}
```

## Testing

### Unit Tests

```php
<?php

namespace Tests\Unit\Livewire;

use App\Livewire\UserTable;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class UserTableTest extends TestCase
{
    public function test_can_render_user_table()
    {
        $this->actingAs(User::factory()->create());
        
        Livewire::test(UserTable::class)
            ->assertStatus(200)
            ->assertSee('Users');
    }
    
    public function test_can_search_users()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        
        $this->actingAs($user);
        
        Livewire::test(UserTable::class)
            ->set('search', 'John')
            ->assertSee('John Doe');
    }
}
```

### Feature Tests

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserDataTableTest extends TestCase
{
    public function test_authorized_user_can_access_user_table()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('user.view');
        
        $response = $this->actingAs($user)
            ->get(route('admin.users.index'));
            
        $response->assertStatus(200);
        $response->assertSeeLivewire('user-table');
    }
}
```

## Troubleshooting

### Common Issues

1. **PowerGrid not loading**
   - Ensure assets are compiled: `npm run build`
   - Check Livewire configuration
   - Verify component registration

2. **Styling issues**
   - Ensure Tailwind CSS is properly configured
   - Check for CSS conflicts
   - Verify dark mode classes

3. **Performance issues**
   - Add database indexes
   - Implement eager loading
   - Consider pagination limits

### Debug Mode

Enable debug mode in your PowerGrid component:

```php
public function setUp(): array
{
    return [
        // ... other setup
        \PowerComponents\LivewirePowerGrid\Components\SetUp\Debug::make()
            ->enabled(config('app.debug')),
    ];
}
```

## Migration from Existing Tables

To migrate from existing Blade-based tables:

1. Create new PowerGrid component
2. Update controller to conditionally use new component
3. Test thoroughly
4. Remove old implementation once confirmed working

This ensures zero downtime during migration and provides fallback capability.

## Best Practices

1. **Follow naming conventions**: Use consistent naming for components and methods
2. **Implement authorization**: Always check permissions before actions
3. **Use hooks**: Leverage the hook system for extensibility
4. **Optimize queries**: Use eager loading and proper indexing
5. **Test thoroughly**: Write unit and feature tests
6. **Document customizations**: Document any custom modifications
7. **Follow DRY principle**: Use the base trait for common functionality
8. **Keep it simple**: Follow KISS principle for maintainability

## Conclusion

This data table integration provides a powerful, extensible, and consistent approach to data management in Lara Dashboard while maintaining the existing hook system and design patterns.

The implementation follows KISS and DRY principles, ensures backward compatibility, and provides a solid foundation for future enhancements.