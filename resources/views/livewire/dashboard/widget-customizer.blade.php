<div
    class="relative shrink-0"
    x-data="{ open: @entangle('showPanel').live }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    @if($hasWidgets)
        <button
            type="button"
            wire:click="togglePanel"
            class="inline-flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
            aria-expanded="{{ $showPanel ? 'true' : 'false' }}"
            aria-haspopup="dialog"
        >
            <iconify-icon icon="heroicons:adjustments-horizontal" width="16" height="16" aria-hidden="true"></iconify-icon>
            {{ __('Customize dashboard') }}
        </button>

        @if($showPanel)
            <div
                class="absolute right-0 z-50 mt-2 w-[min(100vw-2rem,28rem)] rounded-lg bg-white p-4 shadow-xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10"
                role="dialog"
                aria-label="{{ __('Customize dashboard widgets') }}"
            >
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white">{{ __('Dashboard widgets') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Choose what appears on your dashboard. Hidden widgets can be restored here anytime.') }}
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="togglePanel"
                        class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                        aria-label="{{ __('Close') }}"
                    >
                        <iconify-icon icon="heroicons:x-mark" width="18" height="18" aria-hidden="true"></iconify-icon>
                    </button>
                </div>

                <div class="max-h-[min(60vh,24rem)] space-y-5 overflow-y-auto pr-1">
                    @foreach($widgetGroups as $groupName => $widgets)
                        <div>
                            <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {{ $groupName }}
                            </h4>
                            <div class="space-y-0.5">
                                @foreach($widgets as $widget)
                                    <label
                                        class="flex cursor-pointer items-center gap-3 rounded-md px-2 py-2 transition hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        wire:key="dashboard-widget-pref-{{ $widget['id'] }}"
                                    >
                                        <input
                                            type="checkbox"
                                            wire:model="selectedVisibleIds"
                                            value="{{ $widget['id'] }}"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900"
                                        >
                                        <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-800 dark:text-white">
                                            {{ $widget['label'] }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Checked widgets will appear on your dashboard.') }}
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        wire:click="savePreferences"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        {{ __('Save preferences') }}
                    </button>
                    <button
                        type="button"
                        wire:click="resetPreferences"
                        class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        {{ __('Show all widgets') }}
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>
