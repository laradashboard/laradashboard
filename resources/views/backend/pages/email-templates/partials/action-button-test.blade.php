<x-buttons.action-item
    type="button"
    onClick="openTestEmailModal({{ $emailTemplate->id }}, 'email-template')"
    icon="lucide:mail"
    :label="__('Send Test Email')"
/>
