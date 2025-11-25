<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-email-tabs.navigation currentTab="email-templates" />

    <div class="space-y-6">
        <livewire:datatable.email-template-datatable lazy />
    </div>

    <x-modals.test-email />
    <x-modals.duplicate-email-template />

    @push('scripts')
    <script>
        function openDuplicateEmailTemplateModal(id, url) {
            window.dispatchEvent(new CustomEvent('open-duplicate-email-template-modal', {
                detail: { id, url }
            }));
        }
    </script>
    @endpush
</x-layouts.backend-layout>
