<x-buttons.action-item
    type="link"
    :href="route('admin.notifications.preview-page', $notification->id)"
    target="_blank"
    icon="lucide:eye"
    :label="__('Preview')"
/>

<x-buttons.action-item
    type="button"
    :onClick="'openTestEmailModal(' . $notification->id . ')'"
    icon="lucide:mail"
    :label="__('Test')"
/>
