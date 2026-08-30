@if(config('app.demo_mode', false))
<x-popover position="bottom" width="w-[280px]">
    <x-slot name="trigger">
        <span class="rounded-radius border border-warning bg-warning px-2 py-1 text-xs font-medium text-warning-500 dark:border-gray-900 dark:bg-gray-800 dark:text-warning-500 p-3 min-w-16 flex justify-center items-center gap-1 cursor-pointer">
            <iconify-icon icon="lucide:alert-triangle"></iconify-icon>
            {{ __("Demo") }}
        </span>
    </x-slot>

    <div class="w-[280px] p-4 font-normal">
        <h3 class="font-medium text-gray-700 dark:text-white mb-2">
            {{ __("Demo Mode Active") }}
        </h3>
        <p class="mb-2">
            {{ __("Demo mode is currently enabled for this site. Many features are disabled to prevent changes to the demo data.") }}
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __("Demo data resets every hour.") }}
        </p>
    </div>
</x-popover>
@endif
