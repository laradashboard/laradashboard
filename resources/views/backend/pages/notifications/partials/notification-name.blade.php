<div>
    <a href="{{ route('admin.notifications.show', $notification->id) }}" class="font-medium text-primary hover:underline">
        {{ $notification->name }}
    </a>
    @if($notification->description)
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($notification->description, 60) }}</p>
    @endif
</div>
