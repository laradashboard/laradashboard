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
            {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_AVATAR, '') !!}
            @if($user)
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                    {{ __('Account created on') }} {{ $user->created_at->format('M d, Y') }}
                </p>
            @endif
        </div>

        @if(($isEdit || $isProfile) && $user)
            <x-users.social-links 
                :user="$user" 
                :userMeta="$userMeta"
                :showEdit="true"
            />
            {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_SOCIAL_LINKS, '') !!}
        @endif
    </div>
    @endif

    {{-- Form Fields Section --}}
    <div class="w-full {{ $showImage ? 'md:w-4/5' : 'md:w-full' }}">
        <div class="grid grid-cols-1 md:grid-cols-2 flex-wrap gap-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white pb-1 border-b border-gray-200 dark:border-gray-700 col-span-2">
                {{ __('Personal Information') }}
            </h3>

            <div class="col-span-2 md:col-span-1">
                <x-inputs.input
                    name="first_name"
                    id="first_name"
                    :label="$firstNameLabel"
                    :value="old('first_name', $user?->first_name)"
                    :placeholder="__('Enter First Name')"
                    required
                />
                {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_FIRST_NAME, '', $user) !!}
            </div>

            <div class="col-span-2 md:col-span-1">
                <x-inputs.input
                    name="last_name"
                    id="last_name"
                    :label="$lastNameLabel"
                    :value="old('last_name', $user?->last_name)"
                    :placeholder="__('Enter Last Name')"
                    required
                />
                {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_LAST_NAME, '', $user) !!}
            </div>

            <div class="col-span-2 md:col-span-1">
                @if($showUsername)
                    <x-inputs.input
                        name="username"
                        id="username"
                        :label="$usernameLabel"
                        :value="old('username', $user?->username)"
                        :placeholder="__('Enter Username')"
                        required
                    />
                    {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_USERNAME, '', $user) !!}
                @endif
            </div>

            <div class="col-span-2 md:col-span-1">
                <x-inputs.input
                    type="email"
                    name="email"
                    id="email"
                    :label="$emailLabel"
                    :value="old('email', $user?->email)"
                    :placeholder="__('Enter Email')"
                    required
                />
                {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_EMAIL, '', $user) !!}
            </div>

            <div class="col-span-2 md:col-span-1">
                <x-inputs.password
                    name="password"
                    label="{{ $passwordLabel }}"
                    placeholder="{{ __('Enter Password') }}"
                    :required="$isCreate"
                    :autogenerate="true"
                />
                {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_PASSWORD, '', $user) !!}
            </div>

            <div class="col-span-2 md:col-span-1">
                <x-inputs.password
                    name="password_confirmation"
                    label="{{ $confirmPasswordLabel }}"
                    placeholder="{{ __('Confirm Password') }}"
                    :required="$isCreate"
                />
                {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_CONFIRM_PASSWORD, '', $user) !!}
            </div>

            @if($showRoles || $showDisplayName)
                <h3 class="text-lg font-medium text-gray-900 dark:text-white pb-1 border-b border-gray-200 dark:border-gray-700 col-span-2">
                    {{ __('Permissions & Display') }}
                </h3>

                @if($showRoles)
                <div class="col-span-2 md:col-span-1">
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
                        :searchable="true"
                        required
                    />
                {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_ROLES, '', $user) !!}
                </div>
                @endif

                @if($showDisplayName)
                <div class="col-span-2 md:col-span-1">
                    <x-inputs.input
                        name="display_name"
                        id="display_name"
                        :label="__('Display Name')"
                        :value="old('display_name', $userMeta['display_name'] ?? '')"
                        :placeholder="__('Enter Display Name')"
                    />
                    {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_DISPLAY_NAME, '', $user) !!}
                </div>
                @endif
            @endif

            @if($showAdditional)
                <h3 class="text-lg font-medium text-gray-900 dark:text-white pb-1 border-b border-gray-200 dark:border-gray-700 col-span-2">
                    {{ __('Additional Information') }}
                </h3>
                <div class="col-span-2">
                    <x-inputs.textarea
                        name="bio"
                        id="bio"
                        :label="__('Bio')"
                        :value="old('bio', $userMeta['bio'] ?? '')"
                        :placeholder="__('Tell us about yourself...')"
                        :rows="3"
                    />
                    {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_BIO, '', $user) !!}
                </div>

                @if(!empty($timezones))
                    <div class="col-span-2 md:col-span-1">
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
                    {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_TIMEZONE, '', $user) !!}
                @endif

                @if(!empty($locales))
                    <div class="col-span-2 md:col-span-1">
                        <x-searchable-select
                            name="locale"
                            label="{{ __('Locale') }}"
                            placeholder="{{ __('Select Locale') }}"
                            searchPlaceholder="{{ __('Search locales...') }}"
                            :options="$locales"
                            :selected="old('locale', $userMeta['locale'] ?? '')"
                            position="top"
                        />
                        {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_LOCALE, '', $user) !!}
                    </div>
                @endif
            @endif

            <div class="col-span-2 flex mt-4">
                <x-buttons.submit-buttons cancelUrl="{{ $cancelUrl }}" />
            </div>
        </div>
    </div>
</div>