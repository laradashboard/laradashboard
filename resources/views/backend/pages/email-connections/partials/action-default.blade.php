<x-buttons.action-item
    type="button"
    onClick="setAsDefault({{ $connection->id }})"
    icon="lucide:star"
    :label="__('Make Default')"
/>
