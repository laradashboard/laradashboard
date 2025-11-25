<div class="flex items-center">
    <iconify-icon icon="{{ $notification->getNotificationTypeIcon() }}" class="mr-1 text-primary"></iconify-icon>
    <span class="text-sm text-gray-900 dark:text-white">{{ $notification->getNotificationTypeLabel() }}</span>
</div>
