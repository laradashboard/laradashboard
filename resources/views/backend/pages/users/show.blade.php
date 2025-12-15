<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(UserFilterHook::USER_SHOW_AFTER_BREADCRUMBS, '', $user) !!}

    <div class="space-y-6">
        {{-- Header Card with Actions --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-4">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700">
                    <div>
                        <h3 class="text-base font-medium text-gray-700 dark:text-white/90">
                            {{ $user->full_name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    </div>
                </div>
            </x-slot>

            <x-slot name="headerRight">
                <div class="flex gap-2">
                    @if (auth()->user()->can('user.login_as') && $user->id !== auth()->id())
                        <a href="{{ route('admin.users.login-as', $user->id) }}" class="btn-default">
                            <iconify-icon icon="lucide:log-in" class="mr-2"></iconify-icon>
                            {{ __('Login as') }}
                        </a>
                    @endif
                </div>
            </x-slot>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content (Left - 2 columns) --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Personal Information --}}
                    <x-card.card bodyClass="!p-5 !space-y-4">
                        <x-slot:header>{{ __('Personal Information') }}</x-slot:header>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('First Name') }}</label>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $user->first_name }}</p>
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Last Name') }}</label>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $user->last_name ?: '-' }}</p>
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Email') }}</label>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $user->email }}</p>
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Username') }}</label>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $user->username ?: '-' }}</p>
                            </div>
                        </div>
                    </x-card.card>

                    {{-- Roles --}}
                    <x-card.card bodyClass="!p-5">
                        <x-slot:header>{{ __('Roles & Permissions') }}</x-slot:header>

                        @if($user->roles->count() > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-light">
                                        <iconify-icon icon="lucide:shield" class="mr-1.5 text-sm"></iconify-icon>
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-400 dark:text-gray-500 italic">{{ __('No roles assigned.') }}</p>
                        @endif
                    </x-card.card>

                    {{-- Additional Information --}}
                    @php
                        $bio = $user->userMeta->where('meta_key', 'bio')->first()?->meta_value;
                        $displayName = $user->userMeta->where('meta_key', 'display_name')->first()?->meta_value;
                    @endphp

                    @if($bio || $displayName)
                        <x-card.card bodyClass="!p-5 !space-y-4">
                            <x-slot:header>{{ __('Additional Information') }}</x-slot:header>

                            @if($displayName)
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Display Name') }}</label>
                                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $displayName }}</p>
                                </div>
                            @endif

                            @if($bio)
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Bio') }}</label>
                                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $bio }}</p>
                                </div>
                            @endif
                        </x-card.card>
                    @endif

                    {!! Hook::applyFilters(UserFilterHook::USER_SHOW_AFTER_MAIN_CONTENT, '', $user) !!}
                </div>

                {{-- Sidebar (Right - 1 column) --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Account Status --}}
                    <x-card.card bodyClass="!p-4 !space-y-4">
                        <x-slot:header>{{ __('Account Status') }}</x-slot:header>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Email Verified') }}</label>
                            <div class="mt-1">
                                @if($user->email_verified_at)
                                    <span class="badge badge-success">
                                        <iconify-icon icon="lucide:check-circle" class="mr-1"></iconify-icon>
                                        {{ __('Verified') }}
                                    </span>
                                @else
                                    <span class="badge badge-warning">
                                        <iconify-icon icon="lucide:alert-circle" class="mr-1"></iconify-icon>
                                        {{ __('Not Verified') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Email Subscribed') }}</label>
                            <div class="mt-1">
                                @if($user->email_subscribed)
                                    <span class="badge badge-success">{{ __('Yes') }}</span>
                                @else
                                    <span class="badge badge-default">{{ __('No') }}</span>
                                @endif
                            </div>
                        </div>
                    </x-card.card>

                    {{-- Preferences --}}
                    @php
                        $timezone = $user->userMeta->where('meta_key', 'timezone')->first()?->meta_value;
                        $locale = $user->userMeta->where('meta_key', 'locale')->first()?->meta_value;
                    @endphp

                    @if($timezone || $locale)
                        <x-card.card bodyClass="!p-4 !space-y-4">
                            <x-slot:header>{{ __('Preferences') }}</x-slot:header>

                            @if($timezone)
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Timezone') }}</label>
                                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $timezone }}</p>
                                </div>
                            @endif

                            @if($locale)
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Locale') }}</label>
                                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $locale }}</p>
                                </div>
                            @endif
                        </x-card.card>
                    @endif

                    {{-- Metadata --}}
                    <x-card.card bodyClass="!p-4 !space-y-4">
                        <x-slot:header>{{ __('Metadata') }}</x-slot:header>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('User ID') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">#{{ $user->id }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                {{ $user->created_at->format('M d, Y h:i A') }}
                            </p>
                        </div>

                        @if($user->created_at->ne($user->updated_at))
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Last Updated') }}</label>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $user->updated_at->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        @endif

                        @if($user->email_verified_at)
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Email Verified At') }}</label>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $user->email_verified_at->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        @endif
                    </x-card.card>

                    {!! Hook::applyFilters(UserFilterHook::USER_SHOW_AFTER_SIDEBAR, '', $user) !!}
                </div>
            </div>
        </x-card>

        {{-- Danger Zone --}}
        @if (auth()->user()->canBeModified($user, 'user.delete') && $user->id !== auth()->id())
            <x-card.card class="border-red-200 dark:border-red-900/50">
                <x-slot:header>
                    <span class="text-red-600 dark:text-red-400">{{ __('Danger Zone') }}</span>
                </x-slot:header>

                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Delete this user') }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Once you delete a user, there is no going back. Please be certain.') }}</p>
                    </div>

                    <div x-data="{ deleteModalOpen: false }">
                        <button
                            @click="deleteModalOpen = true"
                            type="button"
                            class="btn-danger"
                        >
                            <iconify-icon icon="lucide:trash-2" class="mr-2"></iconify-icon>
                            {{ __('Delete User') }}
                        </button>

                        <x-modals.confirm-delete
                            id="delete-modal-{{ $user->id }}"
                            title="{{ __('Delete User') }}"
                            content="{{ __('Are you sure you want to delete this user? This action cannot be undone.') }}"
                            formId="delete-form-{{ $user->id }}"
                            formAction="{{ route('admin.users.destroy', $user->id) }}"
                            modalTrigger="deleteModalOpen"
                            cancelButtonText="{{ __('No, cancel') }}"
                            confirmButtonText="{{ __('Yes, Delete') }}"
                        />
                    </div>
                </div>
            </x-card.card>
        @endif
    </div>

    {!! Hook::applyFilters(UserFilterHook::USER_SHOW_AFTER_CONTENT, '', $user) !!}
</x-layouts.backend-layout>
