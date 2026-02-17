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

<x-card class="mt-6">
    <x-slot name="header">
        {{ __('Login Security') }}
    </x-slot>
    <x-slot name="headerDescription">
        {{ __('Protect your login and admin URLs from unauthorized access.') }}
    </x-slot>

    <div class="space-y-6">
        {{-- Custom Login Route --}}
        <div class="relative">
            <label class="form-label" for="custom_login_route">
                {{ __('Custom Login URL') }}
            </label>
            <div class="flex items-center gap-2">
                <span class="text-gray-500 dark:text-gray-400">{{ url('/') }}/</span>
                <input
                    type="text"
                    name="custom_login_route"
                    id="custom_login_route"
                    placeholder="{{ __('login') }}"
                    @if(config('app.demo_mode', false)) disabled @endif
                    class="form-control"
                    value="{{ config('settings.custom_login_route', '') }}"
                    pattern="^[a-zA-Z0-9\-\_\/]+$"
                    title="{{ __('Only letters, numbers, hyphens, underscores and forward slashes are allowed') }}"
                />
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Leave empty to use the default /login URL. Enter a custom path (e.g., "secure-access" or "my-login") to create an alternative login URL.') }}
            </p>
            @if(config('app.demo_mode', false))
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('This field is disabled in demo mode.') }}
            </p>
            @endif
            @if(config('settings.custom_login_route'))
            <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-md">
                <p class="text-sm text-blue-800 dark:text-blue-300">
                    {{ __('Custom login URL:') }}
                    <a href="{{ url(config('settings.custom_login_route')) }}" target="_blank" class="font-medium underline">
                        {{ url(config('settings.custom_login_route')) }}
                    </a>
                </p>
            </div>
            @endif
        </div>

        {{-- Hide Default Login URL --}}
        <div class="relative">
            <label class="flex items-center gap-3">
                <input type="checkbox"
                    name="hide_default_login_url"
                    value="1"
                    @if(config('settings.hide_default_login_url') == '1') checked @endif
                    @if(config('app.demo_mode', false)) disabled @endif
                    @if(!config('settings.custom_login_route')) disabled @endif
                    class="form-checkbox rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700 disabled:opacity-50">
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Hide Default Login URL') }}
                    </span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('When enabled, the default /login URL will return a 404 error. Only the custom login URL will work.') }}
                    </p>
                </div>
            </label>
            @if(!config('settings.custom_login_route'))
            <p class="mt-1 ml-9 text-xs text-amber-600 dark:text-amber-400">
                {{ __('Set a custom login URL above to enable this option.') }}
            </p>
            @endif
            @if(config('app.demo_mode', false))
            <p class="mt-1 ml-9 text-xs text-gray-500 dark:text-gray-400">
                {{ __('This option is disabled in demo mode.') }}
            </p>
            @endif
        </div>

        {{-- Hide Admin URL --}}
        <div class="relative">
            <label class="flex items-center gap-3">
                <input type="checkbox"
                    name="hide_admin_url"
                    value="1"
                    @if(config('settings.hide_admin_url') == '1') checked @endif
                    @if(config('app.demo_mode', false)) disabled @endif
                    class="form-checkbox rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700">
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Hide Admin URL') }}
                    </span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('When enabled, unauthenticated users accessing /admin will see a 403 error instead of being redirected to the login page.') }}
                    </p>
                </div>
            </label>
            @if(config('app.demo_mode', false))
            <p class="mt-1 ml-9 text-xs text-gray-500 dark:text-gray-400">
                {{ __('This option is disabled in demo mode.') }}
            </p>
            @endif
        </div>

        {{-- Active URLs Summary --}}
        <div class="p-3 rounded-md bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <strong class="text-gray-700 dark:text-gray-300">{{ __('Active Login URLs:') }}</strong>
                    <ul class="mt-1 list-disc list-inside text-gray-600 dark:text-gray-400 ml-2">
                        @if(!config('settings.hide_default_login_url') || !config('settings.custom_login_route'))
                        <li>{{ url('login') }}</li>
                        @endif
                        @if(config('settings.custom_login_route'))
                        <li>{{ url(config('settings.custom_login_route')) }}</li>
                        @endif
                    </ul>
                </div>
                <div>
                    <strong class="text-gray-700 dark:text-gray-300">{{ __('Admin URL Behavior:') }}</strong>
                    <p class="mt-1 {{ config('settings.hide_admin_url') == '1' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                        @if(config('settings.hide_admin_url') == '1')
                            {{ __('/admin → 403 Error (Hidden)') }}
                        @else
                            {{ __('/admin → Redirects to login') }}
                        @endif
                    </p>
                </div>
            </div>
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
                        @if(config('settings.auth_default_user_role', 'Subscriber') === $role->name) selected @endif
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

