<div>
    @if($emailTemplate->is_active)
        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
            {{ __('Active') }}
        </span>
    @else
        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
            {{ __('Inactive') }}
        </span>
    @endif
</div>