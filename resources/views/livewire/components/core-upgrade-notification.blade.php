<div>
    @if ($hasUpdate)
        @can('settings.view')
            <x-tooltip title="{{ __('Update available') }}: v{{ $latestVersion }}" position="bottom">
                <a href="{{ route('admin.core-upgrades.index') }}"
                    class="relative flex p-2 items-center justify-center rounded-full transition-colors
                        {{ $isCritical
                            ? 'text-red-600 hover:bg-red-100 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-900/30 dark:hover:text-red-300'
                            : 'text-amber-600 hover:bg-amber-100 hover:text-amber-700 dark:text-amber-400 dark:hover:bg-amber-900/30 dark:hover:text-amber-300'
                        }}">
                    <iconify-icon icon="lucide:arrow-up-circle" width="22" height="22"></iconify-icon>
                    <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full text-xs font-semibold
                        {{ $isCritical
                            ? 'bg-red-500 text-white'
                            : 'bg-amber-500 text-white'
                        }}">
                        !
                    </span>
                </a>
            </x-tooltip>
        @endcan
    @endif
</div>
