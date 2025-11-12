<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <!-- Email Templates Table -->
        <div class="space-y-6">
            <livewire:datatable.email-template-datatable lazy />
        </div>
    </div>

    <!-- Test Email Modal Component -->
    <x-modals.test-email />

    <script>
        function openTestEmailModal(id) {
            window.dispatchEvent(new CustomEvent('open-test-email-modal', {
                detail: { id: id }
            }));
        }
    </script>
</x-layouts.backend-layout>
