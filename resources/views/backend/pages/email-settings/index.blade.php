<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-email-tabs.navigation currentTab="email-settings" />

    <div class="space-y-6">
        <form method="POST" action="{{ route('admin.email-settings.update') }}" data-prevent-unsaved-changes>
            @csrf
            @include('backend.pages.email-settings.partials.email-settings-form')

            <div class="mt-6">
                <x-buttons.submit-buttons :submit-label="__('Save Changes')" />
            </div>
        </form>
    </div>
</x-layouts.backend-layout>
