<div>
    @if($message)
        <div class="mb-4 p-4 rounded-lg {{ str_contains($message, 'Error') ? 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800' : 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800' }}">
            <div class="flex items-start gap-3">
                <iconify-icon icon="{{ str_contains($message, 'Error') ? 'feather:alert-circle' : 'feather:check-circle' }}" height="20" width="20" class="mt-0.5"></iconify-icon>
                <div class="flex-1">
                    <p class="font-medium">{{ $message }}</p>
                    
                    @if(!empty($importStats))
                        <div class="mt-3 grid grid-cols-3 gap-4 text-sm">
                            <div class="bg-white/50 dark:bg-gray-800/50 rounded-lg p-3">
                                <div class="text-xs opacity-75">Total Rows</div>
                                <div class="text-lg font-bold">{{ $importStats['total'] }}</div>
                            </div>
                            <div class="bg-white/50 dark:bg-gray-800/50 rounded-lg p-3">
                                <div class="text-xs opacity-75">Imported</div>
                                <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ $importStats['imported'] }}</div>
                            </div>
                            <div class="bg-white/50 dark:bg-gray-800/50 rounded-lg p-3">
                                <div class="text-xs opacity-75">Skipped</div>
                                <div class="text-lg font-bold text-red-600 dark:text-red-400">{{ $importStats['skipped'] }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if(!empty($importErrors))
        <div class="mb-4 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-lg">
            <div class="p-4 border-b border-red-200 dark:border-red-800">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="feather:alert-triangle" class="text-red-600 dark:text-red-400" height="20" width="20"></iconify-icon>
                    <h4 class="font-semibold text-red-900 dark:text-red-300">{{ __('Import Errors') }} ({{ count($importErrors) }})</h4>
                </div>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-red-100 dark:bg-red-900/20 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-red-900 dark:text-red-300 font-medium">Row</th>
                            <th class="px-4 py-2 text-left text-red-900 dark:text-red-300 font-medium">Error Type</th>
                            <th class="px-4 py-2 text-left text-red-900 dark:text-red-300 font-medium">Message</th>
                            <th class="px-4 py-2 text-left text-red-900 dark:text-red-300 font-medium">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-red-200 dark:divide-red-800">
                        @foreach($importErrors as $error)
                            <tr class="hover:bg-red-100/50 dark:hover:bg-red-900/10">
                                <td class="px-4 py-3 text-red-900 dark:text-red-300 font-medium">{{ $error['row'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-200 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                        {{ $error['type'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-red-700 dark:text-red-400">{{ $error['message'] }}</td>
                                <td class="px-4 py-3">
                                    <details class="text-xs">
                                        <summary class="cursor-pointer text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">View Data</summary>
                                        <pre class="mt-2 p-2 bg-red-100 dark:bg-red-900/20 rounded text-red-900 dark:text-red-300 overflow-x-auto">{{ json_encode($error['data'], JSON_PRETTY_PRINT) }}</pre>
                                    </details>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($mode === 'import')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <iconify-icon icon="feather:upload-cloud" class="text-blue-600 dark:text-blue-400" height="24" width="24"></iconify-icon>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Import Data') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Upload your CSV file to import records') }}</p>
                    </div>
                </div>
            </div>

            <form wire:submit.prevent="import" class="p-6 space-y-6">
                @if(!empty($filters))
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-3">
                            <iconify-icon icon="feather:filter" class="text-gray-400" height="18" width="18"></iconify-icon>
                            <h4 class="font-semibold text-gray-700 dark:text-gray-300">{{ __('Optional Fields') }}</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($filters as $field => $options)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ ucfirst(str_replace('_id', '', $field)) }}
                                    </label>
                                    <select wire:model="import_{{ $field }}" class="form-control">
                                        <option value="">{{ __('Select') }}</option>
                                        @foreach($options as $value => $label)
                                            <option value="{{ $value }}">{{ strip_tags(is_array($label) ? ($label['label'] ?? $label['name'] ?? $value) : $label) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Drag & Drop File Upload -->
                <div class="{{ !empty($filters) ? 'border-t border-gray-200 dark:border-gray-700 pt-6' : '' }}">
                    @if(!empty($fileValidation))
                        <div class="mb-6 space-y-4">
                            <!-- File Info Card -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border {{ !empty($fileValidation['errors']) ? 'border-red-300 dark:border-red-700' : 'border-blue-200 dark:border-blue-800' }} rounded-xl p-4">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-3 {{ !empty($fileValidation['errors']) ? 'bg-red-100 dark:bg-red-900/30' : 'bg-blue-100 dark:bg-blue-900/30' }} rounded-lg">
                                            <iconify-icon icon="{{ !empty($fileValidation['errors']) ? 'feather:alert-circle' : 'feather:file-text' }}" class="{{ !empty($fileValidation['errors']) ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400' }}" height="24" width="24"></iconify-icon>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $fileValidation['name'] }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $fileValidation['sizeFormatted'] }} • {{ strtoupper($fileValidation['type']) }}</p>
                                        </div>
                                    </div>
                                    <button type="button" wire:click="$set('file', null)" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                        <iconify-icon icon="feather:x" height="20" width="20"></iconify-icon>
                                    </button>
                                </div>

                                <!-- Validation Status -->
                                <div class="grid grid-cols-3 gap-3 mb-4">
                                    <div class="bg-white/60 dark:bg-gray-800/60 rounded-lg p-3 text-center">
                                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">{{ __('Total Rows') }}</div>
                                        <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $fileValidation['rowCount'] ?? 0 }}</div>
                                    </div>
                                    <div class="bg-white/60 dark:bg-gray-800/60 rounded-lg p-3 text-center">
                                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">{{ __('Columns') }}</div>
                                        <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $fileValidation['headerCount'] ?? 0 }}</div>
                                    </div>
                                    <div class="bg-white/60 dark:bg-gray-800/60 rounded-lg p-3 text-center">
                                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">{{ __('Status') }}</div>
                                        <div class="text-lg font-bold {{ !empty($fileValidation['errors']) ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                            @if(!empty($fileValidation['errors']))
                                                <iconify-icon icon="feather:x-circle" height="20" width="20"></iconify-icon>
                                            @else
                                                <iconify-icon icon="feather:check-circle" height="20" width="20"></iconify-icon>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Errors -->
                                @if(!empty($fileValidation['errors']))
                                    <div class="mb-4 p-3 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
                                        <div class="flex items-center gap-2 mb-2">
                                            <iconify-icon icon="feather:alert-triangle" class="text-red-600 dark:text-red-400" height="16" width="16"></iconify-icon>
                                            <span class="font-semibold text-red-900 dark:text-red-300 text-sm">{{ __('Errors Found') }}</span>
                                        </div>
                                        <ul class="space-y-1 text-sm text-red-700 dark:text-red-400">
                                            @foreach($fileValidation['errors'] as $error)
                                                <li class="flex items-start gap-2">
                                                    <span class="mt-1">•</span>
                                                    <span>{{ $error }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <!-- Warnings -->
                                @if(!empty($fileValidation['warnings']))
                                    <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                        <div class="flex items-center gap-2 mb-2">
                                            <iconify-icon icon="feather:alert-circle" class="text-amber-600 dark:text-amber-400" height="16" width="16"></iconify-icon>
                                            <span class="font-semibold text-amber-900 dark:text-amber-300 text-sm">{{ __('Warnings') }}</span>
                                        </div>
                                        <ul class="space-y-1 text-sm text-amber-700 dark:text-amber-400">
                                            @foreach($fileValidation['warnings'] as $warning)
                                                <li class="flex items-start gap-2">
                                                    <span class="mt-1">•</span>
                                                    <span>{{ $warning }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <!-- Column Headers -->
                                @if(!empty($fileValidation['headers']))
                                    <div class="mb-4">
                                        <div class="flex items-center gap-2 mb-2">
                                            <iconify-icon icon="feather:columns" class="text-gray-600 dark:text-gray-400" height="16" width="16"></iconify-icon>
                                            <span class="font-semibold text-gray-900 dark:text-gray-100 text-sm">{{ __('Detected Columns') }}</span>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($fileValidation['headers'] as $header)
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ in_array($header, $columns) ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}">
                                                    @if(in_array($header, $columns))
                                                        <iconify-icon icon="feather:check" height="12" width="12" class="mr-1"></iconify-icon>
                                                    @endif
                                                    {{ $header }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Data Preview -->
                                @if(!empty($fileValidation['preview']))
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <iconify-icon icon="feather:eye" class="text-gray-600 dark:text-gray-400" height="16" width="16"></iconify-icon>
                                            <span class="font-semibold text-gray-900 dark:text-gray-100 text-sm">{{ __('Data Preview') }} ({{ __('First 5 rows') }})</span>
                                        </div>
                                        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                            <table class="w-full text-xs">
                                                <thead class="bg-gray-50 dark:bg-gray-900">
                                                    <tr>
                                                        @foreach(array_keys($fileValidation['preview'][0]) as $header)
                                                            <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">{{ $header }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                    @foreach($fileValidation['preview'] as $row)
                                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                                            @foreach($row as $cell)
                                                                <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $cell ?: '-' }}</td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                <!-- Data Type Validation Errors -->
                                @if(!empty($fileValidation['dataTypeErrors']))
                                    <div class="mt-4">
                                        <div class="flex items-center gap-2 mb-2">
                                            <iconify-icon icon="feather:alert-octagon" class="text-orange-600 dark:text-orange-400" height="16" width="16"></iconify-icon>
                                            <span class="font-semibold text-gray-900 dark:text-gray-100 text-sm">{{ __('Data Type Issues') }} ({{ count($fileValidation['dataTypeErrors']) }})</span>
                                        </div>
                                        <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg overflow-hidden">
                                            <div class="max-h-48 overflow-y-auto">
                                                <table class="w-full text-xs">
                                                    <thead class="bg-orange-100 dark:bg-orange-900/30 sticky top-0">
                                                        <tr>
                                                            <th class="px-3 py-2 text-left font-medium text-orange-900 dark:text-orange-300">Row</th>
                                                            <th class="px-3 py-2 text-left font-medium text-orange-900 dark:text-orange-300">Column</th>
                                                            <th class="px-3 py-2 text-left font-medium text-orange-900 dark:text-orange-300">Value</th>
                                                            <th class="px-3 py-2 text-left font-medium text-orange-900 dark:text-orange-300">Expected</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-orange-200 dark:divide-orange-800">
                                                        @foreach($fileValidation['dataTypeErrors'] as $error)
                                                            <tr class="hover:bg-orange-100/50 dark:hover:bg-orange-900/10">
                                                                <td class="px-3 py-2 text-orange-900 dark:text-orange-300 font-medium">{{ $error['row'] }}</td>
                                                                <td class="px-3 py-2 text-orange-700 dark:text-orange-400">{{ $error['column'] }}</td>
                                                                <td class="px-3 py-2 text-orange-700 dark:text-orange-400 font-mono">{{ $error['value'] }}</td>
                                                                <td class="px-3 py-2 text-orange-700 dark:text-orange-400">{{ $error['expected'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <div class="relative">
                        <input type="file" wire:model="file" accept=".csv" id="file-upload" class="hidden">
                        <label for="file-upload" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-xl cursor-pointer bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <iconify-icon icon="feather:upload-cloud" class="text-gray-400 mb-4" height="48" width="48"></iconify-icon>
                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-semibold">{{ __('Click to upload') }}</span> {{ __('or drag and drop') }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('CSV file (MAX. 10MB)') }}</p>
                            </div>
                        </label>
                        @error('file') 
                            <div class="flex items-center gap-2 mt-3 text-red-600 dark:text-red-400 text-sm">
                                <iconify-icon icon="feather:alert-circle" height="16" width="16"></iconify-icon>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                        <div wire:loading wire:target="file" class="absolute inset-0 flex items-center justify-center bg-white/80 dark:bg-gray-800/80 rounded-xl">
                            <div class="flex flex-col items-center gap-3">
                                <iconify-icon icon="feather:loader" class="animate-spin text-blue-600" height="32" width="32"></iconify-icon>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Analyzing file...') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        <iconify-icon icon="feather:arrow-left" height="16" width="16" class="mr-2"></iconify-icon>
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" {{ !empty($fileValidation['errors']) || empty($fileValidation) ? 'disabled' : '' }}>
                        <iconify-icon icon="feather:upload" height="16" width="16" class="mr-2"></iconify-icon>
                        <span wire:loading.remove wire:target="import">{{ __('Import Data') }}</span>
                        <span wire:loading wire:target="import" class="flex items-center gap-2">
                            <iconify-icon icon="feather:loader" class="animate-spin" height="16" width="16"></iconify-icon>
                            {{ __('Importing...') }}
                        </span>
                    </button>
                </div>

                <!-- Sample Download -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-600 rounded-lg">
                                <iconify-icon icon="feather:download" class="text-white" height="20" width="20"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-sm">{{ __('Need a sample file?') }}</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Download to see the correct format') }}</p>
                            </div>
                        </div>
                        <button wire:click="downloadSample" type="button" class="btn btn-primary" wire:loading.attr="disabled">
                            <iconify-icon icon="feather:download" height="16" width="16" class="mr-2"></iconify-icon>
                            <span wire:loading.remove wire:target="downloadSample">{{ __('Download') }}</span>
                            <span wire:loading wire:target="downloadSample" class="flex items-center gap-2">
                                <iconify-icon icon="feather:loader" class="animate-spin" height="16" width="16"></iconify-icon>
                                {{ __('Downloading...') }}
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @elseif($mode === 'export')
        <form wire:submit.prevent="export" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            @if(!empty($filters))
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ __('Filters') }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($filters as $field => $options)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ ucfirst(str_replace('_id', '', $field)) }}
                                </label>
                                <select wire:model.live="filtersValues.{{ $field }}" class="form-control">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($options as $value => $label)
                                        <option value="{{ $value }}">{{ strip_tags(is_array($label) ? ($label['label'] ?? $label['name'] ?? $value) : $label) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mb-6">
                <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ __('Select Columns') }}</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($columns as $col)
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model.live="selectedColumns" value="{{ $col }}" class="form-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>{{ $col }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary" {{ count($selectedColumns) < 1 ? 'disabled' : '' }} wire:loading.attr="disabled">
                    <iconify-icon icon="feather:download" height="16" width="16" class="mr-2"></iconify-icon>
                    <span wire:loading.remove wire:target="export">{{ __('Export Data') }}</span>
                    <span wire:loading wire:target="export">{{ __('Exporting...') }}</span>
                </button>
            </div>
        </form>
    @endif
</div>
