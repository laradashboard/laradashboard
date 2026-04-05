<x-buttons.action-item
    type="button"
    onClick="toggleConnection({{ $connection->id }})"
    icon="{{ $connection->is_active ? 'lucide:pause' : 'lucide:play' }}"
    :label="$connection->is_active ? __('Disable') : __('Enable')"
/>
