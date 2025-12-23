# Import & Export Feature Migration

## Summary

The Import and Export features have been successfully migrated from the CRM module to the core application, making them globally available for use with any model throughout the application.

## What Changed

### New Global Components

1. **Services** (in `app/Services/`)
   - `ImportService.php` - Handles import logic, file parsing, validation
   - `ExportService.php` - Handles export logic, CSV generation, filtering

2. **Livewire Components** (in `app/Livewire/Components/`)
   - `ImportForm.php` - Import UI component with file upload, column mapping, validation
   - `Export.php` - Export UI component with column selection and filtering

3. **Views** (in `resources/views/components/`)
   - `import-form.blade.php` - Import interface
   - `export.blade.php` - Export interface

### Backward Compatibility

The CRM module components have been updated to extend the global components:
- `modules/crm/app/Livewire/Components/ImportForm.php` now extends `App\Livewire\Components\ImportForm`
- `modules/crm/app/Livewire/Components/Export.php` now extends `App\Livewire\Components\Export`
- CRM views remain unchanged for consistency
- All existing CRM imports/exports continue to work

## Key Features

✅ **Model-Agnostic** - Works with any Eloquent model  
✅ **Namespace Flexibility** - Supports custom model namespaces  
✅ **Column Mapping** - Automatic and manual column mapping  
✅ **Validation** - Built-in validation using Form Requests  
✅ **Progress Tracking** - Real-time import progress  
✅ **Error Handling** - Detailed row-level errors  
✅ **Filter Support** - Export with custom filters  
✅ **Relationship Support** - Auto-includes related data  
✅ **Dark Mode** - Full dark mode support  
✅ **Easy Integration** - Works with DataTables and any view  

## Usage

### Basic Import

```blade
@livewire('components.import-form', [
    'modelType' => 'Product',
    'modelNamespace' => 'App\\Models',
])
```

### Basic Export

```blade
@livewire('components.export', [
    'modelType' => 'Product',
    'modelNamespace' => 'App\\Models',
])
```

### With DataTable

```blade
<button onclick="openModal('importModal')" class="btn btn-secondary">
    <iconify-icon icon="mdi:import"></iconify-icon> Import
</button>

<x-modal id="importModal" title="Import Products">
    @livewire('components.import-form', [
        'modelType' => 'Product',
        'modelNamespace' => 'App\\Models',
    ])
</x-modal>
```

## Documentation

📖 **Complete Guide**: `docs/import-export-guide.md`  
📂 **Examples**: `docs/examples/`
- `ProductImportExportController.php` - Controller example
- `import-view-example.blade.php` - Import page example
- `export-view-example.blade.php` - Export page example
- `datatable-with-import-export-example.blade.php` - DataTable integration

## Model Requirements

Your model should implement:

```php
public static function requiredImportColumns(): array
{
    return ['name', 'email']; // Required fields
}

public static function validImportColumns(): array
{
    return ['name', 'email', 'phone', 'address']; // All valid fields
}
```

## Form Request

Create a Form Request for validation:

```php
class ProductFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ];
    }
}
```

## Routes

Add export download route:

```php
Route::get('export/download/{filename}', function ($filename) {
    $path = storage_path("app/exports/{$filename}");
    return response()->download($path)->deleteFileAfterSend(true);
})->name('export.download');
```

## Testing

The implementation has been tested with:
- ✅ CRM Contact imports (backward compatibility)
- ✅ File format validation (CSV, XLS, XLSX)
- ✅ Column mapping (automatic and manual)
- ✅ Row-level validation
- ✅ Error handling
- ✅ Export with filters
- ✅ Relationship exports

## Benefits

1. **Reusability** - Use with any model in any module
2. **Consistency** - Same UI/UX across all imports/exports
3. **Maintainability** - Single source of truth
4. **Flexibility** - Easy to customize per model
5. **Scalability** - Works with large datasets
6. **Developer-Friendly** - Simple integration

## Migration Path

For existing modules using custom import/export:

1. Update your model to implement `requiredImportColumns()` and `validImportColumns()`
2. Create a Form Request for validation
3. Replace custom import/export with global components
4. Add optional filters/settings as needed
5. Test thoroughly

## Support

For questions or issues:
1. Check `docs/import-export-guide.md`
2. Review examples in `docs/examples/`
3. See CRM module implementation
4. Verify model and Form Request setup

## Future Enhancements

Potential improvements:
- [ ] Batch processing for large files
- [ ] Import scheduling
- [ ] Export templates
- [ ] Multi-sheet Excel support
- [ ] Progress webhooks
- [ ] Import history tracking
- [ ] Column transformation rules
- [ ] Data preview before import

## Credits

Migrated from CRM module by making the components generic and reusable across the entire application.
