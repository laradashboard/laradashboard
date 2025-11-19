<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-email-tabs.navigation currentTab="notifications" />

    <div class="space-y-6">
        <livewire:datatable.notification-datatable lazy />
    </div>

    <!-- Test Email Modal Component -->
    <x-modals.test-email />

    @push('scripts')
    <script>
        function openTestEmailModal(id) {
            // Update the modal with the correct send-test URL
            const modal = document.querySelector('[x-data]');
            if (modal) {
                const sendTestUrl = `/admin/settings/notifications/${id}/send-test`;
                window.dispatchEvent(new CustomEvent('open-test-email-modal', {
                    detail: { 
                        id: id,
                        sendTestUrl: sendTestUrl
                    }
                }));
            }
        }
    </script>
    @endpush
</x-layouts.backend-layout>
