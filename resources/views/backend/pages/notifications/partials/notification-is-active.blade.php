@if($notification->is_active)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
        <iconify-icon icon="lucide:check-circle" class="mr-1"></iconify-icon>
        {{ __('Active') }}
    </span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
        <iconify-icon icon="lucide:x-circle" class="mr-1"></iconify-icon>
        {{ __('Inactive') }}
    </span>
@endif
