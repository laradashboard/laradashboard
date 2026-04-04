<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-card>
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
    </x-card>

    {{-- Send Login Link --}}
    @if($user->id !== auth()->id())
        <x-card class="mt-4">
            <div class="flex items-center justify-between gap-4" x-data="{ sending: false }">
                <div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Send Login Link') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Send an email to :email with their username and a link to set their password.', ['email' => $user->email]) }}
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.users.send-login-link', $user->id) }}" @submit="sending = true">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-md bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors whitespace-nowrap disabled:opacity-50"
                        :disabled="sending"
                    >
                        <template x-if="!sending">
                            <span class="flex items-center gap-2">
                                <iconify-icon icon="lucide:send" width="16" height="16" aria-hidden="true"></iconify-icon>
                                {{ __('Send Login Link') }}
                            </span>
                        </template>
                        <template x-if="sending">
                            <span class="flex items-center gap-2">
                                <iconify-icon icon="lucide:loader-2" width="16" height="16" class="animate-spin" aria-hidden="true"></iconify-icon>
                                {{ __('Sending...') }}
                            </span>
                        </template>
                    </button>
                </form>
            </div>
        </x-card>
    @endif
</x-layouts.backend-layout>
