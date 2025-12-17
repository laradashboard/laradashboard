<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $module->status ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' }}">
    {{ $module->status ? __('Enabled') : __('Disabled') }}
</span>
