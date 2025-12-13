@push('head-scripts')
<script>
    // Email connection store data - defined early so it's available when Alpine initializes
    window._emailConnectionStore = {
        editingId: null,
        providerType: null,
        formData: {},

        reset() {
            this.editingId = null;
            this.providerType = null;
            this.formData = {};
        },

        setProvider(type) {
            this.providerType = type;
        },

        setEditConnection(connection, provider) {
            this.editingId = connection.id;
            this.providerType = connection.provider_type;
            this.formData = {
                name: connection.name,
                from_email: connection.from_email,
                from_name: connection.from_name || '',
                force_from_email: connection.force_from_email || false,
                force_from_name: connection.force_from_name || false,
                is_active: connection.is_active,
                is_default: connection.is_default,
                priority: connection.priority,
                settings: connection.settings || {},
                credentials: connection.credentials || {},
            };
        }
    };

    // Register Alpine store when Alpine initializes
    document.addEventListener('alpine:init', () => {
        Alpine.store('emailConnection', window._emailConnectionStore);
    });

    // Global functions for email connections
    window.getEmailConnectionStore = function() {
        // Try Alpine store first, fall back to window object
        if (typeof Alpine !== 'undefined' && Alpine.store && Alpine.store('emailConnection')) {
            return Alpine.store('emailConnection');
        }
        return window._emailConnectionStore;
    };

    window.openProviderSelector = function() {
        window.getEmailConnectionStore().reset();
        window.dispatchEvent(new CustomEvent('open-provider-selector'));
    };

    window.selectProvider = function(providerType) {
        window.getEmailConnectionStore().setProvider(providerType);
        window.dispatchEvent(new CustomEvent('close-provider-selector'));
        window.dispatchEvent(new CustomEvent('open-connection-form'));
    };

    window.editConnection = async function(connectionId) {
        try {
            const response = await fetch(`{{ route('admin.email-connections.index') }}/${connectionId}`);
            const data = await response.json();

            if (data.connection && data.provider) {
                window.getEmailConnectionStore().setEditConnection(data.connection, data.provider);
                window.dispatchEvent(new CustomEvent('open-connection-form'));
            }
        } catch (error) {
            console.error('Error loading connection:', error);
        }
    };

    window.openTestModal = function(connectionId, connectionName) {
        window.dispatchEvent(new CustomEvent('open-test-connection-modal', {
            detail: { id: connectionId, name: connectionName }
        }));
    };

    window.setAsDefault = async function(connectionId) {
        try {
            const response = await fetch(`{{ route('admin.email-connections.index') }}/${connectionId}/default`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                window.location.reload();
            }
        } catch (error) {
            console.error('Error setting default:', error);
        }
    };
</script>
@endpush

<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-email-tabs.navigation currentTab="connections" />

    <div class="space-y-6">
        <livewire:datatable.email-connection-datatable lazy />
    </div>

    @include('backend.pages.email-connections.partials.provider-selector-modal')
    @include('backend.pages.email-connections.partials.connection-form-drawer')
    @include('backend.pages.email-connections.partials.test-connection-modal')
</x-layouts.backend-layout>

