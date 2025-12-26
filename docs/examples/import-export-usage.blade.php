{{-- USAGE EXAMPLES --}}

{{-- 1. Basic Usage (No Filters) --}}
@livewire('components.import-export', [
    'modelClass' => \App\Models\User::class
])

{{-- 2. With Filters (Export filtered data) --}}
@livewire('components.import-export', [
    'modelClass' => \Modules\Crm\Models\Product::class,
    'filters' => [
        'type' => 'product',
        'is_active' => 1,
        'category_id' => 5
    ]
])

{{-- 3. With Dynamic Filters from Request --}}
@livewire('components.import-export', [
    'modelClass' => \Modules\Crm\Models\Contact::class,
    'filters' => request()->only(['status', 'type', 'group_id'])
])

{{-- That's it! One component for both Import & Export --}}
