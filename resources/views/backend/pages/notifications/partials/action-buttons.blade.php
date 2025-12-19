<x-buttons.action-item
    type="button"
    onClick="openTestEmailModal({{ $notification->id }}, 'notification')"
    icon="lucide:mail"
    :label="__('Test')"
/>
