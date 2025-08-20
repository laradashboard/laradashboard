# Laravel PageGenerator

A comprehensive page generation system for Laravel that makes building admin panels, forms, and tables faster and more maintainable while following SOLID, KISS, and DRY principles.

## Table of Contents

1. [Installation & Setup](#installation--setup)
2. [Architecture Overview](#architecture-overview)
3. [Quick Start](#quick-start)
4. [Creating List Pages](#creating-list-pages)
5. [Creating Form Pages](#creating-form-pages)
6. [Using in Controllers](#using-in-controllers)
7. [Customization](#customization)
8. [Components Reference](#components-reference)
9. [Advanced Usage](#advanced-usage)
10. [Best Practices](#best-practices)

## Installation & Setup

The PageGenerator system is already included in this project. All necessary files are located in:

- `app/PageGenerator/` - Core classes and contracts
- `app/Pages/` - Your page implementations
- `resources/views/components/page-generator/` - Blade components
- `resources/views/page-generator/` - Default views

## Architecture Overview

The PageGenerator follows these principles:

### SOLID Principles
- **Single Responsibility**: Each page class handles one specific page type
- **Open/Closed**: Easy to extend without modifying core classes
- **Liskov Substitution**: All page implementations are interchangeable
- **Interface Segregation**: Separate contracts for different page types
- **Dependency Inversion**: Depends on abstractions, not concretions

### Key Components

1. **Contracts** (`app/PageGenerator/Contracts/`)
   - `PageContract` - Base interface for all pages
   - `ListPageContract` - Interface for list/table pages
   - `FormPageContract` - Interface for form pages

2. **Abstract Classes** (`app/PageGenerator/`)
   - `AbstractPage` - Base implementation
   - `AbstractListPage` - Base for list pages
   - `AbstractFormPage` - Base for form pages

3. **Page Classes** (`app/Pages/`)
   - Organized by module (e.g., `User/`, `Post/`)
   - Concrete implementations for specific pages

4. **Components** (`resources/views/components/page-generator/`)
   - Reusable Blade components for forms and tables

## Quick Start

### 1. Create a Simple List Page

```php
<?php

namespace App\Pages\Product;

use App\Models\Product;
use App\PageGenerator\AbstractListPage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ProductList extends AbstractListPage
{
    protected array $searchableColumns = ['name', 'description'];
    protected array $sortableColumns = ['name', 'price', 'created_at'];

    public function getTitle(): string
    {
        return __('Products');
    }

    public function getBreadcrumbs(): array
    {
        return [
            'title' => __('Products'),
        ];
    }

    public function getColumns(): array
    {
        return [
            [
                'name' => 'name',
                'label' => __('Name'),
                'type' => 'text',
            ],
            [
                'name' => 'price',
                'label' => __('Price'),
                'type' => 'text',
            ],
            [
                'name' => 'created_at',
                'label' => __('Created'),
                'type' => 'date',
            ],
        ];
    }

    public function getData(): LengthAwarePaginator|Collection
    {
        $query = Product::query();
        
        $query = $this->applySearch($query);
        $query = $this->applySorting($query);
        
        return $query->paginate($this->getPerPage());
    }
}
```

### 2. Create a Simple Form Page

```php
<?php

namespace App\Pages\Product;

use App\Models\Product;
use App\PageGenerator\AbstractFormPage;
use Illuminate\Http\Request;

class ProductCreate extends AbstractFormPage
{
    public function getTitle(): string
    {
        return __('Create Product');
    }

    public function getBreadcrumbs(): array
    {
        return [
            'title' => __('New Product'),
            'items' => [
                [
                    'label' => __('Products'),
                    'url' => route('admin.products.index'),
                ],
            ],
        ];
    }

    public function getFields(): array
    {
        return [
            'name' => [
                'type' => 'text',
                'label' => __('Name'),
                'required' => true,
            ],
            'description' => [
                'type' => 'textarea',
                'label' => __('Description'),
                'rows' => 4,
            ],
            'price' => [
                'type' => 'number',
                'label' => __('Price'),
                'required' => true,
                'step' => '0.01',
            ],
        ];
    }

    public function getFormAction(): string
    {
        return route('admin.products.store');
    }

    public function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ];
    }

    public function getCancelRoute(): string
    {
        return route('admin.products.index');
    }
}
```

### 3. Use in Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Pages\Product\ProductList;
use App\Pages\Product\ProductCreate;
use App\PageGenerator\Traits\HasPageGenerator;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use HasPageGenerator;

    public function index(Request $request)
    {
        $page = $this->makePage(ProductList::class, $request);
        return $this->renderPage($page);
    }

    public function create(Request $request)
    {
        $page = $this->makePage(ProductCreate::class, $request);
        return $this->renderPage($page);
    }
}
```

## Creating List Pages

List pages extend `AbstractListPage` and provide data display with features like:

- Search functionality
- Sorting
- Filtering
- Pagination
- Bulk actions
- Row actions

### Basic Structure

```php
class YourListPage extends AbstractListPage
{
    // Required methods
    public function getTitle(): string;
    public function getBreadcrumbs(): array;
    public function getColumns(): array;
    public function getData(): LengthAwarePaginator|Collection;
    
    // Optional methods
    public function getFilters(): array;
    public function getActions(): array;
    public function getBulkActions(): array;
    public function authorize(): bool;
}
```

### Column Types

```php
public function getColumns(): array
{
    return [
        // Text column
        [
            'name' => 'name',
            'label' => __('Name'),
            'type' => 'text',
        ],
        
        // Image column
        [
            'name' => 'avatar',
            'label' => __('Avatar'),
            'type' => 'image',
            'class' => 'w-10 h-10 rounded-full',
        ],
        
        // Badge column
        [
            'name' => 'status',
            'label' => __('Status'),
            'type' => 'badge',
            'badgeClass' => 'badge-success',
        ],
        
        // Boolean column
        [
            'name' => 'is_active',
            'label' => __('Active'),
            'type' => 'boolean',
        ],
        
        // Date column
        [
            'name' => 'created_at',
            'label' => __('Created'),
            'type' => 'date',
            'format' => 'Y-m-d H:i',
        ],
        
        // Link column
        [
            'name' => 'website',
            'label' => __('Website'),
            'type' => 'link',
            'target' => '_blank',
        ],
        
        // Custom column
        [
            'name' => 'custom',
            'label' => __('Custom'),
            'type' => 'custom',
            'render' => function ($item, $value) {
                return '<span class="text-blue-500">' . $value . '</span>';
            },
        ],
    ];
}
```

### Filters

```php
public function getFilters(): array
{
    return [
        [
            'name' => 'status',
            'label' => __('Status'),
            'type' => 'select',
            'options' => [
                'active' => __('Active'),
                'inactive' => __('Inactive'),
            ],
        ],
        [
            'name' => 'created_at',
            'label' => __('Created Date'),
            'type' => 'daterange',
            'callback' => function ($query, $value) {
                // Custom filter logic
                return $query;
            },
        ],
    ];
}
```

### Actions

```php
public function getActions(): array
{
    return [
        [
            'label' => __('Edit'),
            'icon' => 'pencil',
            'route' => fn($item) => route('admin.items.edit', $item->id),
            'condition' => fn($item) => auth()->user()->can('update', $item),
        ],
        [
            'type' => 'delete',
            'label' => __('Delete'),
            'icon' => 'trash',
            'route' => fn($item) => route('admin.items.destroy', $item->id),
            'condition' => fn($item) => auth()->user()->can('delete', $item),
        ],
    ];
}
```

### Bulk Actions

```php
public function getBulkActions(): array
{
    return [
        [
            'name' => 'bulk_delete',
            'label' => __('Delete Selected'),
            'icon' => 'lucide:trash',
            'route' => route('admin.items.bulk-delete'),
            'method' => 'DELETE',
            'confirm' => true,
        ],
        [
            'name' => 'bulk_export',
            'label' => __('Export Selected'),
            'icon' => 'lucide:download',
            'route' => route('admin.items.export'),
            'method' => 'POST',
        ],
    ];
}
```

## Creating Form Pages

Form pages extend `AbstractFormPage` and provide form building with:

- Multiple field types
- Validation
- Sections
- File uploads
- AJAX support

### Basic Structure

```php
class YourFormPage extends AbstractFormPage
{
    // Required methods
    public function getTitle(): string;
    public function getBreadcrumbs(): array;
    public function getFields(): array;
    public function getFormAction(): string;
    public function getValidationRules(): array;
    public function getCancelRoute(): string;
    
    // Optional methods
    public function getSections(): array;
    public function beforeSave(array $data): array;
    public function afterSave(Model $model): void;
    public function authorize(): bool;
}
```

### Field Types

```php
public function getFields(): array
{
    return [
        // Text input
        'name' => [
            'type' => 'text',
            'label' => __('Name'),
            'required' => true,
            'placeholder' => __('Enter name'),
        ],
        
        // Email input
        'email' => [
            'type' => 'email',
            'label' => __('Email'),
            'required' => true,
        ],
        
        // Password input
        'password' => [
            'type' => 'password',
            'label' => __('Password'),
            'required' => true,
            'help' => __('Minimum 8 characters'),
        ],
        
        // Textarea
        'description' => [
            'type' => 'textarea',
            'label' => __('Description'),
            'rows' => 4,
        ],
        
        // Select dropdown
        'category' => [
            'type' => 'select',
            'label' => __('Category'),
            'options' => [
                'tech' => __('Technology'),
                'news' => __('News'),
            ],
        ],
        
        // Multiple select
        'tags' => [
            'type' => 'select',
            'label' => __('Tags'),
            'multiple' => true,
            'options' => $this->getTags(),
        ],
        
        // Checkbox
        'is_active' => [
            'type' => 'checkbox',
            'label' => __('Active'),
            'value' => '1',
        ],
        
        // Radio buttons
        'status' => [
            'type' => 'radio',
            'label' => __('Status'),
            'options' => [
                'draft' => __('Draft'),
                'published' => __('Published'),
            ],
        ],
        
        // File upload
        'image' => [
            'type' => 'file',
            'label' => __('Image'),
            'accept' => 'image/*',
            'preview' => true,
        ],
        
        // Date input
        'publish_date' => [
            'type' => 'date',
            'label' => __('Publish Date'),
        ],
        
        // Number input
        'price' => [
            'type' => 'number',
            'label' => __('Price'),
            'step' => '0.01',
            'min' => '0',
        ],
        
        // Hidden field
        'user_id' => [
            'type' => 'hidden',
            'default' => auth()->id(),
        ],
        
        // Custom field
        'custom_field' => [
            'type' => 'custom',
            'component' => 'custom.field-component',
        ],
    ];
}
```

### Form Sections

```php
public function getSections(): array
{
    return [
        [
            'title' => __('Basic Information'),
            'description' => __('Enter the basic details'),
            'fields' => ['name', 'email', 'description'],
            'columns' => 'md:grid-cols-2',
        ],
        [
            'title' => __('Settings'),
            'fields' => ['status', 'is_active'],
        ],
    ];
}
```

### Data Processing

```php
public function beforeSave(array $data): array
{
    // Process data before saving
    if (isset($data['password'])) {
        $data['password'] = bcrypt($data['password']);
    }
    
    return $data;
}

public function afterSave(Model $model): void
{
    // Handle relationships or additional processing
    if ($this->request->has('tags')) {
        $model->tags()->sync($this->request->input('tags'));
    }
}
```

## Using in Controllers

### Method 1: Using the Trait

```php
use App\PageGenerator\Traits\HasPageGenerator;

class YourController extends Controller
{
    use HasPageGenerator;

    public function index(Request $request)
    {
        $page = $this->makePage(YourListPage::class, $request);
        return $this->renderPage($page);
    }
}
```

### Method 2: Direct Instantiation

```php
public function index(Request $request)
{
    return app(YourListPage::class, [$request]);
}
```

### Method 3: Service Container

```php
public function index(YourListPage $page)
{
    return $page;
}
```

### Method 4: Custom View Override

```php
public function index(Request $request)
{
    $page = app(YourListPage::class, [$request]);
    $page->setView('custom.list-view');
    return $page;
}
```

## Customization

### Custom Views

You can override the default views:

```php
// Override in page class
protected function getDefaultView(): string
{
    return 'custom.my-list-view';
}

// Or set dynamically
$page->setView('custom.my-view');
```

### Custom Components

Create custom form fields:

```php
// In field definition
'custom_field' => [
    'type' => 'custom',
    'component' => 'custom.wysiwyg-editor',
    'options' => ['toolbar' => 'full'],
],
```

### Authorization

```php
public function authorize(): bool
{
    return Gate::allows('viewAny', YourModel::class);
}
```

### Adding Data to Views

```php
$page = app(YourPage::class, [$request]);
$page->with('customData', $data);
$page->withData(['key1' => 'value1', 'key2' => 'value2']);
return $page;
```

## Components Reference

### List Component

```blade
<x-page-generator.list 
    :columns="$columns"
    :data="$data"
    :actions="$actions"
    :bulk-actions="$bulkActions"
    :filters="$filters"
    :show-checkboxes="true"
    :show-search="true"
    :show-filters="true"
    :show-pagination="true"
    :create-route="route('admin.items.create')"
/>
```

### Form Component

```blade
<x-page-generator.form 
    :form-action="route('admin.items.store')"
    :fields="$fields"
    :sections="$sections"
    :model="$model"
    :cancel-route="route('admin.items.index')"
/>
```

### Individual Components

```blade
<!-- Search -->
<x-page-generator.search placeholder="Search items..." />

<!-- Filters -->
<x-page-generator.filters :filters="$filters" />

<!-- Form Field -->
<x-page-generator.form-field 
    :field="$field" 
    :name="$name"
    :model="$model"
/>
```

## Advanced Usage

### Custom Column Rendering

```php
[
    'name' => 'status',
    'label' => __('Status'),
    'type' => 'custom',
    'render' => function ($item, $value) {
        $class = $value === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        return "<span class=\"px-2 py-1 rounded-full text-xs font-medium {$class}\">{$value}</span>";
    },
],
```

### Complex Filters

```php
[
    'name' => 'date_range',
    'label' => __('Date Range'),
    'type' => 'daterange',
    'callback' => function ($query, $value) {
        $from = request('date_range_from');
        $to = request('date_range_to');
        
        if ($from && $to) {
            return $query->whereBetween('created_at', [$from, $to]);
        }
        
        return $query;
    },
],
```

### AJAX Forms

```php
protected bool $isAjaxForm = true;

// The form will automatically handle AJAX submission
// and show loading states
```

### Conditional Fields

```php
'admin_notes' => [
    'type' => 'textarea',
    'label' => __('Admin Notes'),
    'condition' => fn() => auth()->user()->isAdmin(),
],
```

## Best Practices

### 1. Organization

- Group related pages in folders (`app/Pages/User/`, `app/Pages/Product/`)
- Use descriptive class names (`UserList`, `UserCreate`, `UserEdit`)
- Keep pages focused on single responsibility

### 2. Performance

- Use eager loading in `getData()` method
- Implement proper pagination
- Add database indexes for searchable/sortable columns

### 3. Security

- Always implement `authorize()` method
- Validate all form inputs
- Use policies for fine-grained permissions

### 4. Maintenance

- Keep field definitions DRY using helper methods
- Use translation keys for all user-facing text
- Document complex custom renderers

### 5. Testing

```php
// Test page instantiation
$page = app(UserList::class, [request()]);
$this->assertInstanceOf(UserList::class, $page);

// Test authorization
$this->assertTrue($page->authorize());

// Test data retrieval
$data = $page->getData();
$this->assertInstanceOf(LengthAwarePaginator::class, $data);
```

## Migration Guide

To convert existing pages to use PageGenerator:

1. **Identify the page type** (list or form)
2. **Create the page class** in `app/Pages/`
3. **Move logic** from controller to page class
4. **Update controller** to use page class
5. **Test thoroughly**

Example conversion:

```php
// Before (in controller)
public function index()
{
    $users = User::with('roles')->paginate(10);
    return view('admin.users.index', compact('users'));
}

// After (page class)
class UserList extends AbstractListPage
{
    public function getData(): LengthAwarePaginator
    {
        return User::with('roles')->paginate($this->getPerPage());
    }
    
    // ... other methods
}

// After (in controller)
public function index(Request $request)
{
    return app(UserList::class, [$request]);
}
```

This PageGenerator system provides a powerful, flexible, and maintainable way to build admin interfaces while keeping your code organized and DRY.