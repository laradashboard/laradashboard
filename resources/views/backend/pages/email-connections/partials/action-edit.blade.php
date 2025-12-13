<x-buttons.action-item
    type="button"
    onClick="editConnection({{ $connection->id }})"
    icon="lucide:pencil"
    :label="__('Edit')"
/>
