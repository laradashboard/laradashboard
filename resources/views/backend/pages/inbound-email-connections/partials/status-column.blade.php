<div class="flex flex-col gap-1">
    @if($connection->is_active)
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
            <iconify-icon icon="lucide:check-circle" class="mr-1"></iconify-icon>
            {{ __('Active') }}
        </span>
    @else
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
            <iconify-icon icon="lucide:pause-circle" class="mr-1"></iconify-icon>
            {{ __('Inactive') }}
        </span>
    @endif

    @if($connection->last_check_status === 'success')
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
            <iconify-icon icon="lucide:wifi" class="mr-1"></iconify-icon>
            {{ __('Connected') }}
        </span>
    @elseif($connection->last_check_status === 'failed')
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400" title="{{ $connection->last_check_message }}">
            <iconify-icon icon="lucide:wifi-off" class="mr-1"></iconify-icon>
            {{ __('Failed') }}
        </span>
    @else
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
            <iconify-icon icon="lucide:help-circle" class="mr-1"></iconify-icon>
            {{ __('Not Tested') }}
        </span>
    @endif
</div>
