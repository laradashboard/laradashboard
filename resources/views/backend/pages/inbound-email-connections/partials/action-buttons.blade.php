<x-buttons.action-item
    type="button"
    onClick="testInboundConnection({{ $connection->id }}, '{{ e($connection->name) }}')"
    icon="lucide:plug-zap"
    :label="__('Test Connection')"
/>

<x-buttons.action-item
    type="button"
    onClick="toggleInboundConnection({{ $connection->id }})"
    icon="{{ $connection->is_active ? 'lucide:pause' : 'lucide:play' }}"
    :label="$connection->is_active ? __('Deactivate') : __('Activate')"
/>

@if($connection->is_active)
<x-buttons.action-item
    type="button"
    onClick="processInboundNow({{ $connection->id }})"
    icon="lucide:refresh-cw"
    :label="__('Process Now')"
/>
@endif