@inject('socialAuthService', 'App\Services\SocialAuthService')

<x-card class="mt-6">
    <x-slot name="header">
        {{ __('Social Authentication') }}
    </x-slot>
    <x-slot name="headerDescription">
        {{ __('Allow users to sign in using their social media accounts. Configure OAuth credentials from each provider.') }}
    </x-slot>

    <div class="space-y-6">
        {{-- Enable Social Login --}}
        <div class="relative">
            <label class="flex items-center gap-3">
                <input
                    type="checkbox"
                    name="auth_show_social_login"
                    value="1"
                    @if(filter_var(config('settings.auth_show_social_login', '0'), FILTER_VALIDATE_BOOLEAN)) checked @endif
                    class="form-checkbox rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700"
                    x-data
                    @change="$dispatch('social-login-toggled', { enabled: $el.checked })"
                >
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Enable Social Login') }}
                    </span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Allow users to sign in or register using social media accounts.') }}
                    </p>
                </div>
            </label>
        </div>

        {{-- Social Providers Configuration --}}
        <div
            x-data="{ socialLoginEnabled: {{ filter_var(config('settings.auth_show_social_login', '0'), FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false' }} }"
            @social-login-toggled.window="socialLoginEnabled = $event.detail.enabled"
            x-show="socialLoginEnabled"
            x-transition
            class="space-y-6 pt-4 border-t border-gray-200 dark:border-gray-700"
        >
            @foreach($socialAuthService::PROVIDERS as $provider => $config)
                @php
                    $isEnabled = filter_var(config('settings.' . $config['setting_enable'], '0'), FILTER_VALIDATE_BOOLEAN);
                    $hasEnvClientId = !empty(env($config['env_client_id']));
                    $hasEnvClientSecret = !empty(env($config['env_client_secret']));
                    $hasEnvCredentials = $hasEnvClientId && $hasEnvClientSecret;
                @endphp

                <div
                    x-data="{ providerEnabled: {{ $isEnabled ? 'true' : 'false' }}, showCredentials: {{ $isEnabled ? 'true' : 'false' }} }"
                    class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50"
                >
                    {{-- Provider Header --}}
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                name="{{ $config['setting_enable'] }}"
                                value="1"
                                @if($isEnabled) checked @endif
                                class="form-checkbox rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700"
                                @change="providerEnabled = $el.checked; showCredentials = $el.checked"
                            >
                            <div class="flex items-center gap-2">
                                <iconify-icon
                                    icon="{{ $config['icon'] }}"
                                    class="text-xl"
                                    @if($provider === 'github') style="color: currentColor;" @endif
                                ></iconify-icon>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ $config['name'] }}
                                </span>
                            </div>
                        </label>

                        <div class="flex items-center gap-2">
                            @if($hasEnvCredentials)
                                <span class="text-xs px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded">
                                    {{ __('.env configured') }}
                                </span>
                            @endif
                            <a
                                href="{{ $config['console_url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 text-xs px-2 py-1 bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 rounded hover:bg-brand-200 dark:hover:bg-brand-900/50 transition-colors"
                                title="{{ __('Open :provider Developer Console', ['provider' => $config['name']]) }}"
                            >
                                <iconify-icon icon="lucide:external-link" class="text-xs"></iconify-icon>
                                {{ __('Get Credentials') }}
                            </a>
                        </div>
                    </div>

                    {{-- Provider Credentials --}}
                    <div
                        x-show="showCredentials"
                        x-transition
                        class="mt-4 space-y-4"
                    >
                        {{-- Client ID --}}
                        <div>
                            <label class="form-label" for="{{ $config['setting_client_id'] }}">
                                {{ __('Client ID') }}
                            </label>
                            <input
                                type="text"
                                name="{{ $config['setting_client_id'] }}"
                                id="{{ $config['setting_client_id'] }}"
                                class="form-control"
                                value="{{ config('settings.' . $config['setting_client_id'], '') }}"
                                placeholder="{{ $hasEnvClientId ? '********** (' . __('from .env') . ')' : __('Enter :provider Client ID', ['provider' => $config['name']]) }}"
                            >
                            @if($hasEnvClientId)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('Leave empty to use value from .env (:key)', ['key' => $config['env_client_id']]) }}
                                </p>
                            @endif
                        </div>

                        {{-- Client Secret --}}
                        <div>
                            <label class="form-label" for="{{ $config['setting_client_secret'] }}">
                                {{ __('Client Secret') }}
                            </label>
                            <input
                                type="password"
                                name="{{ $config['setting_client_secret'] }}"
                                id="{{ $config['setting_client_secret'] }}"
                                class="form-control"
                                value="{{ config('settings.' . $config['setting_client_secret'], '') }}"
                                placeholder="{{ $hasEnvClientSecret ? '********** (' . __('from .env') . ')' : __('Enter :provider Client Secret', ['provider' => $config['name']]) }}"
                                autocomplete="off"
                            >
                            @if($hasEnvClientSecret)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('Leave empty to use value from .env (:key)', ['key' => $config['env_client_secret']]) }}
                                </p>
                            @endif
                        </div>

                        {{-- Callback URL & Links --}}
                        <div class="p-3 bg-gray-100 dark:bg-gray-700/50 rounded-md space-y-3">
                            <div>
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                    {{ __('Callback URL (add this to :provider)', ['provider' => $config['name']]) }}
                                </p>
                                <div class="flex items-center gap-2">
                                    <code class="flex-1 text-xs text-gray-800 dark:text-gray-200 break-all select-all bg-white dark:bg-gray-800 px-2 py-1 rounded border border-gray-200 dark:border-gray-600">
                                        {{ url('/auth/' . $provider . '/callback') }}
                                    </code>
                                    <button
                                        type="button"
                                        onclick="navigator.clipboard.writeText('{{ url('/auth/' . $provider . '/callback') }}'); this.innerHTML='<iconify-icon icon=\'lucide:check\' class=\'text-green-500\'></iconify-icon>'; setTimeout(() => this.innerHTML='<iconify-icon icon=\'lucide:copy\'></iconify-icon>', 2000)"
                                        class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors"
                                        title="{{ __('Copy to clipboard') }}"
                                    >
                                        <iconify-icon icon="lucide:copy"></iconify-icon>
                                    </button>
                                </div>
                            </div>

                            {{-- Quick Links --}}
                            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Quick Links:') }}</span>
                                <a
                                    href="{{ $config['console_url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 text-xs text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300 hover:underline"
                                >
                                    <iconify-icon icon="lucide:settings"></iconify-icon>
                                    {{ __(':provider Console', ['provider' => $config['name']]) }}
                                </a>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <a
                                    href="{{ $config['docs_url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 text-xs text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300 hover:underline"
                                >
                                    <iconify-icon icon="lucide:book-open"></iconify-icon>
                                    {{ __('Documentation') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Help Text --}}
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-md">
            <div class="flex gap-2">
                <iconify-icon icon="lucide:info" class="text-blue-500 mt-0.5"></iconify-icon>
                <div class="text-sm text-blue-800 dark:text-blue-300">
                    <p class="font-medium mb-1">{{ __('How to configure social login:') }}</p>
                    <ol class="list-decimal list-inside space-y-1 text-xs">
                        <li>{{ __('Create an OAuth application on the provider\'s developer console') }}</li>
                        <li>{{ __('Copy the Client ID and Client Secret from the provider') }}</li>
                        <li>{{ __('Add the Callback URL shown above to your OAuth app settings') }}</li>
                        <li>{{ __('Enter the credentials here or in your .env file') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</x-card>

{!! Hook::applyFilters(AuthFilterHook::SETTINGS_AUTH_TAB_AFTER_SECTION_END, '') !!}
