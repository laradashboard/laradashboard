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
    <x-slot name="breadcrumbsData">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs">
            <x-slot name="title_after">
                <x-popover position="bottom" width="w-[340px]">
                    <x-slot name="trigger">
                        <iconify-icon icon="lucide:info" class="text-lg ml-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" title="{{ __('How Email Connections Work') }}"></iconify-icon>
                    </x-slot>

                    <div class="w-[340px] p-4 font-normal">
                        <h3 class="font-medium text-gray-700 dark:text-white mb-2">{{ __('How Email Connections Work') }}</h3>
                        <p class="mb-3 text-sm text-gray-600 dark:text-gray-300">{{ __('Email connections provide a unified way to send all emails from your application.') }}</p>

                        <div class="space-y-3 text-sm">
                            <div class="flex items-start gap-2">
                                <div class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400">1</span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <strong class="text-gray-700 dark:text-white">{{ __('No connections added:') }}</strong>
                                    {{ __('Emails are sent using your .env file credentials (MAIL_HOST, MAIL_USERNAME, etc.)') }}
                                </p>
                            </div>

                            <div class="flex items-start gap-2">
                                <div class="w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-medium text-green-600 dark:text-green-400">2</span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <strong class="text-gray-700 dark:text-white">{{ __('With connections:') }}</strong>
                                    {{ __('The system uses the "best" connection - either the one marked as default, or the highest priority active connection.') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 flex items-start gap-2">
                                <iconify-icon icon="lucide:lightbulb" class="text-sm mt-0.5 flex-shrink-0"></iconify-icon>
                                <span>{{ __('Tip: All emails including password resets, notifications, and CRM emails will use your configured connection.') }}</span>
                            </p>
                        </div>
                    </div>
                </x-popover>
            </x-slot>
        </x-breadcrumbs>
    </x-slot>

    <x-email-tabs.navigation currentTab="connections" />

    <div class="space-y-6">
        <livewire:datatable.email-connection-datatable lazy />
    </div>

    @include('backend.pages.email-connections.partials.provider-selector-modal')
    @include('backend.pages.email-connections.partials.connection-form-drawer')
    @include('backend.pages.email-connections.partials.test-connection-modal')
</x-layouts.backend-layout>

