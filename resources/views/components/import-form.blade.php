{{-- Global Import Form Component --}}
{{-- Usage: @livewire('components.import-form', ['modelType' => 'Contact', 'modelNamespace' => 'Modules\\Crm\\Models']) --}}

<div class="w-full">
    <x-card>
        <x-slot name="header">
            <span class="font-semibold">{{ __('Select File to Import') }}</span>
        </x-slot>

        <div class="p-4 w-full">
            {{-- Success/Error Status --}}
            @if ($file)
                @if (count($missingColumns) < 1 && count($validationErrors) < 1)
                    <div class="inline-flex items-center px-3 py-1 rounded bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 text-sm font-medium mb-4">
                        <svg class="h-5 w-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('Validation Passed') }}
                    </div>
                @else
                    <div class="inline-flex items-center px-3 py-1 rounded bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 text-sm font-medium mb-4">
                        <svg class="h-5 w-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ __('Validation Failed') }}
                    </div>
                @endif
            @endif

            {{-- File Upload Dropzone --}}
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md relative @error('file') border-red-500 @enderror"
                id="dropzone"
                x-data="{ 
                    isDragging: false,
                    handleDrop(e) {
                        e.preventDefault();
                        this.isDragging = false;
                        
                        if (e.dataTransfer.files.length) {
                            const fileInput = document.getElementById('file');
                            
                            if (fileInput) {
                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(e.dataTransfer.files[0]);
                                fileInput.files = dataTransfer.files;
                                
                                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                    }
                }"
                x-on:dragover.prevent="isDragging = true"
                x-on:dragleave.prevent="isDragging = false"
                x-on:drop="handleDrop"
                :class="{ 'bg-blue-50 dark:bg-blue-900/10 border-blue-300 dark:border-blue-700': isDragging }">
                
                <div class="space-y-1 text-center" wire:loading.remove wire:target="file">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                        <label for="file"
                            class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 dark:focus-within:ring-offset-gray-800">
                            <span>{{ __('Upload a file') }}</span>
                            <input id="file" wire:model.live="file" name="file" type="file"
                                class="sr-only" accept=".csv,.xls,.xlsx">
                        </label>
                        <p class="pl-1">{{ __('or drag and drop') }}</p>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('CSV, XLS, XLSX up to ' . $maxFileSize / 1024 . 'MB') }}
                    </p>
                </div>
                
                <div wire:loading wire:target="file" class="text-center">
                    <svg class="animate-spin h-10 w-10 text-blue-500 mx-auto"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Uploading...') }}</p>
                </div>
                
                @if ($file && !$importFinished)
                    <div class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-900/80 rounded-md">
                        <div class="text-center p-4 w-full">
                            <div class="flex items-center justify-center mb-2">
                                <svg class="h-8 w-8 text-green-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $file->getClientOriginalName() }}</p>
                            <button type="button" wire:click="removeFile"
                                class="mt-2 text-sm text-red-600 dark:text-red-400 hover:underline">
                                {{ __('Remove file') }}
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Import Success Alert --}}
        @if ($importFinished)
            <div class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400">
                <svg class="shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <div class="ms-3 text-sm font-medium">
                    {{ __('Import finished!') }}
                    <span class="font-semibold">{{ $importProgress }}</span> {{ __('rows imported.') }}
                </div>
            </div>
        @endif

        {{-- Column Mapping and Validation --}}
        @if (count($fileColumns) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                {{-- Left: Column Mapping --}}
                <div class="rounded-2xl shadow p-6 bg-white dark:bg-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        {{ __(ucfirst($modelType)) . ' Mapping' }}
                    </h3>

                    <form wire:submit.prevent="startImport">
                        {{-- Optional Required Fields --}}
                        @if (!empty($optionalRequired))
                            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/30 rounded">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ __('Assign values to all imported rows') }}
                                </h4>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    {{ __('If selected, this value will be used for all imported rows.') }}
                                </div>
                                <div class="space-y-4">
                                    @foreach ($optionalRequired as $field => $options)
                                        <div>
                                            <label class="form-label" for="optionalRequired-{{ $field }}">
                                                {{ ucfirst($field) }}
                                            </label>
                                            <select wire:model.live="optionalRequiredValues.{{ $field }}" 
                                                    class="form-control"
                                                    id="optionalRequired-{{ $field }}">
                                                <option value="">{{ __('Select') }}</option>
                                                @foreach ($options as $option)
                                                    <option value="{{ is_array($option) ? $option['value'] : $option }}">
                                                        {{ is_array($option) ? $option['label'] : $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center justify-end">
                            <button type="submit" class="btn btn-primary" {{ !$file ? 'disabled' : '' }}>
                                <iconify-icon icon="mdi:import" height="16" width="16" class="ml-2"></iconify-icon>
                                {{ __('Import ' . ucfirst($modelType)) }}
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Right: Validation Results --}}
                <div class="rounded-2xl">
                    @if (isset($validationErrors) && is_array($validationErrors) && count($validationErrors))
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded p-4 mb-4 max-h-[500px] overflow-y-auto">
                            <h3 class="font-semibold text-red-700 dark:text-red-400 mb-2">{{ __('Import Errors') }}</h3>
                            <ul class="space-y-2">
                                @foreach ($validationErrors as $row => $fields)
                                    <li>
                                        <span class="font-medium text-red-600 dark:text-red-400">{{ __('Row') }} {{ (int) $row + 2 }}:</span>
                                        <ul class="ml-4 list-disc">
                                            @if(is_array($fields))
                                                @foreach ($fields as $field => $error)
                                                    <li class="text-red-800 dark:text-red-300">
                                                        <span class="font-semibold">{{ ucfirst($field) }}:</span> {{ $error }}
                                                    </li>
                                                @endforeach
                                            @else
                                                <li class="text-red-800 dark:text-red-300">{{ $fields }}</li>
                                            @endif
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($showFileHeaders)
                        <div class="rounded-md bg-gray-50 dark:bg-gray-700/30 p-4 shadow">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                {{ __('File Columns') }}
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($fileColumns as $column)
                                    @php
                                        $isValid = in_array($column, $validColumns);
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm {{ $isValid ? 'bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200' : 'bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-200' }}">
                                        {{ $column }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </x-card>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('show-import-success', (data) => {
            if (window.showToast) {
                window.showToast('success', 'Import Complete', data[0].message);
            }
            setTimeout(() => {
                Livewire.dispatch('resetFormDelayed');
            }, 3000);
        });
    });
</script>
@endpush
