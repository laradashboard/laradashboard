@push('head-scripts')
<script>
    window._inboundConnectionStore = {
        editingId: null,
        formData: {
            name: '',
            imap_host: '',
            imap_port: 993,
            imap_encryption: 'ssl',
            imap_username: '',
            imap_password: '',
            imap_folder: 'INBOX',
            imap_validate_cert: true,
            delete_after_processing: false,
            mark_as_read: true,
            fetch_limit: 50,
            polling_interval: 5,
            email_connection_id: null,
            is_active: true,
        },

        reset() {
            this.editingId = null;
            this.formData = {
                name: '',
                imap_host: '',
                imap_port: 993,
                imap_encryption: 'ssl',
                imap_username: '',
                imap_password: '',
                imap_folder: 'INBOX',
                imap_validate_cert: true,
                delete_after_processing: false,
                mark_as_read: true,
                fetch_limit: 50,
                polling_interval: 5,
                email_connection_id: null,
                is_active: true,
            };
        },

        setEditConnection(connection) {
            this.editingId = connection.id;
            this.formData = {
                name: connection.name || '',
                imap_host: connection.imap_host || '',
                imap_port: connection.imap_port || 993,
                imap_encryption: connection.imap_encryption || 'ssl',
                imap_username: connection.imap_username || '',
                imap_password: connection.imap_password || '',
                imap_folder: connection.imap_folder || 'INBOX',
                imap_validate_cert: connection.imap_validate_cert ?? true,
                delete_after_processing: connection.delete_after_processing ?? false,
                mark_as_read: connection.mark_as_read ?? true,
                fetch_limit: connection.fetch_limit || 50,
                polling_interval: connection.polling_interval || 5,
                email_connection_id: connection.email_connection_id || null,
                is_active: connection.is_active ?? true,
            };
        }
    };

    document.addEventListener('alpine:init', () => {
        Alpine.store('inboundConnection', window._inboundConnectionStore);
    });

    window.getInboundConnectionStore = function() {
        if (typeof Alpine !== 'undefined' && Alpine.store && Alpine.store('inboundConnection')) {
            return Alpine.store('inboundConnection');
        }
        return window._inboundConnectionStore;
    };

    window.editInboundConnection = async function(connectionId) {
        try {
            const response = await fetch(`{{ route('admin.inbound-email-connections.index') }}/${connectionId}`);
            const data = await response.json();

            if (data.connection) {
                window.getInboundConnectionStore().setEditConnection(data.connection);
                window.dispatchEvent(new CustomEvent('open-inbound-connection-form'));
            }
        } catch (error) {
            console.error('Error loading connection:', error);
        }
    };

    window.testInboundConnection = async function(connectionId, connectionName) {
        const store = window.getInboundConnectionStore();
        window.dispatchEvent(new CustomEvent('open-test-inbound-modal', {
            detail: { id: connectionId, name: connectionName }
        }));
    };

    window.toggleInboundConnection = async function(connectionId) {
        try {
            const response = await fetch(`{{ route('admin.inbound-email-connections.index') }}/${connectionId}/toggle-active`, {
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
            console.error('Error toggling connection:', error);
        }
    };

    window.processInboundNow = async function(connectionId) {
        try {
            const response = await fetch(`{{ route('admin.inbound-email-connections.index') }}/${connectionId}/process-now`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();
            alert(data.message);

            if (data.success) {
                window.location.reload();
            }
        } catch (error) {
            console.error('Error processing connection:', error);
        }
    };
</script>
@endpush

<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-slot name="breadcrumbsData">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs">
            <x-slot name="title_after">
                <x-popover position="bottom" width="w-[380px]">
                    <x-slot name="trigger">
                        <iconify-icon icon="lucide:info" class="text-lg ml-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" title="{{ __('How Inbound Email Works') }}"></iconify-icon>
                    </x-slot>

                    <div class="w-[380px] p-4 font-normal">
                        <h3 class="font-medium text-gray-700 dark:text-white mb-2">{{ __('How Inbound Email Works') }}</h3>
                        <p class="mb-3 text-sm text-gray-600 dark:text-gray-300">{{ __('Inbound email connections allow your application to receive and process incoming emails via IMAP.') }}</p>

                        <div class="space-y-3 text-sm">
                            <div class="flex items-start gap-2">
                                <div class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400">1</span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <strong class="text-gray-700 dark:text-white">{{ __('IMAP Connection:') }}</strong>
                                    {{ __('Connect to any IMAP server (Gmail, Outlook, etc.)') }}
                                </p>
                            </div>

                            <div class="flex items-start gap-2">
                                <div class="w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-medium text-green-600 dark:text-green-400">2</span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <strong class="text-gray-700 dark:text-white">{{ __('Automatic Processing:') }}</strong>
                                    {{ __('Emails are fetched and processed automatically via scheduler or manually.') }}
                                </p>
                            </div>

                            <div class="flex items-start gap-2">
                                <div class="w-5 h-5 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-medium text-purple-600 dark:text-purple-400">3</span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <strong class="text-gray-700 dark:text-white">{{ __('CRM Integration:') }}</strong>
                                    {{ __('Replies are matched to tickets automatically.') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 flex items-start gap-2">
                                <iconify-icon icon="lucide:terminal" class="text-sm mt-0.5 flex-shrink-0"></iconify-icon>
                                <span>{{ __('Tip: Run "php artisan email:process-inbound" or add it to your scheduler for automatic processing.') }}</span>
                            </p>
                        </div>
                    </div>
                </x-popover>
            </x-slot>
        </x-breadcrumbs>
    </x-slot>

    <x-email-tabs.navigation currentTab="inbound" />

    <div class="space-y-6">
        <livewire:datatable.inbound-email-connection-datatable lazy />
    </div>

    @include('backend.pages.inbound-email-connections.partials.connection-form-drawer')
    @include('backend.pages.inbound-email-connections.partials.test-connection-modal')
</x-layouts.backend-layout>
