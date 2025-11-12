<x-buttons.action-item
    type="button"
    :onClick="'openTestEmailModal(' . $emailTemplate->id . ')'"
    icon="lucide:mail"
    :label="__('Test')"
/>
