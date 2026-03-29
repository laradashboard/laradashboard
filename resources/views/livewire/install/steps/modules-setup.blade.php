<div class="space-y-6" wire:init="loadModulesFromMarketplace">
    {{-- Loading State --}}
    @if (!$modulesLoaded)
        <div class="flex flex-col items-center justify-center py-12">
            <svg class="animate-spin w-8 h-8 text-primary mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-gray-500 dark:text-gray-400">{{ __('Loading available modules from marketplace...') }}</p>
        </div>
    @else
        {{-- Marketplace Error Warning --}}
        @if ($modulesFetchError)
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-yellow-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <div class="text-sm text-yellow-700 dark:text-yellow-300">
                        <p class="font-medium mb-1">{{ __('Marketplace Unavailable') }}</p>
                        <p>{{ $modulesFetchError }}</p>
                        @if (!empty($availableModules))
                            <p class="mt-1">{{ __('Showing locally available modules instead.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- No Modules Available --}}
        @if (empty($availableModules))
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">{{ __('No modules available to install.') }}</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">{{ __('You can install modules later from the admin panel.') }}</p>
            </div>
        @else
            {{-- Select/Deselect All --}}
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __(':selected of :total modules selected', ['selected' => count($selectedModules), 'total' => count($availableModules)]) }}
                </p>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        wire:click="selectAllModules"
                        class="text-sm text-primary hover:underline"
                    >
                        {{ __('Select All') }}
                    </button>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <button
                        type="button"
                        wire:click="deselectAllModules"
                        class="text-sm text-gray-500 dark:text-gray-400 hover:underline"
                    >
                        {{ __('Deselect All') }}
                    </button>
                </div>
            </div>

            {{-- Module List --}}
            <div class="space-y-3">
                @foreach ($availableModules as $module)
                    @php
                        $slug = $module['slug'];
                        $isSelected = in_array($slug, $selectedModules);
                        $isFree = $module['is_free'] ?? true;
                        $installResult = $moduleInstallResults[$slug] ?? null;
                    @endphp
                    <div
                        wire:key="module-{{ $slug }}"
                        @if ($isFree)
                            wire:click="toggleModule('{{ $slug }}')"
                        @endif
                        class="flex items-center gap-4 p-4 rounded-lg border-2 transition-colors
                            {{ !$isFree
                                ? 'border-gray-200 dark:border-gray-700 opacity-60 cursor-not-allowed'
                                : ($isSelected
                                    ? 'border-primary bg-primary/5 dark:bg-primary/10 cursor-pointer'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 cursor-pointer') }}"
                        role="checkbox"
                        aria-checked="{{ $isSelected ? 'true' : 'false' }}"
                        aria-label="{{ $module['name'] }}"
                        @if (!$isFree) aria-disabled="true" @endif
                        tabindex="0"
                    >
                        {{-- Checkbox --}}
                        <div class="shrink-0">
                            @if ($isFree)
                                <div class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors
                                    {{ $isSelected
                                        ? 'bg-primary border-primary'
                                        : 'border-gray-300 dark:border-gray-600' }}">
                                    @if ($isSelected)
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </div>
                            @else
                                <div class="w-5 h-5 rounded border-2 border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Module Icon --}}
                        <div class="shrink-0 w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            @if (!empty($module['icons']))
                                @if (is_array($module['icons']) && !empty($module['icons']['1x']))
                                    <img src="{{ $module['icons']['1x'] }}" alt="" class="w-8 h-8 rounded" />
                                @elseif (is_string($module['icons']))
                                    <span class="text-lg">
                                        <iconify-icon icon="{{ $module['icons'] }}" aria-hidden="true"></iconify-icon>
                                    </span>
                                @else
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                @endif
                            @else
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            @endif
                        </div>

                        {{-- Module Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $module['name'] }}</h4>
                                @if ($isFree)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                        {{ __('Free') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                        {{ __('Pro') }}
                                    </span>
                                @endif
                                @if (!empty($module['version']))
                                    <span class="text-xs text-gray-400 dark:text-gray-500">v{{ $module['version'] }}</span>
                                @endif
                                @if (!empty($module['is_local']))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                        {{ __('Bundled') }}
                                    </span>
                                @endif
                            </div>
                            @if (!$isFree)
                                <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">{{ __('Requires license — install from admin panel after activation') }}</p>
                            @elseif (!empty($module['description']))
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ \Illuminate\Support\Str::limit(strip_tags($module['description']), 150) }}</p>
                            @endif
                        </div>

                        {{-- Install Result Badge --}}
                        @if ($installResult)
                            <div class="shrink-0">
                                @if ($installResult['success'])
                                    <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        {{ __('Installed') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs text-red-600 dark:text-red-400" title="{{ $installResult['message'] }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        {{ __('Failed') }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Info Box --}}
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-blue-700 dark:text-blue-300">
                    <p class="font-medium mb-1">{{ __('Optional Step') }}</p>
                    <p>{{ __('This step is optional. You can skip it and manage modules later from the admin panel under Modules section.') }}</p>
                </div>
            </div>
        </div>
    @endif
</div>
