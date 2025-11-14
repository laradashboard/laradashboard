<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        @include('backend.pages.email-templates.partials.settings-view')

        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                {{ __('Email Templates') }}
            </h3>
            <livewire:datatable.email-template-datatable lazy />
        </div>
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
