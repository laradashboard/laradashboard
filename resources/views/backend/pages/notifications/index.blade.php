<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-email-tabs.navigation currentTab="notifications" />

    <div class="space-y-6">
        <livewire:datatable.notification-datatable lazy />
    </div>

    <x-modals.test-email />
</x-layouts.backend-layout>
