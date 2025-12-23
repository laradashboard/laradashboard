{{-- Global Export Component --}}
{{-- Usage: @livewire('components.export', ['modelType' => 'Contact', 'modelNamespace' => 'Modules\\Crm\\Models']) --}}

<div x-data="{
    triggerDownload(url) {
        const a = document.createElement('a');
        a.href = url;
        a.download = '';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
}" x-on:export-download.window="triggerDownload($event.detail.url)">
    <form wire:submit.prevent="export">
        @if (!empty($filtersItems))
            <div class="mb-4 p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">{{ __('Filters') }}</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($filtersItems as $field => $options)
                        <div>
                            <label for="{{ $field }}" class="form-label">
                                {{ __(ucwords(str_replace('_', ' ', $field))) }}
                            </label>
                            <select name="{{ $field }}" 
                                    id="{{ $field }}" 
                                    wire:model.live="filtersValues.{{ $field }}" 
                                    class="form-control">
                                <option value="">{{ __('All') }}</option>
                                @foreach ($options as $option)
                                    <option value="{{ $option['value'] }}">
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mb-6">
            <div class="mb-4 flex items-center gap-3">
                <label class="flex items-center gap-2 font-medium text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="allSelected"
                        class="form-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>{{ __('Select All Columns') }}</span>
                </label>
                <span wire:loading wire:target="allSelected" class="ml-2">
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                </span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ __('Columns') }}</div>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($availableColumns as $col)
                            @if (!str_ends_with($col, '_id'))
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" wire:model.live="selectedColumns" value="{{ $col }}"
                                        class="form-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span>{{ $col }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
                
                <div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ __('Relational Fields') }}</div>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($availableColumns as $col)
                            @if (str_ends_with($col, '_id'))
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" wire:model.live="selectedColumns"
                                        value="{{ $col }}"
                                        class="form-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span>{{ $col }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex items-center justify-end mb-4">
            <button type="submit" class="btn btn-primary" {{ count($selectedColumns) < 1 ? 'disabled' : '' }}>
                <iconify-icon icon="feather:download" height="16" width="16" class="mr-2"></iconify-icon>
                {{ __('Export ' . ucfirst($modelType)) }}
            </button>
        </div>
    </form>
</div>
