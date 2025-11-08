<div>
    @if($emailTemplate->is_default)
        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
            {{ __('Default') }}
        </span>
    @else
        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100">
            {{ __('No') }}
        </span>
    @endif
</div>