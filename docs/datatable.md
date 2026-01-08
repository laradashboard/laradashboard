# Datatable System Documentation

A comprehensive, enterprise-grade datatable system built on Livewire with powerful features including auto-generation, filtering, sorting, bulk actions, and extensible hooks.

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [Architecture Overview](#architecture-overview)
3. [Creating a Datatable](#creating-a-datatable)
4. [Configuration Properties](#configuration-properties)
5. [Column Headers](#column-headers)
6. [Custom Column Renderers](#custom-column-renderers)
7. [Filtering](#filtering)
8. [Sorting](#sorting)
9. [Searching](#searching)
10. [Pagination](#pagination)
11. [Bulk Actions](#bulk-actions)
12. [Action Buttons](#action-buttons)
13. [Permissions & Authorization](#permissions--authorization)
14. [Routes Configuration](#routes-configuration)
15. [Query Building](#query-building)
16. [Hooks & Events](#hooks--events)
17. [Customization Hooks](#customization-hooks)
18. [Auto-Generation](#auto-generation)
19. [Blade Components](#blade-components)
20. [Complete Examples](#complete-examples)
21. [File Reference](#file-reference)

---

## Quick Start

### 1. Create a Datatable Class

```php
<?php

namespace App\Livewire\Datatable;

use App\Models\Product;
use Spatie\QueryBuilder\QueryBuilder;

class ProductDatatable extends Datatable
{
    public string $model = Product::class;

    protected function getHeaders(): array
    {
        return [
            ['id' => 'name', 'title' => 'Name', 'sortable' => true, 'searchable' => true],
            ['id' => 'price', 'title' => 'Price', 'sortable' => true],
            ['id' => 'status', 'title' => 'Status'],
            ['id' => 'created_at', 'title' => 'Created'],
            ['id' => 'actions', 'title' => '', 'is_action' => true],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        return QueryBuilder::for($this->model);
    }

    protected function getRoutes(): array
    {
        return [
            'create' => 'products.create',
            'view'   => 'products.show',
            'edit'   => 'products.edit',
            'delete' => 'products.destroy',
        ];
    }
}
```

### 2. Use in Blade Template

```blade
<livewire:datatable.product-datatable />
```

---

## Architecture Overview

### Core Components

| Component | Location | Purpose |
|-----------|----------|---------|
| `Datatable` | `app/Livewire/Datatable/Datatable.php` | Base abstract class |
| `HasDatatableGenerator` | `app/Concerns/Datatable/` | Auto-generates columns from model |
| `HasDatatableDelete` | `app/Concerns/Datatable/` | Delete operations & bulk delete |
| `HasDatatableActionItems` | `app/Concerns/Datatable/` | Row action buttons |
| `DatatableHook` | `app/Enums/Hooks/` | Hook constants |

### Class Hierarchy

```
Livewire\Component
└── App\Livewire\Datatable\Datatable (abstract)
    ├── App\Livewire\Datatable\UserDatatable
    ├── App\Livewire\Datatable\PostDatatable
    └── Your custom datatables...

... for module
Modules\Crm\Livewire\Components\CrmDatatable (base)
├── ContactDatatable
├── TaskDatatable
└── ...
```

---

## Creating a Datatable

### Basic Structure

```php
<?php

namespace App\Livewire\Datatable;

use App\Models\YourModel;
use Spatie\QueryBuilder\QueryBuilder;

class YourModelDatatable extends Datatable
{
    // Required: Specify the model
    public string $model = YourModel::class;

    // Define columns
    protected function getHeaders(): array
    {
        return [
            // Your column definitions
        ];
    }

    // Build the query
    protected function buildQuery(): QueryBuilder
    {
        return QueryBuilder::for($this->model);
    }

    // Define CRUD routes
    protected function getRoutes(): array
    {
        return [
            'create' => 'your-model.create',
            'view'   => 'your-model.show',
            'edit'   => 'your-model.edit',
            'delete' => 'your-model.destroy',
        ];
    }
}
```

### Using Artisan (Recommended)

```bash
php artisan make:livewire Datatable/ProductDatatable
```

Then extend the base `Datatable` class.

---

## Configuration Properties

### Display & UI Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$model` | `string` | `''` | Model class name (required) |
| `$search` | `string` | `''` | Current search term |
| `$searchbarPlaceholder` | `string` | `''` | Search input placeholder |
| `$noResultsMessage` | `string` | `''` | Empty state message |
| `$customNoResultsMessage` | `string` | `''` | Custom empty state HTML |

### Pagination Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$page` | `int` | `1` | Current page number |
| `$perPage` | `int` | `10` | Items per page (URL-bound) |
| `$perPageOptions` | `array` | `[10,20,50,100,'All']` | Per-page dropdown options |
| `$paginateOnEachSlide` | `int` | `0` | Pagination slide count |
| `$enablePagination` | `bool` | `true` | Show pagination controls |

### Sorting Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$sort` | `string` | `'created_at'` | Default sort column |
| `$direction` | `string` | `'desc'` | Sort direction (`asc`/`desc`) |

### Feature Toggles

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$enableCheckbox` | `bool` | `true` | Show row checkboxes |
| `$enableBulkActions` | `bool` | `true` | Enable bulk operations |
| `$enableActionColumn` | `bool` | `true` | Show actions column |
| `$showCreateButton` | `bool` | `false` | Show "New" button |
| `$enableLivewireDelete` | `bool` | `true` | Use Livewire for deletions |

### Selection & Filters

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$selectedItems` | `array` | `[]` | Selected row IDs |
| `$filters` | `array` | `[]` | Active filter values |
| `$customFilters` | `mixed` | `null` | Custom filter component |

### Permissions & Routes

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$permissions` | `array` | `[]` | Required permission strings |
| `$disabledRoutes` | `array` | `[]` | Routes to disable (`view`, `edit`, `delete`) |

### Auto-Generation Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$excludedColumns` | `array` | `[...]` | Columns to exclude |
| `$relationships` | `array` | `[]` | Relations to eager load |
| `$maxColumns` | `int` | `8` | Max auto-generated columns |
| `$searchableColumns` | `array` | `[]` | Searchable column list |

### Query String Binding

```php
public array $queryString = [
    'search' => ['except' => ''],
    'sort' => ['except' => 'created_at'],
    'direction' => ['except' => 'desc'],
    'perPage' => ['except' => 10],
    // Add your filter properties here
];
```

---

## Column Headers

### Header Definition Structure

```php
protected function getHeaders(): array
{
    return [
        [
            'id' => 'column_name',           // Required: Column identifier
            'title' => 'Display Title',       // Required: Header label
            'sortBy' => 'database_column',    // Column for sorting (defaults to id)
            'sortable' => true,               // Enable sorting
            'searchable' => true,             // Include in search
            'width' => '150px',               // Fixed width
            'align' => 'center',              // Text alignment: left|center|right
            'renderer' => 'customMethod',     // Custom render method name
            'is_action' => false,             // Is this the actions column?
        ],
    ];
}
```

### Header Properties Reference

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `id` | `string` | - | Column identifier (required) |
| `title` | `string` | - | Display title (required) |
| `sortBy` | `string` | `id` | Database column for sorting |
| `sortable` | `bool` | `false` | Allow sorting |
| `searchable` | `bool` | `false` | Include in search |
| `width` | `string` | `null` | Column width (e.g., `'20%'`, `'150px'`) |
| `align` | `string` | `'left'` | Text alignment |
| `renderer` | `string` | `null` | Custom render method |
| `renderContent` | `string` | `null` | Blade include path |
| `renderRawContent` | `string` | `null` | Raw HTML content |
| `is_action` | `bool` | `false` | Actions column flag |

### Example: Complete Headers

```php
protected function getHeaders(): array
{
    return [
        [
            'id' => 'id',
            'title' => 'ID',
            'sortable' => true,
            'width' => '80px',
            'align' => 'center',
        ],
        [
            'id' => 'name',
            'title' => __('Name'),
            'sortable' => true,
            'searchable' => true,
        ],
        [
            'id' => 'email',
            'title' => __('Email'),
            'sortable' => true,
            'searchable' => true,
        ],
        [
            'id' => 'status',
            'title' => __('Status'),
            'sortable' => true,
            'renderer' => 'renderStatusBadge',
        ],
        [
            'id' => 'role.name',
            'title' => __('Role'),
            'sortBy' => 'role_id',
        ],
        [
            'id' => 'created_at',
            'title' => __('Created'),
            'sortable' => true,
            'width' => '180px',
        ],
        [
            'id' => 'actions',
            'title' => '',
            'is_action' => true,
            'width' => '120px',
        ],
    ];
}
```

---

## Custom Column Renderers

### Auto-Discovery Convention

Create a method named `render{PascalCaseColumnId}Column()`:

```php
// For column with id 'status'
public function renderStatusColumn($item): string
{
    $colors = [
        'active' => 'green',
        'pending' => 'yellow',
        'inactive' => 'red',
    ];

    $color = $colors[$item->status] ?? 'gray';

    return '<span class="badge badge-' . $color . '">' . ucfirst($item->status) . '</span>';
}

// For column with id 'user_name'
public function renderUserNameColumn($item): string
{
    return '<strong>' . e($item->user_name) . '</strong>';
}
```

### Explicit Renderer Declaration

```php
protected function getHeaders(): array
{
    return [
        [
            'id' => 'price',
            'title' => 'Price',
            'renderer' => 'formatPrice',  // Explicit method name
        ],
    ];
}

public function formatPrice($item): string
{
    return '$' . number_format($item->price, 2);
}
```

### Built-in Renderers

The base class includes these renderers:

```php
// ID column with link to view
public function renderIdCell($item): string

// Formatted date/time columns
public function renderCreatedAtColumn($item): string
public function renderUpdatedAtColumn($item): string
public function renderTimestampColumn($item, $column): string
```

### Rendering with Blade Components

```php
protected function getHeaders(): array
{
    return [
        [
            'id' => 'avatar',
            'title' => 'Avatar',
            'renderContent' => 'renderAvatarCell',
        ],
    ];
}

public function renderAvatarCell($item): string
{
    return view('components.avatar', ['user' => $item])->render();
}
```

### Raw HTML Content

```php
[
    'id' => 'indicator',
    'title' => '',
    'renderRawContent' => '<div class="w-3 h-3 bg-green-500 rounded-full"></div>',
]
```

---

## Filtering

### Defining Filters

```php
// Add filter property
public string $status = '';

// Register in queryString
public array $queryString = [
    ...parent::QUERY_STRING_DEFAULTS,
    'status' => ['except' => ''],
];

// Define filter configuration
protected function getFilters(): array
{
    return [
        [
            'id' => 'status',
            'label' => __('Status'),
            'filterLabel' => __('Filter by Status'),
            'icon' => 'lucide:filter',
            'allLabel' => __('All Statuses'),
            'options' => [
                'active' => __('Active'),
                'pending' => __('Pending'),
                'inactive' => __('Inactive'),
            ],
            'selected' => $this->status,
        ],
    ];
}
```

### Filter Configuration Options

| Option | Type | Required | Description |
|--------|------|----------|-------------|
| `id` | `string` | Yes | Property name to bind |
| `label` | `string` | Yes | Display label |
| `filterLabel` | `string` | No | Dropdown button label |
| `icon` | `string` | No | Icon name (e.g., `'lucide:filter'`) |
| `allLabel` | `string` | No | "All" option text |
| `options` | `array` | Yes | Available options |
| `selected` | `mixed` | Yes | Currently selected value |
| `route` | `string` | No | Route for non-Livewire mode |

### Options Formats

```php
// Simple format
'options' => [
    'value1' => 'Label 1',
    'value2' => 'Label 2',
]

// Advanced format (when value differs from key)
'options' => [
    'key1' => ['label' => 'Label 1', 'value' => 'actual_value_1'],
    'key2' => ['label' => 'Label 2', 'value' => 'actual_value_2'],
]
```

### Multiple Filters Example

```php
public string $status = '';
public string $category = '';
public string $author = '';

protected function getFilters(): array
{
    return [
        [
            'id' => 'status',
            'label' => __('Status'),
            'allLabel' => __('All'),
            'options' => Post::getStatusOptions(),
            'selected' => $this->status,
        ],
        [
            'id' => 'category',
            'label' => __('Category'),
            'allLabel' => __('All Categories'),
            'options' => Category::pluck('name', 'id')->toArray(),
            'selected' => $this->category,
        ],
        [
            'id' => 'author',
            'label' => __('Author'),
            'allLabel' => __('All Authors'),
            'options' => User::pluck('name', 'id')->toArray(),
            'selected' => $this->author,
        ],
    ];
}
```

### Applying Filters in Query

```php
protected function buildQuery(): QueryBuilder
{
    return QueryBuilder::for($this->model)
        ->when($this->status, fn($q) => $q->where('status', $this->status))
        ->when($this->category, fn($q) => $q->where('category_id', $this->category))
        ->when($this->author, fn($q) => $q->where('author_id', $this->author));
}
```

### Filter Lifecycle Hooks

```php
// Auto-called when filter changes (updatingPropertyName)
public function updatingStatus(): void
{
    $this->resetPage();
}

// Check if any filters are active
public function hasActiveFilters(): bool

// Clear all filters
public function clearFilters(): void
```

---

## Sorting

### Enable Sorting on Columns

```php
[
    'id' => 'name',
    'title' => 'Name',
    'sortable' => true,
    'sortBy' => 'name',  // Optional: defaults to 'id'
]
```

### Default Sort Configuration

```php
public string $sort = 'created_at';
public string $direction = 'desc';
```

### Custom Sort Logic

```php
public function sortQuery(QueryBuilder $query): QueryBuilder
{
    if ($this->sort === 'custom_column') {
        return $query->orderByRaw('CASE WHEN status = "active" THEN 0 ELSE 1 END')
                     ->orderBy('name', $this->direction);
    }

    return $query->orderBy($this->sort, $this->direction);
}
```

### Sorting by Relationships

```php
[
    'id' => 'category.name',
    'title' => 'Category',
    'sortable' => true,
    'sortBy' => 'category_id',  // Sort by foreign key
]
```

---

## Searching

### Enable Search on Columns

```php
[
    'id' => 'name',
    'title' => 'Name',
    'searchable' => true,
]
```

### Custom Search Placeholder

```php
public string $searchbarPlaceholder = 'Search products by name, SKU, or description...';
```

### Implementing Search in Query

```php
protected function buildQuery(): QueryBuilder
{
    return QueryBuilder::for($this->model)
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            });
        });
}
```

### Search Keyboard Shortcut

The searchbar component supports `Cmd/Ctrl + K` to focus.

---

## Pagination

### Configuration

```php
public int $perPage = 10;
public array $perPageOptions = [10, 20, 50, 100, 'All'];
public bool $enablePagination = true;
```

### Custom Per-Page Options

```php
public array $perPageOptions = [5, 10, 25, 50];
```

### Disable Pagination

```php
public bool $enablePagination = false;
```

### Reset Page on Filter/Search

```php
public function updatingSearch(): void
{
    $this->resetPage();
}

public function updatingStatus(): void
{
    $this->resetPage();
}
```

---

## Bulk Actions

### Enable Bulk Actions

```php
public bool $enableBulkActions = true;
public bool $enableCheckbox = true;
```

### Built-in Bulk Delete

Bulk delete is built-in. Users can:
1. Select individual rows via checkboxes
2. Select all on current page
3. Select all across all pages
4. Click "Delete" to open confirmation modal

### Custom Bulk Delete Handler

```php
protected function handleBulkDelete(array $ids): void
{
    // Custom logic before delete
    $items = YourModel::whereIn('id', $ids)->get();

    foreach ($items as $item) {
        // Custom cleanup
        $item->attachments()->delete();
        $item->delete();
    }
}
```

### Bulk Delete Confirmation Message

Override in the view or use translation:

```php
__('Are you sure you want to delete :count items?')
```

---

## Action Buttons

### Default Actions

The system provides three default actions: **View**, **Edit**, **Delete**.

### Configure Action Labels & Icons

```php
// Properties from HasDatatableActionItems trait
public string $actionColumnLabel = '';
public bool $showActionColumnLabel = false;
public string $actionColumnIcon = 'lucide:more-horizontal';

public string $viewButtonLabel = '';
public string $viewButtonIcon = 'lucide:eye';

public string $editButtonLabel = '';
public string $editButtonIcon = 'lucide:pencil';

public string $deleteButtonLabel = '';
public string $deleteButtonIcon = 'lucide:trash';

// Set custom labels
public function mount(): void
{
    parent::mount();
    $this->setActionLabels();
}

protected function setActionLabels(): void
{
    $this->viewButtonLabel = __('View');
    $this->editButtonLabel = __('Edit');
    $this->deleteButtonLabel = __('Delete');
}
```

### Disable Specific Actions

```php
public array $disabledRoutes = ['delete'];  // Disable delete action
public array $disabledRoutes = ['view', 'edit'];  // Disable view and edit
```

### Conditional Action Display

```php
public function showActionItems($item): bool
{
    // Hide actions for certain items
    if ($item->is_system) {
        return false;
    }

    return true;
}
```

### Add Custom Actions

```php
// Insert before View button
public function renderBeforeActionView($item): string
{
    return '<a href="' . route('items.duplicate', $item) . '" class="btn btn-sm">
        <x-lucide-copy class="w-4 h-4" />
    </a>';
}

// Insert after View button
public function renderAfterActionView($item): string
{
    return '';
}

// Insert after Edit button
public function renderAfterActionEdit($item): string
{
    return '<a href="' . route('items.export', $item) . '" class="btn btn-sm">
        <x-lucide-download class="w-4 h-4" />
    </a>';
}

// Insert after Delete button
public function renderAfterActionDelete($item): string
{
    return '';
}
```

### Protect Rows from Deletion

Add `is_deletable` attribute to your model:

```php
// In your Model
public function getIsDeletableAttribute(): bool
{
    return $this->status !== 'protected' && !$this->is_system;
}
```

---

## Permissions & Authorization

### Define Required Permissions

```php
protected function getPermissions(): array
{
    return [
        'view' => 'products.view',
        'create' => 'products.create',
        'edit' => 'products.update',
        'delete' => 'products.delete',
    ];
}
```

### Check Permissions for Actions

```php
public function getActionCellPermissions($item): array
{
    return [
        'view' => auth()->user()->can('view', $item),
        'edit' => auth()->user()->can('update', $item),
        'delete' => auth()->user()->can('delete', $item),
    ];
}
```

### Row-Level Permissions

```php
public function getActionCellPermissions($item): array
{
    $user = auth()->user();

    return [
        'view' => true,
        'edit' => $item->author_id === $user->id || $user->isAdmin(),
        'delete' => $user->isAdmin() && $item->isDeletable(),
    ];
}
```

---

## Routes Configuration

### Define CRUD Routes

```php
protected function getRoutes(): array
{
    return [
        'create' => 'products.create',
        'view'   => 'products.show',
        'edit'   => 'products.edit',
        'delete' => 'products.destroy',
    ];
}
```

### Route Parameters

```php
// Base parameters for all routes
protected function getRouteParameters(): array
{
    return [
        'tenant' => tenant('id'),
    ];
}

// Parameters for specific item
protected function getItemRouteParameters($item): array
{
    return [
        'product' => $item->id,
        'tenant' => tenant('id'),
    ];
}
```

### Custom Route URLs

```php
public function getCreateRouteUrl(): string
{
    return route('products.create', $this->getRouteParameters());
}

public function getViewRouteUrl($item): string
{
    return route('products.show', $this->getItemRouteParameters($item));
}

public function getEditRouteUrl($item): string
{
    return route('products.edit', $this->getItemRouteParameters($item));
}

public function getDeleteRouteUrl($item): string
{
    return route('products.destroy', $this->getItemRouteParameters($item));
}
```

### Module-Specific Routes

For modules, you might need to override the route prefix:

```php
protected function getRoutePrefix(): string
{
    return 'crm.';  // All routes become crm.products.create, etc.
}
```

---

## Query Building

### Basic Query

```php
protected function buildQuery(): QueryBuilder
{
    return QueryBuilder::for($this->model);
}
```

### With Eager Loading

```php
public array $relationships = ['author', 'category', 'tags'];

protected function buildQuery(): QueryBuilder
{
    return QueryBuilder::for($this->model)
        ->with($this->relationships);
}
```

### With Filters and Search

```php
protected function buildQuery(): QueryBuilder
{
    return QueryBuilder::for($this->model)
        ->with(['author', 'category'])
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('content', 'like', "%{$this->search}%");
            });
        })
        ->when($this->status, fn($q) => $q->where('status', $this->status))
        ->when($this->category, fn($q) => $q->where('category_id', $this->category));
}
```

### With Aggregates

```php
protected function buildQuery(): QueryBuilder
{
    return QueryBuilder::for($this->model)
        ->withCount('comments')
        ->withSum('orderItems', 'quantity');
}
```

### With Joins

```php
protected function buildQuery(): QueryBuilder
{
    return QueryBuilder::for($this->model)
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->select('products.*', 'categories.name as category_name');
}
```

### Scope Queries

```php
protected function buildQuery(): QueryBuilder
{
    return QueryBuilder::for($this->model)
        ->active()
        ->forCurrentTenant()
        ->latest();
}
```

---

## Hooks & Events

### Available Hook Constants

```php
use App\Enums\Hooks\DatatableHook;

DatatableHook::BEFORE_SEARCHBOX          // Before search UI
DatatableHook::AFTER_SEARCHBOX           // After search UI
DatatableHook::DATATABLE_MOUNTED         // After mount

// Delete hooks
DatatableHook::BEFORE_DELETE_ACTION      // Before delete (action)
DatatableHook::BEFORE_DELETE_FILTER      // Before delete (filter)
DatatableHook::AFTER_DELETE_ACTION       // After delete (action)
DatatableHook::AFTER_DELETE_FILTER       // After delete (filter)

// Bulk delete hooks
DatatableHook::BEFORE_BULK_DELETE_ACTION
DatatableHook::BEFORE_BULK_DELETE_FILTER
DatatableHook::AFTER_BULK_DELETE_ACTION
DatatableHook::AFTER_BULK_DELETE_FILTER
```

### Using Hooks

```php
use App\Concerns\Hookable;

class ProductDatatable extends Datatable
{
    use Hookable;

    public function mount(): void
    {
        parent::mount();

        // Register hook listeners
        $this->registerHook(DatatableHook::BEFORE_DELETE_ACTION, function ($item) {
            // Log deletion attempt
            activity()->log("Attempting to delete product: {$item->name}");
        });

        $this->registerHook(DatatableHook::AFTER_DELETE_ACTION, function ($item) {
            // Clear cache after deletion
            Cache::forget("product:{$item->id}");
        });
    }
}
```

### Custom Delete Handlers

```php
// Single item delete
protected function handleRowDelete(Model $item): void
{
    // Custom cleanup
    $item->images()->delete();
    Storage::deleteDirectory("products/{$item->id}");

    $item->delete();
}

// Bulk delete
protected function handleBulkDelete(array $ids): void
{
    $items = Product::whereIn('id', $ids)->get();

    foreach ($items as $item) {
        $this->handleRowDelete($item);
    }
}
```

---

## Customization Hooks

### UI Injection Points

```php
// Before searchbar
public function renderBeforeSearchbar(): string
{
    return '<div class="alert alert-info mb-4">Select items to perform bulk actions.</div>';
}

// After searchbar
public function renderAfterSearchbar(): string
{
    return '<div class="text-sm text-gray-500">Showing active products only</div>';
}

// After each row
public function renderAfterRow($item): string
{
    if ($item->hasWarning()) {
        return '<tr class="bg-yellow-50"><td colspan="100%">
            <span class="text-yellow-700">' . $item->warning_message . '</span>
        </td></tr>';
    }
    return '';
}
```

### Custom Create Button

```php
public function getCustomNewResourceLink(): ?string
{
    return '<a href="' . route('products.wizard') . '" class="btn btn-primary">
        <x-lucide-wand class="w-4 h-4 mr-2" />
        Create with Wizard
    </a>';
}
```

---

## Auto-Generation

### Enable Auto-Generation

The `HasDatatableGenerator` trait can automatically generate columns from your model's database schema.

```php
class ProductDatatable extends Datatable
{
    // Don't override getHeaders() - let it auto-generate

    public array $excludedColumns = ['password', 'remember_token', 'secret_key'];
    public int $maxColumns = 6;
}
```

### Excluded Columns (Default)

```php
$excludedColumns = [
    'password',
    'remember_token',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'stripe_id',
    'pm_type',
    'pm_last_four',
    'trial_ends_at',
];
```

### Auto-Detection Features

- **Sortable**: Columns with indexes
- **Searchable**: String columns (varchar, text)
- **Renderers**: Auto-discovers `render{Column}Column()` methods

---

## Blade Components

### Main Datatable Component

**Location**: `resources/views/components/datatable/datatable.blade.php`

```blade
<x-datatable
    :headers="$headers"
    :items="$items"
    :searchable="true"
    :bulk-actions="true"
    :checkboxes="true"
    :pagination="true"
    wire:model="selectedItems"
/>
```

### Searchbar Component

**Location**: `resources/views/components/datatable/searchbar.blade.php`

Features:
- Live search (Livewire mode)
- Keyboard shortcut (Cmd/Ctrl + K)
- Clear button
- Custom placeholder

### Filters Component

**Location**: `resources/views/components/datatable/responsive-filters.blade.php`

Features:
- Desktop: Individual dropdown buttons
- Mobile: Collapsed filter menu
- Active filter indicators
- Clear all button

### Loading Skeleton

**Location**: `resources/views/components/datatable/skeleton.blade.php`

```blade
<x-datatable.skeleton :columns="6" :rows="10" />
```

---

## Complete Examples

### Example 1: User Datatable with Role Filter

```php
<?php

namespace App\Livewire\Datatable;

use App\Models\User;
use Spatie\QueryBuilder\QueryBuilder;

class UserDatatable extends Datatable
{
    public string $model = User::class;
    public string $role = '';

    public array $queryString = [
        'search' => ['except' => ''],
        'sort' => ['except' => 'created_at'],
        'direction' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
        'role' => ['except' => ''],
    ];

    public array $relationships = ['roles'];

    protected function getHeaders(): array
    {
        return [
            ['id' => 'id', 'title' => 'ID', 'sortable' => true, 'width' => '80px'],
            ['id' => 'name', 'title' => __('Name'), 'sortable' => true, 'searchable' => true],
            ['id' => 'email', 'title' => __('Email'), 'sortable' => true, 'searchable' => true],
            ['id' => 'role', 'title' => __('Role'), 'renderer' => 'renderRoleBadge'],
            ['id' => 'created_at', 'title' => __('Joined'), 'sortable' => true],
            ['id' => 'actions', 'title' => '', 'is_action' => true],
        ];
    }

    protected function getFilters(): array
    {
        return [
            [
                'id' => 'role',
                'label' => __('Role'),
                'filterLabel' => __('Filter by Role'),
                'icon' => 'lucide:shield',
                'allLabel' => __('All Roles'),
                'options' => \Spatie\Permission\Models\Role::pluck('name', 'name')->toArray(),
                'selected' => $this->role,
            ],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        return QueryBuilder::for($this->model)
            ->with($this->relationships)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->role, fn($q) => $q->role($this->role));
    }

    public function renderRoleBadge($item): string
    {
        $roles = $item->roles->pluck('name')->implode(', ');
        return '<span class="badge badge-primary">' . $roles . '</span>';
    }

    protected function getRoutes(): array
    {
        return [
            'create' => 'backend.users.create',
            'view' => 'backend.users.show',
            'edit' => 'backend.users.edit',
            'delete' => 'backend.users.destroy',
        ];
    }
}
```

### Example 2: E-commerce Product Datatable

```php
<?php

namespace Modules\Shop\Livewire;

use App\Livewire\Datatable\Datatable;
use Modules\Shop\Models\Product;
use Spatie\QueryBuilder\QueryBuilder;

class ProductDatatable extends Datatable
{
    public string $model = Product::class;
    public string $status = '';
    public string $category = '';
    public string $stock = '';

    public bool $showCreateButton = true;

    public array $queryString = [
        'search' => ['except' => ''],
        'sort' => ['except' => 'created_at'],
        'direction' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
        'status' => ['except' => ''],
        'category' => ['except' => ''],
        'stock' => ['except' => ''],
    ];

    protected function getHeaders(): array
    {
        return [
            ['id' => 'image', 'title' => '', 'width' => '60px', 'renderer' => 'renderImage'],
            ['id' => 'name', 'title' => __('Product'), 'sortable' => true, 'searchable' => true],
            ['id' => 'sku', 'title' => __('SKU'), 'sortable' => true, 'searchable' => true],
            ['id' => 'price', 'title' => __('Price'), 'sortable' => true, 'align' => 'right'],
            ['id' => 'stock_quantity', 'title' => __('Stock'), 'sortable' => true, 'align' => 'center'],
            ['id' => 'status', 'title' => __('Status'), 'sortable' => true],
            ['id' => 'actions', 'title' => '', 'is_action' => true],
        ];
    }

    protected function getFilters(): array
    {
        return [
            [
                'id' => 'status',
                'label' => __('Status'),
                'allLabel' => __('All'),
                'options' => [
                    'active' => __('Active'),
                    'draft' => __('Draft'),
                    'archived' => __('Archived'),
                ],
                'selected' => $this->status,
            ],
            [
                'id' => 'category',
                'label' => __('Category'),
                'allLabel' => __('All Categories'),
                'options' => Category::pluck('name', 'id')->toArray(),
                'selected' => $this->category,
            ],
            [
                'id' => 'stock',
                'label' => __('Stock'),
                'allLabel' => __('All'),
                'options' => [
                    'in_stock' => __('In Stock'),
                    'low_stock' => __('Low Stock'),
                    'out_of_stock' => __('Out of Stock'),
                ],
                'selected' => $this->stock,
            ],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        return QueryBuilder::for($this->model)
            ->with(['category', 'images'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('sku', 'like', "%{$this->search}%")
                      ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->category, fn($q) => $q->where('category_id', $this->category))
            ->when($this->stock, function ($q) {
                match ($this->stock) {
                    'in_stock' => $q->where('stock_quantity', '>', 10),
                    'low_stock' => $q->whereBetween('stock_quantity', [1, 10]),
                    'out_of_stock' => $q->where('stock_quantity', 0),
                };
            });
    }

    public function renderImage($item): string
    {
        $image = $item->images->first();
        $src = $image ? $image->url : asset('images/placeholder.png');

        return '<img src="' . $src . '" class="w-10 h-10 object-cover rounded" />';
    }

    public function renderPriceColumn($item): string
    {
        return '<span class="font-mono">$' . number_format($item->price, 2) . '</span>';
    }

    public function renderStockQuantityColumn($item): string
    {
        $color = match (true) {
            $item->stock_quantity === 0 => 'red',
            $item->stock_quantity <= 10 => 'yellow',
            default => 'green',
        };

        return '<span class="badge badge-' . $color . '">' . $item->stock_quantity . '</span>';
    }

    public function renderStatusColumn($item): string
    {
        $colors = [
            'active' => 'green',
            'draft' => 'gray',
            'archived' => 'red',
        ];

        return '<span class="badge badge-' . ($colors[$item->status] ?? 'gray') . '">' .
               ucfirst($item->status) . '</span>';
    }

    protected function getRoutes(): array
    {
        return [
            'create' => 'shop.products.create',
            'view' => 'shop.products.show',
            'edit' => 'shop.products.edit',
            'delete' => 'shop.products.destroy',
        ];
    }
}
```

### Example 3: Activity Log Datatable (Read-Only)

```php
<?php

namespace App\Livewire\Datatable;

use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogDatatable extends Datatable
{
    public string $model = Activity::class;
    public string $logName = '';
    public string $event = '';

    // Disable all actions - read-only table
    public array $disabledRoutes = ['create', 'view', 'edit', 'delete'];
    public bool $enableCheckbox = false;
    public bool $enableBulkActions = false;
    public bool $enableActionColumn = false;

    protected function getHeaders(): array
    {
        return [
            ['id' => 'created_at', 'title' => __('Date'), 'sortable' => true, 'width' => '180px'],
            ['id' => 'log_name', 'title' => __('Log'), 'sortable' => true],
            ['id' => 'event', 'title' => __('Event'), 'sortable' => true],
            ['id' => 'description', 'title' => __('Description')],
            ['id' => 'causer', 'title' => __('User')],
            ['id' => 'subject', 'title' => __('Subject')],
        ];
    }

    protected function getFilters(): array
    {
        return [
            [
                'id' => 'logName',
                'label' => __('Log'),
                'allLabel' => __('All Logs'),
                'options' => Activity::distinct('log_name')->pluck('log_name', 'log_name')->toArray(),
                'selected' => $this->logName,
            ],
            [
                'id' => 'event',
                'label' => __('Event'),
                'allLabel' => __('All Events'),
                'options' => [
                    'created' => __('Created'),
                    'updated' => __('Updated'),
                    'deleted' => __('Deleted'),
                ],
                'selected' => $this->event,
            ],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        return QueryBuilder::for($this->model)
            ->with(['causer', 'subject'])
            ->when($this->logName, fn($q) => $q->where('log_name', $this->logName))
            ->when($this->event, fn($q) => $q->where('event', $this->event))
            ->latest();
    }

    public function renderCauserColumn($item): string
    {
        if (!$item->causer) {
            return '<span class="text-gray-400">System</span>';
        }

        return e($item->causer->name);
    }

    public function renderSubjectColumn($item): string
    {
        if (!$item->subject) {
            return '<span class="text-gray-400">Deleted</span>';
        }

        $type = class_basename($item->subject_type);
        return "{$type} #{$item->subject_id}";
    }
}
```

---

## File Reference

### Core Files

| File | Purpose |
|------|---------|
| `app/Livewire/Datatable/Datatable.php` | Base abstract class |
| `app/Concerns/Datatable/HasDatatableGenerator.php` | Auto-generation trait |
| `app/Concerns/Datatable/HasDatatableDelete.php` | Delete operations trait |
| `app/Concerns/Datatable/HasDatatableActionItems.php` | Action buttons trait |
| `app/Enums/Hooks/DatatableHook.php` | Hook constants |

### View Files

| File | Purpose |
|------|---------|
| `resources/views/components/datatable/datatable.blade.php` | Main layout |
| `resources/views/components/datatable/searchbar.blade.php` | Search input |
| `resources/views/components/datatable/responsive-filters.blade.php` | Filter UI |
| `resources/views/components/datatable/skeleton.blade.php` | Loading state |
| `resources/views/backend/livewire/datatable/datatable.blade.php` | Livewire wrapper |
| `resources/views/backend/livewire/datatable/action-buttons.blade.php` | Action buttons |

### Existing Datatables

#### Core Application
- `app/Livewire/Datatable/UserDatatable.php`
- `app/Livewire/Datatable/RoleDatatable.php`
- `app/Livewire/Datatable/PermissionDatatable.php`
- `app/Livewire/Datatable/PostDatatable.php`
- `app/Livewire/Datatable/TermDatatable.php`
- `app/Livewire/Datatable/ActionLogDatatable.php`
- `app/Livewire/Datatable/NotificationDatatable.php`
- `app/Livewire/Datatable/EmailTemplateDatatable.php`
- `app/Livewire/Datatable/EmailConnectionDatatable.php`
- `app/Livewire/Datatable/ModuleDatatable.php`

#### Module examples
- `modules/crm/app/Livewire/Components/CrmDatatable.php` (base)
- `modules/crm/app/Livewire/Components/ContactDatatable.php`
- `modules/crm/app/Livewire/Components/ContactGroupDatatable.php`
- `modules/crm/app/Livewire/Components/TaskDatatable.php`
- `modules/crm/app/Livewire/Components/DealDatatable.php`
- `modules/customform/app/Livewire/Components/CustomFormDatatable.php` (base)
- `modules/customform/app/Livewire/Components/FormDatatable.php`
- `modules/customform/app/Livewire/Components/SubmissionDatatable.php`

---

## Best Practices

### 1. Always Eager Load Relationships

```php
public array $relationships = ['author', 'category'];

protected function buildQuery(): QueryBuilder
{
    return QueryBuilder::for($this->model)
        ->with($this->relationships);
}
```

### 2. Reset Page on Filter Changes

```php
public function updatingStatus(): void
{
    $this->resetPage();
}
```

### 3. Use Translation Strings

```php
['id' => 'name', 'title' => __('Name')]
```

### 4. Keep Queries Efficient

```php
// Good: Select only needed columns
->select(['id', 'name', 'email', 'status', 'created_at'])

// Good: Use indexes for sorting
['sortBy' => 'created_at']  // Assuming index exists
```

### 5. Leverage Auto-Discovery

Name render methods consistently:

```php
// Column: status → Method: renderStatusColumn
// Column: user_name → Method: renderUserNameColumn
// Column: created_at → Method: renderCreatedAtColumn
```

### 6. Use Proper Permissions

```php
protected function getPermissions(): array
{
    return [
        'view' => 'products.view',
        'create' => 'products.create',
        'edit' => 'products.update',
        'delete' => 'products.delete',
    ];
}
```

---

## Troubleshooting

### Common Issues

**Issue**: Columns not displaying
- Check that `getHeaders()` returns proper array structure
- Verify column `id` matches model attribute

**Issue**: Sorting not working
- Ensure `sortable => true` is set
- Check `sortBy` points to valid database column

**Issue**: Search not finding results
- Verify `searchable => true` on columns
- Check `buildQuery()` includes search logic

**Issue**: Actions not appearing
- Verify routes are defined in `getRoutes()`
- Check permissions with `getActionCellPermissions()`
- Ensure `$disabledRoutes` doesn't include the action

**Issue**: Bulk delete not working
- Check `$enableBulkActions` is `true`
- Verify `$enableCheckbox` is `true`
- Ensure delete route is configured

---

## Migration Guide

### From Custom Tables to Datatable System

1. Create new Livewire component extending `Datatable`
2. Define `$model` property
3. Implement `getHeaders()` method
4. Implement `buildQuery()` method
5. Define routes in `getRoutes()`
6. Add filters if needed
7. Replace old view with `<livewire:your-datatable />`

---

*Last updated: January 2026*
