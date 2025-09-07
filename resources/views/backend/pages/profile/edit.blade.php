@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
    <div class="p-4 mx-auto max-w-7xl md:p-6">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

        {!! Hook::applyFilters(UserFilterHook::PROFILE_AFTER_BREADCRUMBS, '') !!}

        <div class="space-y-6">
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
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
                </div>
            </div>
        </div>

        {!! Hook::applyFilters(UserFilterHook::PROFILE_AFTER_FORM, '') !!}
    </div>
@endsection
