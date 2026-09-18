<x-layouts.backend-layout :breadcrumbs="$breadcrumbs" :unified-scroll="true">
    <x-email-tabs.navigation currentTab="email-templates" />

    <div class="space-y-6">
        <livewire:datatable.email-template-datatable lazy />
    </div>

    <x-modals.test-email />
    <x-modals.duplicate-email-template />
</x-layouts.backend-layout>
