# Global Import & Export Feature

## Overview

The Import and Export features have been moved to the core application (`app/`) making them globally available for use with any model throughout the application. This provides a flexible, reusable solution for data import/export functionality with DataTables and other views.

## Features

- ✅ **Model-agnostic** - Works with any Eloquent model
- ✅ **Namespace flexibility** - Supports custom model namespaces
- ✅ **Column mapping** - Automatic and manual column mapping
- ✅ **Validation** - Built-in validation using Form Requests
- ✅ **Progress tracking** - Real-time import progress updates
- ✅ **Error handling** - Detailed row-level validation errors
- ✅ **Filter support** - Export with custom filters
- ✅ **Relationship support** - Auto-includes related data in exports
- ✅ **Dark mode** - Full dark mode support

## Directory Structure

```
app/
├── Livewire/
│   └── Components/
│       ├── ImportForm.php     # Global import Livewire component
│       └── Export.php          # Global export Livewire component
└── Services/
    ├── ImportService.php       # Import business logic
    └── ExportService.php       # Export business logic

resources/views/components/
├── import-form.blade.php       # Import UI view
└── export.blade.php            # Export UI view
```

## Usage

### 1. Prepare Your Model

Your model should implement two methods to define importable/exportable columns:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * Get required columns for import
     */
    public static function requiredImportColumns(): array
    {
        return ['name', 'price', 'sku'];
    }

    /**
     * Get all valid columns for import
     */
    public static function validImportColumns(): array
    {
        return ['name', 'price', 'sku', 'description', 'category_id', 'stock'];
    }
}
```

### 2. Create a Form Request

Create a Form Request for validation:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'sku' => 'required|string|unique:products,sku',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'stock' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required',
            'price.required' => 'Price is required',
            'sku.unique' => 'SKU must be unique',
        ];
    }
}
```

### 3. Using Import in Your Views

#### Basic Import (No Optional Fields)

```blade
{{-- In your blade view --}}
<div>
    <h2>Import Products</h2>
    
    @livewire('components.import-form', [
        'modelType' => 'Product',
        'modelNamespace' => 'App\\Models',
    ])
</div>
```

#### Import with Optional Required Fields

```blade
@livewire('components.import-form', [
    'modelType' => 'Product',
    'modelNamespace' => 'App\\Models',
    'optionalRequired' => [
        'category' => [
            ['value' => 1, 'label' => 'Electronics'],
            ['value' => 2, 'label' => 'Clothing'],
            ['value' => 3, 'label' => 'Books'],
        ],
        'status' => [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'inactive', 'label' => 'Inactive'],
        ],
    ],
])
```

#### Import from CRM Module (Backward Compatible)

```blade
@livewire('crm::components.import-form', [
    'modelType' => 'Contact',
    'optionalRequired' => $optionalRequired,
])
```

### 4. Using Export in Your Views

#### Basic Export

```blade
<div>
    <h2>Export Products</h2>
    
    @livewire('components.export', [
        'modelType' => 'Product',
        'modelNamespace' => 'App\\Models',
    ])
</div>
```

#### Export with Filters

```blade
@livewire('components.export', [
    'modelType' => 'Product',
    'modelNamespace' => 'App\\Models',
    'filtersItems' => [
        'category' => [
            ['value' => '', 'label' => 'All Categories'],
            ['value' => 1, 'label' => 'Electronics'],
            ['value' => 2, 'label' => 'Clothing'],
        ],
        'status' => [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'inactive', 'label' => 'Inactive'],
        ],
    ],
])
```

### 5. Adding to DataTable Actions

You can easily integrate import/export buttons in your DataTable views:

```blade
<div class="flex gap-2 mb-4">
    {{-- Import Button --}}
    <button type="button" 
            onclick="openModal('importModal')" 
            class="btn btn-secondary">
        <iconify-icon icon="mdi:import" class="mr-2"></iconify-icon>
        Import Products
    </button>
    
    {{-- Export Button --}}
    <button type="button" 
            onclick="openModal('exportModal')" 
            class="btn btn-secondary">
        <iconify-icon icon="feather:download" class="mr-2"></iconify-icon>
        Export Products
    </button>
</div>

{{-- Import Modal --}}
<x-modal id="importModal" title="Import Products" size="xl">
    @livewire('components.import-form', [
        'modelType' => 'Product',
        'modelNamespace' => 'App\\Models',
    ])
</x-modal>

{{-- Export Modal --}}
<x-modal id="exportModal" title="Export Products">
    @livewire('components.export', [
        'modelType' => 'Product',
        'modelNamespace' => 'App\\Models',
    ])
</x-modal>
```

