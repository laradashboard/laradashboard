<div class="flex flex-col gap-1">
    <span class="text-sm text-gray-500 dark:text-gray-400">v{{ $module->version }}</span>
    @if (!$module->isCompatibleWithCore())
        <span class="inline-flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400" title="{{ __('Requires LaraDashboard v:version', ['version' => $module->min_laradashboard_required]) }}">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            {{ __('Update needed') }}
        </span>
    @endif
</div>
