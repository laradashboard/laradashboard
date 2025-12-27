@props([
    'modelType' => null,
    'modelNamespace' => null,
    'optionalRequired' => [],
    'sampleRoute' => null,
    'sampleText' => __('Download our sample CSV file to see the correct format.'),
    'filters' => [],
])

{{-- Import Modal --}}
<div x-show="importModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div @click="importModalOpen = false" class="fixed inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-lg max-w-4xl w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-semibold">{{ __('Import :type', ['type' => $modelType]) }}</h3>
                <button @click="importModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <iconify-icon icon="mdi:close" class="w-5 h-5"></iconify-icon>
                </button>
            </div>
            <div class="p-6">
                <livewire:components.import-form
                    :modelType="$modelType"
                    :modelNamespace="$modelNamespace"
                    :optionalRequired="$optionalRequired"
                />
                @if($sampleRoute)
                    <x-card class="mt-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-medium text-primary dark:text-blue-100 mb-1">{{ __('Need a sample file?') }}</h4>
                                <p class="text-sm text-gray-400">{{ $sampleText }}</p>
                            </div>
                            <a href="{{ $sampleRoute }}" class="btn btn-primary">
                                <iconify-icon icon="feather:download" class="mr-2"></iconify-icon>
                                {{ __('Download Sample') }}
                            </a>
                        </div>
                    </x-card>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Export Modal --}}
<div x-show="exportModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div @click="exportModalOpen = false" class="fixed inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-lg max-w-2xl w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-semibold">{{ __('Export :type', ['type' => $modelType]) }}</h3>
                <button @click="exportModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <iconify-icon icon="mdi:close" class="w-5 h-5"></iconify-icon>
                </button>
            </div>
            <div class="p-6">
                <livewire:components.export
                    :modelType="$modelType"
                    :modelNamespace="$modelNamespace"
                    :filtersItems="$filters"
                />
            </div>
        </div>
    </div>
</div>
