<x-buttons.action-item
    type="button"
    onClick="editInboundConnection({{ $connection->id }})"
    icon="lucide:pencil"
    :label="__('Edit')"
/>
