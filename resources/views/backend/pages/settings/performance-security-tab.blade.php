{!! Hook::applyFilters(SettingFilterHook::SETTINGS_PERFORMANCE_SECURITY_TAB_BEFORE_SECTION_START, '') !!}

<x-card>
    <x-slot name="header">
        {{ __('Admin Login Route') }}
    </x-slot>
    <x-slot name="headerDescription">
        {{ __('Changing the admin login route can help protect your admin panel from unauthorized access attempts. Make sure to remember the new URL!') }}
    </x-slot>

    <div class="space-y-6">
        <div class="relative">
            <label class="form-label" for="admin_login_route">
                {{ __('Custom Admin Login Route') }}
            </label>
            <div class="flex items-center gap-2">
                <span class="text-gray-500 dark:text-gray-400">{{ url('/') }}/</span>
                <input
                    type="text"
                    name="admin_login_route"
                    id="admin_login_route"
                    placeholder="{{ __('admin/login') }}"
                    @if(config('app.demo_mode', false)) disabled @endif
                    class="form-control"
                    value="{{ config('settings.admin_login_route', 'admin/login') }}"
                    pattern="^[a-zA-Z0-9\-\_\/]+$"
                    title="{{ __('Only letters, numbers, hyphens, underscores and forward slashes are allowed') }}"
                />
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Note: Changing this will immediately update your login URL. Make sure to bookmark the new URL.') }}
            </p>
            @if(config('app.demo_mode', false))
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Editing this field is disabled in demo mode.') }}
            </p>
            @endif
            @if(config('settings.admin_login_route') && config('settings.admin_login_route') !== 'admin/login')
            <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-md">
                <p class="text-sm text-blue-800 dark:text-blue-300">
                    {{ __('Current admin login URL:') }} 
                    <a href="{{ url(config('settings.admin_login_route')) }}" target="_blank" class="font-medium underline">
                        {{ url(config('settings.admin_login_route')) }}
                    </a>
                </p>
            </div>
            @endif
        </div>

        <div class="relative">
            <label class="flex items-center">
                <input type="checkbox" 
                    name="disable_default_admin_redirect" 
                    value="1"
                    @if(config('settings.disable_default_admin_redirect') == '1') checked @endif
                    @if(config('app.demo_mode', false)) disabled @endif
                    class="form-checkbox rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ __('Hide /admin URL (Show 403 instead of redirecting)') }}
                </span>
            </label>
            <p class="mt-1 ml-6 text-xs text-gray-500 dark:text-gray-400">
                {{ __('When checked, non-authenticated users accessing /admin will see a 403 error. When unchecked, they will be redirected to your login page.') }}
            </p>
            @if(config('app.demo_mode', false))
            <p class="mt-1 ml-6 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Editing this option is disabled in demo mode.') }}
            </p>
            @endif
            
            {{-- Current behavior indicator --}}
            <div class="mt-3 ml-6 p-2 rounded-md text-xs {{ config('settings.disable_default_admin_redirect') == '1' ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' : 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300' }}">
                <strong>{{ __('Current behavior:') }}</strong>
                @if(config('settings.disable_default_admin_redirect') == '1')
                    {{ __('/admin → 403 Error (Hidden)') }}
                @else
                    {{ __('/admin → Redirects to login page') }}
                @endif
            </div>
        </div>
    </div>
</x-card>

<div class="mt-6">
    @include('backend.pages.settings.recaptcha-settings')
</div>

{!! Hook::applyFilters(SettingFilterHook::SETTINGS_PERFORMANCE_SECURITY_TAB_AFTER_SECTION_END, '') !!}