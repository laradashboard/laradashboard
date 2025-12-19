<x-buttons.action-item
    type="button"
    onClick="openTestModal({{ $connection->id }}, '{{ e($connection->name) }}')"
    icon="lucide:mail"
    :label="__('Send Test')"
/>
