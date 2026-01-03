<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(UserFilterHook::PROFILE_AFTER_BREADCRUMBS, '') !!}

    {{-- Email Verification Section --}}
    @if(filter_var(config('settings.auth_enable_email_verification', '0'), FILTER_VALIDATE_BOOLEAN))
        @if(!auth()->user()->hasVerifiedEmail())
            <livewire:components.profile-email-verification />
        @endif
    @endif

    <x-card>
        <form
            action="{{ route('profile.update') }}"
            method="POST"
            class="space-y-6"
            enctype="multipart/form-data"
            data-prevent-unsaved-changes
        >
            @csrf
            @method('PUT')

            @include('backend.pages.users.partials.form', [
                'user' => $user,
                'roles' => [],
                'timezones' => $timezones ?? [],
                'locales' => $locales ?? [],
                'userMeta' => $userMeta ?? [],
                'mode' => 'profile',
                'showUsername' => true,
                'showRoles' => false,
                'showAdditional' => true,
                'cancelUrl' => route('admin.dashboard')
            ])
        </form>
    </x-card>

    {!! Hook::applyFilters(UserFilterHook::PROFILE_AFTER_FORM, '') !!}
</x-layouts.backend-layout>