### 6. Using in Actions Dropdown

```blade
<x-actions-dropdown>
    <x-actions-dropdown-item 
        icon="mdi:import"
        label="Import"
        description="Import from CSV/Excel"
        click="openModal('importModal')"
    />
    
    <x-actions-dropdown-item 
        icon="feather:download"
        label="Export"
        description="Export to CSV"
        click="openModal('exportModal')"
    />
</x-actions-dropdown>
```

## Advanced Configuration

### Custom Route Prefix

```blade
@livewire('components.export', [
    'modelType' => 'Product',
    'modelNamespace' => 'App\\Models',
    'routePrefix' => 'admin.products',
])
```

### Custom Import Route

```blade
@livewire('components.import-form', [
    'modelType' => 'Product',
    'modelNamespace' => 'App\\Models',
    'route' => route('admin.products.import'),
])
```

## Routes Setup

Add these routes to your web.php:

```php
// Import/Export routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Generic export download route
    Route::get('export/download/{filename}', function ($filename) {
        $path = storage_path("app/exports/{$filename}");
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        return response()->download($path)->deleteFileAfterSend(true);
    })->name('export.download');
    
    // Model-specific routes
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('import', [ProductController::class, 'importForm'])->name('import.form');
        Route::post('import', [ProductController::class, 'import'])->name('import');
        Route::get('export/sample', [ProductController::class, 'downloadSample'])->name('export.sample');
    });
});
```

## File Format Requirements

### CSV/Excel Import Format

```csv
name,price,sku,description,category_id,stock
"Product 1",29.99,SKU001,"Description here",1,100
"Product 2",39.99,SKU002,"Another description",2,50
```

### Requirements:
- ✅ First row must contain column headers
- ✅ Column names should match model's `validImportColumns()`
- ✅ Required columns must be present
- ✅ Supports CSV, XLS, and XLSX formats
- ✅ Maximum file size: 5MB (configurable)

## Error Handling

The import component provides detailed error feedback:

- **Missing Required Columns** - Highlights missing required fields
- **Validation Errors** - Shows row-by-row validation errors
- **Import Errors** - Displays database or system errors

Errors are displayed in the right panel with:
- Row number
- Field name
- Error message
- Original value (when applicable)

## Best Practices

1. **Always define required and valid columns** on your models
2. **Create comprehensive Form Requests** with clear validation messages
3. **Test with sample data** before importing large datasets
4. **Use optional required fields** for fields that should apply to all imported rows
5. **Provide sample CSV files** to users for reference
6. **Monitor import progress** for large datasets
7. **Handle unique constraints** properly in your validation rules

## Troubleshooting

### Import not working?
- Check that your model implements `requiredImportColumns()` and `validImportColumns()`
- Verify Form Request class exists and is properly namespaced
- Ensure column names match exactly (case-insensitive)

### Export showing empty?
- Verify model namespace is correct
- Check database table has data
- Ensure selected columns exist in table schema

### Validation errors?
- Review Form Request rules
- Check CSV data matches expected format
- Verify foreign key relationships exist

## Migration from CRM Module

The CRM module's import/export components have been updated to extend the global components, ensuring backward compatibility. Existing CRM imports/exports will continue to work without changes.

To migrate other modules:

1. Update Livewire component to extend global component
2. Override `mount()` to set correct namespace
3. Keep existing views for UI consistency
4. Update service references to use global services

## Examples Repository

Check the CRM module for working examples:
- `modules/crm/app/Livewire/Components/ImportForm.php`
- `modules/crm/app/Livewire/Components/Export.php`
- `modules/crm/resources/views/contacts/index.blade.php`

## Support

For issues or questions:
1. Check this documentation
2. Review CRM module examples
3. Check model and Form Request setup
4. Verify routes are properly configured
