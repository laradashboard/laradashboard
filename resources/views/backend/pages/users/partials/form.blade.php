@props([
    'user' => null,
    'roles' => [],
    'timezones' => [],
    'locales' => [],
    'userMeta' => [],
    'mode' => 'create', // 'create', 'edit', 'profile'
    'showUsername' => true,
    'showRoles' => true,
    'showDisplayName' => true,
    'showAdditional' => false,
    'firstNameLabel' => null,
    'lastNameLabel' => null,
    'emailLabel' => null,
    'usernameLabel' => null,
    'passwordLabel' => null,
    'confirmPasswordLabel' => null,
    'rolesLabel' => null,
    'avatarLabel' => null,
    'cancelUrl' => route('admin.users.index'),
    'showImage' => true
])

@php
    $isCreate = $mode === 'create';
    $isEdit = $mode === 'edit';
    $isProfile = $mode === 'profile';

    // Default labels
    $firstNameLabel = $firstNameLabel ?? __('First Name');
    $lastNameLabel = $lastNameLabel ?? __('Last Name');
    $emailLabel = $emailLabel ?? ($isProfile ? __('Email') : __('User Email'));
    $usernameLabel = $usernameLabel ?? __('Username');
    $passwordLabel = $passwordLabel ?? ($isCreate ? __('Password') : __('Password (Optional)'));
    $confirmPasswordLabel = $confirmPasswordLabel ?? ($isCreate ? __('Confirm Password') : __('Confirm Password (Optional)'));
    $rolesLabel = $rolesLabel ?? __('Assign Roles');
    $avatarLabel = $avatarLabel ?? __('Avatar');

    // Get avatar URL for display.
    $avatarUrl = null;
    if ($user?->avatar_id && $user->avatar) {
        $avatarUrl = $user->avatar_url ?? $user->avatar->getUrl();
    }

    $emptyText = isset($user) && $user?->full_name
        ? strtoupper(mb_substr($user->full_name, 0, 2))
        : __('No Profile Selected');
@endphp

