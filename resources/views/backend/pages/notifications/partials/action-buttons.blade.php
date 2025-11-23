<x-buttons.action-item
    type="button"
    :onClick="'openTestEmailModal(' . $notification->id . ')'"
    icon="lucide:mail"
    :label="__('Test')"
/>
