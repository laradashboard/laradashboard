{{-- Example: Export Products Page --}}
{{-- File: resources/views/products/export.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Export Products</h1>
        <p class="text-gray-600 dark:text-gray-400">Select columns and filters to export products</p>
    </div>

    <div class="mb-6">
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
            <iconify-icon icon="mdi:arrow-left" class="mr-2"></iconify-icon>
            Back to Products
        </a>
    </div>

    {{-- Using the global export component --}}
    @livewire('components.export', [
        'modelType' => 'Product',
        'modelNamespace' => 'App\\Models',
        'filtersItems' => [
            'category' => [
                ['value' => '', 'label' => 'All Categories'],
                ['value' => 1, 'label' => 'Electronics'],
                ['value' => 2, 'label' => 'Clothing'],
                ['value' => 3, 'label' => 'Books'],
                ['value' => 4, 'label' => 'Home & Garden'],
            ],
            'status' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
                ['value' => 'draft', 'label' => 'Draft'],
            ],
        ],
    ])
</div>
@endsection