<div class="flex flex-col md:flex-row gap-8 md:gap-12 items-start" x-data="{ avatarSelected: false }" @avatar-selected.window="avatarSelected = $event.detail">
    {{-- Profile Picture Section --}}
    @if($showImage)
    <div class="w-full md:w-1/5 flex-shrink-0 flex flex-col items-center gap-4">
        <div class="w-full flex flex-col items-center">
            <div class="mt-2 w-full">
                <x-media-selector
                    name="avatar_id"
                    label=""
                    :multiple="false"
                    allowedTypes="images"
                    :existingMedia="$user?->avatar_id ? [['id' => $user->avatar_id, 'url' => $avatarUrl, 'name' => $user->avatar->name]] : []"
                    :required="false"
                    height="150px"
                    class="[&_.media-selector-button]:w-full [&_.media-selector-button]:justify-center w-full"
                    buttonText="{{ __('Change Photo') }}"
                    :showClearButton="false"
                    :showNoSelection="true"
                    :showPreviewCircular="true"
                    emptyText="{{ $emptyText }}"
                />
            </div>
            @if($user)
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                    {{ __('Account created on') }} {{ $user->created_at->format('M d, Y') }}
                </p>
            @endif
        </div>
    </div>
    @endif

    {{-- Form Fields Section --}}
    <div class="w-full {{ $showImage ? 'md:w-4/5' : 'md:w-full' }}">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Personal Information --}}
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white pb-1 border-b border-gray-200 dark:border-gray-700">
                    {{ __('Personal Information') }}
                </h3>
            </div>
            <div>
                <label for="first_name" class="form-label">{{ $firstNameLabel }}</label>
                <input type="text" name="first_name" id="first_name" required
                    value="{{ old('first_name', $user?->first_name) }}"
                    placeholder="{{ __('Enter First Name') }}"
                    class="form-control">
            </div>
            <div>
                <label for="last_name" class="form-label">{{ $lastNameLabel }}</label>
                <input type="text" name="last_name" id="last_name" required
                    value="{{ old('last_name', $user?->last_name) }}"
                    placeholder="{{ __('Enter Last Name') }}"
                    class="form-control">
            </div>
            @if($showUsername)
                <div>
                    <label for="username" class="form-label">{{ $usernameLabel }}</label>
                    <input type="text" name="username" id="username" required
                        value="{{ old('username', $user?->username) }}"
                        placeholder="{{ __('Enter Username') }}"
                        class="form-control">
                </div>
            @endif
            <div>
                <label for="email" class="form-label">{{ $emailLabel }}</label>
                <input type="email" name="email" id="email" required
                    value="{{ old('email', $user?->email) }}"
                    placeholder="{{ __('Enter Email') }}"
                    class="form-control">
            </div>

            <div>
                <x-inputs.password
                    name="password"
                    label="{{ $passwordLabel }}"
                    placeholder="{{ __('Enter Password') }}"
                    :required="$isCreate" />
            </div>
            <div>
                <x-inputs.password
                    name="password_confirmation"
                    label="{{ $confirmPasswordLabel }}"
                    placeholder="{{ __('Confirm Password') }}"
                    :required="$isCreate" />
            </div>

            @if($showRoles || $showDisplayName)
                <div class="col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white pb-1 border-b border-gray-200 dark:border-gray-700">
                        {{ __('Permissions & Display') }}
                    </h3>
                </div>
                @if($showRoles)
                <div>
                    <x-inputs.combobox
                        name="roles[]"
                        label="{{ $rolesLabel }}"
                        placeholder="{{ __('Select Roles') }}"
                        :options="collect($roles)
                            ->map(fn($name, $id) => ['value' => $name, 'label' => ucfirst($name)])
                            ->values()
                            ->toArray()"
                        :selected="old('roles', $user?->roles?->pluck('name')->toArray() ?? [])"
                        :multiple="true"
                        :searchable="false" />
                </div>
                @endif

                @if($showDisplayName)
                <div>
                    <label for="display_name" class="form-label">{{ __('Display Name') }}</label>
                    <input type="text" name="display_name" id="display_name"
                        value="{{ old('display_name', $userMeta['display_name'] ?? '') }}"
                        placeholder="{{ __('Enter Display Name') }}"
                        class="form-control">
                </div>
                @endif
            @endif

            {!! ld_apply_filters('after_username_field', '', $user) !!}

            @if($isProfile)
                {!! ld_apply_filters('profile_edit_fields', '', $user) !!}
            @endif

            @if($showAdditional)
                <div class="col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white pb-1 border-b border-gray-200 dark:border-gray-700">
                        {{ __('Additional Information') }}
                    </h3>
                </div>
                <div class="col-span-2">
                    <label for="bio" class="form-label">{{ __('Bio') }}</label>
                    <textarea name="bio" id="bio" rows="3"
                        placeholder="{{ __('Tell us about yourself...') }}"
                        class="form-control h-16">{{ old('bio', $userMeta['bio'] ?? '') }}</textarea>
                </div>
                @if(!empty($timezones))
                    <div>
                        <x-searchable-select
                            name="timezone"
                            label="{{ __('Timezone') }}"
                            placeholder="{{ __('Select Timezone') }}"
                            searchPlaceholder="{{ __('Search timezones...') }}"
                            :options="$timezones"
                            :selected="old('timezone', $userMeta['timezone'] ?? '')"
                            position="top"
                        />
                    </div>
                @endif
                @if(!empty($locales))
                    <div>
                        <x-searchable-select
                            name="locale"
                            label="{{ __('Locale') }}"
                            placeholder="{{ __('Select Locale') }}"
                            searchPlaceholder="{{ __('Search locales...') }}"
                            :options="$locales"
                            :selected="old('locale', $userMeta['locale'] ?? '')"
                            position="top"
                        />
                    </div>
                @endif
            @endif

            @if($isProfile)
                <div class="col-span-2">
                    {!! ld_apply_filters('profile_edit_after_fields', '', $user) !!}
                </div>
            @endif

            <div class="col-span-2 flex mt-4">
                <x-buttons.submit-buttons cancelUrl="{{ $cancelUrl }}" />
            </div>
        </div>
    </div>
</div>