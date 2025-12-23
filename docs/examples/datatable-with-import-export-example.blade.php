{{-- Example: DataTable with Import/Export Buttons --}}
{{-- File: resources/views/products/index.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Products</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage your product inventory</p>
        </div>
        
        <div class="flex gap-2">
            {{-- Import Button --}}
            <button type="button" 
                    onclick="document.getElementById('importModal').classList.remove('hidden')" 
                    class="btn btn-secondary">
                <iconify-icon icon="mdi:import" class="mr-2"></iconify-icon>
                Import
            </button>
            
            {{-- Export Button --}}
            <button type="button" 
                    onclick="document.getElementById('exportModal').classList.remove('hidden')" 
                    class="btn btn-secondary">
                <iconify-icon icon="feather:download" class="mr-2"></iconify-icon>
                Export
            </button>
            
            {{-- Add Product Button --}}
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <iconify-icon icon="mdi:plus" class="mr-2"></iconify-icon>
                Add Product
            </a>
        </div>
    </div>

    {{-- Products DataTable --}}
    @livewire('products-datatable')

    {{-- Import Modal --}}
    <div id="importModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Import Products</h3>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" 
                        class="text-gray-400 hover:text-gray-600">
                    <iconify-icon icon="mdi:close" width="24" height="24"></iconify-icon>
                </button>
            </div>
            
            @livewire('components.import-form', [
                'modelType' => 'Product',
                'modelNamespace' => 'App\\Models',
                'optionalRequired' => [
                    'category' => [
                        ['value' => 1, 'label' => 'Electronics'],
                        ['value' => 2, 'label' => 'Clothing'],
                        ['value' => 3, 'label' => 'Books'],
                    ],
                ],
            ])
        </div>
    </div>

    {{-- Export Modal --}}
    <div id="exportModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Export Products</h3>
                <button onclick="document.getElementById('exportModal').classList.add('hidden')" 
                        class="text-gray-400 hover:text-gray-600">
                    <iconify-icon icon="mdi:close" width="24" height="24"></iconify-icon>
                </button>
            </div>
            
            @livewire('components.export', [
                'modelType' => 'Product',
                'modelNamespace' => 'App\\Models',
                'filtersItems' => [
                    'category' => [
                        ['value' => '', 'label' => 'All Categories'],
                        ['value' => 1, 'label' => 'Electronics'],
                        ['value' => 2, 'label' => 'Clothing'],
                    ],
                ],
            ])
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Close modals on successful import/export
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('show-import-success', () => {
            setTimeout(() => {
                document.getElementById('importModal').classList.add('hidden');
            }, 3000);
        });
        
        Livewire.on('export-download', () => {
            setTimeout(() => {
                document.getElementById('exportModal').classList.add('hidden');
            }, 1000);
        });
    });
</script>
@endpush
@endsection
