# Enhanced Export System with Filters

The ExportService now supports filtering by categories, statuses, and other fields before export.

## Usage Examples

### Basic Export with Auto-Generated Filters
```php
// In your controller or Livewire component
$exportService = new ExportService('Product', 'Modules\\Crm\\Models');

// Get available filter options automatically
$filterOptions = $exportService->getFilterOptions();
// Returns: ['type' => [...], 'category_id' => [...], 'is_active' => [...]]

// Export with filters
$result = $exportService->export(
    ['name', 'sku', 'price', 'type'], 
    ['type' => 'service', 'is_active' => '1']
);
```

### Livewire Component Usage
```php
// In your Blade template
<livewire:export 
    :model-type="'Product'" 
    :model-namespace="'Modules\\Crm\\Models'" 
/>

// Filters are automatically loaded from the model
```

### Manual Filter Configuration
```php
// Custom filter options
$customFilters = [
    'type' => [
        ['value' => 'product', 'label' => 'Product'],
        ['value' => 'service', 'label' => 'Service']
    ],
    'category_id' => [
        ['value' => 1, 'label' => 'Electronics'],
        ['value' => 2, 'label' => 'Clothing']
    ]
];

<livewire:export 
    :model-type="'Product'" 
    :filters-items="$customFilters" 
/>
```

## Supported Filter Types

### Automatic Detection
- **type**: Product/Service, Lead/Customer, etc.
- **status**: Active/Inactive, Open/Closed, etc.
- **is_active**: Boolean active/inactive
- **category_id**: Related category names
- **status_id**: Related status names
- **type_id**: Related type names

### Filter Examples
- Export only "Service" products
- Export only "Lead" status contacts  
- Export only "Active" records
- Export by specific category

## Model Requirements

Your model should implement these methods for automatic filter detection:
```php
// In your model (e.g., Product.php)
public function category()
{
    return $this->belongsTo(ProductCategory::class, 'category_id');
}

public function status()
{
    return $this->belongsTo(LeadStatus::class, 'status_id');
}
```

The system automatically detects relationships and creates filter dropdowns with proper labels.