<div class="flex items-center">
    <iconify-icon icon="{{ $notification->notification_type->icon() }}" class="mr-2 text-primary"></iconify-icon>
    <span class="text-sm text-gray-900 dark:text-white">{{ $notification->notification_type->label() }}</span>
</div>
