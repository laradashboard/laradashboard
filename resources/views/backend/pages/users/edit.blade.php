<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
            <form
                action="{{ route('admin.users.update', $user->id) }}"
                method="POST"
                class="space-y-6"
                enctype="multipart/form-data"
                data-prevent-unsaved-changes
            >
                @csrf
                @method('PUT')

                @php
                    // Load user metadata for additional information
                    $userMeta = $user->userMeta()->pluck('meta_value', 'meta_key')->toArray();

                    // Load localization data
                    $locales = app(\App\Services\LanguageService::class)->getLanguages();
                    $timezones = app(\App\Services\TimezoneService::class)->getTimezones();
                @endphp

                @include('backend.pages.users.partials.form', [
                    'user' => $user,
                    'roles' => $roles,
                    'timezones' => $timezones,
                    'locales' => $locales,
                    'userMeta' => $userMeta,
                    'mode' => 'edit',
                    'showUsername' => true,
                    'showRoles' => true,
                    'showAdditional' => true
                ])
            </form>
        </div>
    </div>
</x-layouts.backend-layout>
