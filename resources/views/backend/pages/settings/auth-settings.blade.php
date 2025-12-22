{!! Hook::applyFilters(AuthFilterHook::SETTINGS_AUTH_TAB_BEFORE_SECTION_START, '') !!}

<x-card>
    <x-slot name="header">
        {{ __('Public Authentication') }}
    </x-slot>
    <x-slot name="headerDescription">
        {{ __('Control which authentication features are available to public users. These settings affect the frontend login and registration pages.') }}
    </x-slot>

    <div class="space-y-6">
        {{-- Enable Public Login --}}
        <div class="relative">
            <label class="flex items-center gap-3">
                <input
                    type="checkbox"
                    name="auth_enable_public_login"
                    value="1"
                    @if(filter_var(config('settings.auth_enable_public_login', '1'), FILTER_VALIDATE_BOOLEAN)) checked @endif
                    class="form-checkbox rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700"
                >
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Enable Public Login') }}
                    </span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Allow users to login from the public frontend login page.') }}
                    </p>
                </div>
            </label>
        </div>

        {{-- Enable Public Registration --}}
        <div class="relative">
            <label class="flex items-center gap-3">
                <input
                    type="checkbox"
                    name="auth_enable_public_registration"
                    value="1"
                    @if(filter_var(config('settings.auth_enable_public_registration', '0'), FILTER_VALIDATE_BOOLEAN)) checked @endif
                    class="form-checkbox rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700"
                >
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Enable Public Registration') }}
                    </span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Allow new users to register from the public frontend registration page.') }}
                    </p>
                </div>
            </label>
        </div>

        {{-- Enable Password Reset --}}
        <div class="relative">
            <label class="flex items-center gap-3">
                <input
                    type="checkbox"
                    name="auth_enable_password_reset"
                    value="1"
                    @if(filter_var(config('settings.auth_enable_password_reset', '1'), FILTER_VALIDATE_BOOLEAN)) checked @endif
                    class="form-checkbox rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700"
                >
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Enable Password Reset') }}
                    </span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Allow users to reset their password via email.') }}
                    </p>
                </div>
            </label>
        </div>

        {{-- Enable Email Verification --}}
        <div class="relative">
            <label class="flex items-center gap-3">
                <input
                    type="checkbox"
                    name="auth_enable_email_verification"
                    value="1"
                    @if(filter_var(config('settings.auth_enable_email_verification', '0'), FILTER_VALIDATE_BOOLEAN)) checked @endif
                    class="form-checkbox rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700"
                >
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Require Email Verification') }}
                    </span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Require users to verify their email address after registration.') }}
                    </p>
                </div>
            </label>
        </div>
    </div>
</x-card>

{!! Hook::applyFilters(AuthFilterHook::SETTINGS_AUTH_TAB_BEFORE_SECTION_END, '') !!}

<x-card class="mt-6">
    <x-slot name="header">
        {{ __('Registration Settings') }}
    </x-slot>
    <x-slot name="headerDescription">
        {{ __('Configure default settings for new user registrations.') }}
    </x-slot>

    <div class="space-y-6">
        {{-- Default User Role --}}
        <div class="relative">
            <label class="form-label" for="auth_default_user_role">
                {{ __('Default User Role') }}
            </label>
            <select
                name="auth_default_user_role"
                id="auth_default_user_role"
                class="form-control"
            >
                @foreach(\Spatie\Permission\Models\Role::all() as $role)
                    <option
                        value="{{ $role->name }}"
                        @if(config('settings.auth_default_user_role', 'user') === $role->name) selected @endif
                    >
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('The role that will be assigned to new users upon registration.') }}
            </p>
        </div>

        {{-- Redirect After Login --}}
        <div class="relative">
            <label class="form-label" for="auth_redirect_after_login">
                {{ __('Redirect After Login') }}
            </label>
            <input
                type="text"
                name="auth_redirect_after_login"
                id="auth_redirect_after_login"
                class="form-control"
                placeholder="{{ __('/dashboard') }}"
                value="{{ config('settings.auth_redirect_after_login', '/') }}"
            >
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('The URL path where users will be redirected after successful login.') }}
            </p>
        </div>

        {{-- Redirect After Register --}}
        <div class="relative">
            <label class="form-label" for="auth_redirect_after_register">
                {{ __('Redirect After Registration') }}
            </label>
            <input
                type="text"
                name="auth_redirect_after_register"
                id="auth_redirect_after_register"
                class="form-control"
                placeholder="{{ __('/') }}"
                value="{{ config('settings.auth_redirect_after_register', '/') }}"
            >
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('The URL path where users will be redirected after successful registration.') }}
            </p>
        </div>
    </div>
</x-card>

<x-card class="mt-6">
    <x-slot name="header">
        {{ __('Page Customization') }}
    </x-slot>
    <x-slot name="headerDescription">
        {{ __('Customize the appearance and content of the authentication pages.') }}
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Login Page Title --}}
        <div class="relative">
            <label class="form-label" for="auth_login_page_title">
                {{ __('Login Page Title') }}
            </label>
            <input
                type="text"
                name="auth_login_page_title"
                id="auth_login_page_title"
                class="form-control"
                placeholder="{{ __('Sign In') }}"
                value="{{ config('settings.auth_login_page_title', '') }}"
            >
        </div>

        {{-- Login Page Description --}}
        <div class="relative">
            <label class="form-label" for="auth_login_page_description">
                {{ __('Login Page Description') }}
            </label>
            <input
                type="text"
                name="auth_login_page_description"
                id="auth_login_page_description"
                class="form-control"
                placeholder="{{ __('Enter your credentials to sign in') }}"
                value="{{ config('settings.auth_login_page_description', '') }}"
            >
        </div>

        {{-- Register Page Title --}}
        <div class="relative">
            <label class="form-label" for="auth_register_page_title">
                {{ __('Registration Page Title') }}
            </label>
            <input
                type="text"
                name="auth_register_page_title"
                id="auth_register_page_title"
                class="form-control"
                placeholder="{{ __('Create Account') }}"
                value="{{ config('settings.auth_register_page_title', '') }}"
            >
        </div>

        {{-- Register Page Description --}}
        <div class="relative">
            <label class="form-label" for="auth_register_page_description">
                {{ __('Registration Page Description') }}
            </label>
            <input
                type="text"
                name="auth_register_page_description"
                id="auth_register_page_description"
                class="form-control"
                placeholder="{{ __('Fill in the form to create your account') }}"
                value="{{ config('settings.auth_register_page_description', '') }}"
            >
        </div>
    </div>
</x-card>

{!! Hook::applyFilters(AuthFilterHook::SETTINGS_AUTH_TAB_AFTER_SECTION_END, '') !!}
