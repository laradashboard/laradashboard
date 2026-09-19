<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(NotificationFilterHook::NOTIFICATIONS_AFTER_BREADCRUMBS, '') !!}

    <x-email-tabs.navigation currentTab="notifications" />

    <div class="space-y-6">
        <livewire:datatable.notification-datatable lazy />
    </div>

    {!! Hook::applyFilters(NotificationFilterHook::NOTIFICATIONS_AFTER_TABLE, '') !!}

    <x-modals.test-email />
</x-layouts.backend-layout>
