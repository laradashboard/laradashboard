{{-- Example: Import Products Page --}}
{{-- File: resources/views/products/import.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Import Products</h1>
        <p class="text-gray-600 dark:text-gray-400">Upload a CSV or Excel file to import products</p>
    </div>

    <div class="flex gap-4 mb-6">
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
            <iconify-icon icon="mdi:arrow-left" class="mr-2"></iconify-icon>
            Back to Products
        </a>
        <a href="{{ route('admin.products.export.sample') }}" class="btn btn-secondary">
            <iconify-icon icon="mdi:download" class="mr-2"></iconify-icon>
            Download Sample CSV
        </a>
    </div>

    {{-- Using the global import component --}}
    @livewire('components.import-form', [
        'modelType' => 'Product',
        'modelNamespace' => 'App\\Models',
        'optionalRequired' => [
            'category' => [
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
