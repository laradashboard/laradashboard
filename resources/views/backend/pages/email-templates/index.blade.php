<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-email-tabs.navigation :currentTab="$currentTab" />

    <div class="space-y-6">
        <livewire:datatable.email-template-datatable lazy />
    </div>

    <!-- Email Settings Drawer -->
    @include('backend.pages.email-templates.partials.settings-drawer')

    <!-- Test Email Modal Component -->
    <x-modals.test-email />

    @push('scripts')
    <script>
        function openTestEmailModal(id) {
            window.dispatchEvent(new CustomEvent('open-test-email-modal', {
                detail: { id: id }
            }));
        }
    </script>
    @endpush
</x-layouts.backend-layout>
