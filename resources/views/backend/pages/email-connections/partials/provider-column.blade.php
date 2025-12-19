<div class="flex items-center gap-2">
    @if($connection->provider_icon)
        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
            <iconify-icon icon="{{ $connection->provider_icon }}" class="text-lg text-gray-600 dark:text-gray-300"></iconify-icon>
        </div>
    @endif
    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ $connection->provider_label }}
    </span>
</div>
