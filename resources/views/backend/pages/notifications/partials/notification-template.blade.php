<div>
    @if($notification->emailTemplate)
        <a href="{{ route('admin.email-templates.show', $notification->email_template_id) }}" class="text-sm text-primary hover:underline">
            {{ $notification->emailTemplate->name }}
        </a>
    @else
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('N/A') }}</span>
    @endif
</div>
