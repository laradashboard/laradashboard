# Import/Export Quick Reference

## 🚀 Quick Setup (5 Steps)

### 1. Model Methods

```php
public static function requiredImportColumns(): array
{
    return ['name', 'email']; // Required fields
}

public static function validImportColumns(): array
{
    return ['name', 'email', 'phone']; // All valid fields
}
```

### 2. Form Request

```php
class ProductFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:products',
        ];
    }
}
```

### 3. Import Usage

```blade
@livewire('components.import-form', [
    'modelType' => 'Product',
    'modelNamespace' => 'App\\Models',
])
```

### 4. Export Usage

```blade
@livewire('components.export', [
    'modelType' => 'Product',
    'modelNamespace' => 'App\\Models',
])
```

### 5. Route

```php
Route::get('export/download/{filename}', function ($filename) {
    return response()->download(storage_path("app/exports/{$filename}"))->deleteFileAfterSend(true);
})->name('export.download');
```

---

## 📝 Common Patterns

### With Modals

```blade
<button onclick="openModal('importModal')">Import</button>

<x-modal id="importModal" title="Import Products">
    @livewire('components.import-form', ['modelType' => 'Product', 'modelNamespace' => 'App\\Models'])
</x-modal>
```

### With Optional Fields

```blade
@livewire('components.import-form', [
    'modelType' => 'Product',
    'modelNamespace' => 'App\\Models',
    'optionalRequired' => [
        'category' => [
            ['value' => 1, 'label' => 'Electronics'],
            ['value' => 2, 'label' => 'Clothing'],
        ],
    ],
])
```

### With Filters

```blade
@livewire('components.export', [
    'modelType' => 'Product',
    'modelNamespace' => 'App\\Models',
    'filtersItems' => [
        'category' => [
            ['value' => '', 'label' => 'All'],
            ['value' => 1, 'label' => 'Electronics'],
        ],
    ],
])
```

---

## 🎯 Parameter Reference

### ImportForm Component

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `modelType` | string | Yes | Model name (e.g., 'Product') |
| `modelNamespace` | string | No | Model namespace (default: auto-detect) |
| `optionalRequired` | array | No | Fields to apply to all rows |
| `route` | string | No | Custom import route |

### Export Component

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `modelType` | string | Yes | Model name (e.g., 'Product') |
| `modelNamespace` | string | No | Model namespace (default: auto-detect) |
| `filtersItems` | array | No | Export filters |
| `routePrefix` | string | No | Route prefix (default: 'admin') |

---

## ⚡ File Format

### CSV/Excel Headers

```csv
name,email,phone,address
"John Doe","john@example.com","1234567890","123 Main St"
"Jane Smith","jane@example.com","0987654321","456 Oak Ave"
```

- First row = column headers
- Column names match `validImportColumns()`
- Required columns must be present

---

## 🔧 Troubleshooting

### Import Not Working?

✅ Check model implements `requiredImportColumns()` and `validImportColumns()`  
✅ Verify Form Request exists with correct namespace  
✅ Ensure column names match (case-insensitive)  
✅ Check validation rules in Form Request  

### Export Empty?

✅ Verify model namespace is correct  
✅ Check table has data  
✅ Ensure columns exist in database  

### Validation Errors?

✅ Review Form Request rules  
✅ Check CSV data format  
✅ Verify foreign keys exist  

---

## 📚 Documentation

- **Complete Guide:** `docs/import-export-guide.md`
- **Migration Info:** `docs/IMPORT_EXPORT_MIGRATION.md`
- **Examples:** `docs/examples/`
- **Summary:** `IMPORT_EXPORT_SUMMARY.md`

---

## 🎨 UI Features

✅ Drag & drop upload  
✅ Real-time validation  
✅ Progress tracking  
✅ Detailed errors  
✅ Dark mode  
✅ Responsive  

---

## 💡 Pro Tips

1. **Always test with sample data first**
2. **Use unique constraints carefully**
3. **Provide sample CSV to users**
4. **Handle large files with batches**
5. **Add indexes for foreign keys**
6. **Use clear validation messages**

---

## 🔗 Quick Links

- [Import/Export Guide](docs/import-export-guide.md)
- [Controller Example](docs/examples/ProductImportExportController.php)
- [View Examples](docs/examples/)
- [CRM Implementation](modules/crm/app/Livewire/Components/)

---

**Need Help?** Check the complete documentation in `docs/import-export-guide.md`
