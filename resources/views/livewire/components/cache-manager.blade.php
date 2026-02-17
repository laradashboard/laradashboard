<div>
    <x-card>
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <iconify-icon icon="lucide:database" width="20" height="20" class="text-gray-500"></iconify-icon>
                {{ __('Cache Management') }}
            </div>
        </x-slot>

        <div class="space-y-6">
            {{-- Success Message --}}
            @if ($successMessage)
                <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="lucide:check-circle" class="text-green-600 dark:text-green-400"></iconify-icon>
                        <span class="text-green-800 dark:text-green-200">{{ $successMessage }}</span>
                    </div>
                    @if ($lastCleared)
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">{{ __('Cleared at:') }} {{ $lastCleared }}</p>
                    @endif
                </div>
            @endif

            {{-- Cache Actions Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Clear All Caches --}}
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                            <iconify-icon icon="lucide:trash-2" class="text-xl text-red-600 dark:text-red-400"></iconify-icon>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Clear All Caches') }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Clear application, config, route, and view caches at once.') }}</p>
                            <button type="button"
                                    wire:click="clearAllCaches"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-wait"
                                    class="btn btn-sm btn-danger mt-3">
                                <span wire:loading.remove wire:target="clearAllCaches">{{ __('Clear All') }}</span>
                                <span wire:loading wire:target="clearAllCaches" class="flex items-center gap-1">
                                    <iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon>
                                    {{ __('Clearing...') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Optimize Application --}}
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                            <iconify-icon icon="lucide:zap" class="text-xl text-green-600 dark:text-green-400"></iconify-icon>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Optimize Application') }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Cache config, routes, and views for better performance.') }}</p>
                            <button type="button"
                                    wire:click="optimizeApplication"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-wait"
                                    class="btn btn-sm btn-success mt-3">
                                <span wire:loading.remove wire:target="optimizeApplication">{{ __('Optimize') }}</span>
                                <span wire:loading wire:target="optimizeApplication" class="flex items-center gap-1">
                                    <iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon>
                                    {{ __('Optimizing...') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Clear Application Cache --}}
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                            <iconify-icon icon="lucide:database" class="text-xl text-blue-600 dark:text-blue-400"></iconify-icon>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Application Cache') }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Clear cached data stored by the application.') }}</p>
                            <button type="button"
                                    wire:click="clearApplicationCache"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-wait"
                                    class="btn btn-sm btn-secondary mt-3">
                                <span wire:loading.remove wire:target="clearApplicationCache">{{ __('Clear') }}</span>
                                <span wire:loading wire:target="clearApplicationCache" class="flex items-center gap-1">
                                    <iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon>
                                    {{ __('Clearing...') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Clear Config Cache --}}
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center shrink-0">
                            <iconify-icon icon="lucide:settings" class="text-xl text-purple-600 dark:text-purple-400"></iconify-icon>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Config Cache') }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Clear cached configuration files.') }}</p>
                            <button type="button"
                                    wire:click="clearConfigCache"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-wait"
                                    class="btn btn-sm btn-secondary mt-3">
                                <span wire:loading.remove wire:target="clearConfigCache">{{ __('Clear') }}</span>
                                <span wire:loading wire:target="clearConfigCache" class="flex items-center gap-1">
                                    <iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon>
                                    {{ __('Clearing...') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Clear Route Cache --}}
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                            <iconify-icon icon="lucide:route" class="text-xl text-amber-600 dark:text-amber-400"></iconify-icon>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Route Cache') }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Clear cached route registrations.') }}</p>
                            <button type="button"
                                    wire:click="clearRouteCache"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-wait"
                                    class="btn btn-sm btn-secondary mt-3">
                                <span wire:loading.remove wire:target="clearRouteCache">{{ __('Clear') }}</span>
                                <span wire:loading wire:target="clearRouteCache" class="flex items-center gap-1">
                                    <iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon>
                                    {{ __('Clearing...') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Clear View Cache --}}
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                            <iconify-icon icon="lucide:layout" class="text-xl text-indigo-600 dark:text-indigo-400"></iconify-icon>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ __('View Cache') }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Clear compiled Blade templates.') }}</p>
                            <button type="button"
                                    wire:click="clearViewCache"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-wait"
                                    class="btn btn-sm btn-secondary mt-3">
                                <span wire:loading.remove wire:target="clearViewCache">{{ __('Clear') }}</span>
                                <span wire:loading wire:target="clearViewCache" class="flex items-center gap-1">
                                    <iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon>
                                    {{ __('Clearing...') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Note --}}
            <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                <div class="flex items-start gap-2">
                    <iconify-icon icon="lucide:info" class="text-blue-600 dark:text-blue-400 mt-0.5"></iconify-icon>
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-medium">{{ __('When to clear caches?') }}</p>
                        <ul class="list-disc list-inside mt-1 text-xs space-y-1">
                            <li>{{ __('After updating .env file settings') }}</li>
                            <li>{{ __('After installing or updating modules') }}</li>
                            <li>{{ __('When you see stale or incorrect data') }}</li>
                            <li>{{ __('After changing configuration files') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </x-card>
</div>
