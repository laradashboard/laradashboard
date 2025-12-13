<div class="flex items-center gap-2">
    <span class="text-sm font-medium text-gray-900 dark:text-white">
        {{ $connection->name }}
    </span>
    @if($connection->is_default)
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary">
            {{ __('Default') }}
        </span>
    @endif
</div>
