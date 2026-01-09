{!! Hook::applyFilters(AdminFilterHook::HEADER_RIGHT_MENU_BEFORE, '') !!}

<div class="flex items-center gap-1">
    <div class="hidden md:block">
        @include('backend.layouts.partials.demo-mode-notice')
    </div>

    {{-- Core Upgrade Notification --}}
    <livewire:components.core-upgrade-notification />

    {!! Hook::applyFilters(AdminFilterHook::HEADER_BEFORE_LOCALE_SWITCHER, '') !!}
    <x-tooltip title="{{ __('Change locale') }}" position="bottom">
        @include('backend.layouts.partials.locale-switcher')
    </x-tooltip>
    {!! Hook::applyFilters(AdminFilterHook::HEADER_AFTER_LOCALE_SWITCHER, '') !!}

    {!! Hook::applyFilters(AdminFilterHook::DARK_MODE_TOGGLER_BEFORE_BUTTON, '') !!}

    <x-tooltip title="{{ __('Toggle theme mode') }}" position="bottom">
        <button id="darkModeToggle"
            class="hover:text-dark-900 relative flex items-center justify-center rounded-full text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white p-2 dark-mode-toggle"
            @click.prevent="darkMode = !darkMode" @click="menuToggle = true">
            <iconify-icon icon="lucide:moon" width="24" height="24" class="hidden dark:block"></iconify-icon>
            <iconify-icon icon="lucide:sun" width="24" height="24" class="dark:hidden"></iconify-icon>
        </button>
    </x-tooltip>
    {!! Hook::applyFilters(AdminFilterHook::DARK_MODE_TOGGLER_AFTER_BUTTON, '') !!}

    @if (config('app.show_demo_component_preview', false))
        <x-tooltip title="{{ __('Preview demo components') }}" position="bottom">
            <a href="{{ route('demo.preview') }}" class="hover:text-dark-900 relative flex p-2 items-center justify-center rounded-full text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                <iconify-icon icon="lucide:view" width="22" height="22"></iconify-icon>
            </a>
        </x-tooltip>
    @endif

    @if (env('GITHUB_LINK'))
        <x-tooltip title="{{ __('Go to Github') }}" position="bottom">
            <a href="{{ env('GITHUB_LINK') }}" target="_blank"
                class="hover:text-dark-900 relative flex p-2 items-center justify-center rounded-full text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                <iconify-icon icon="lucide:github" width="22" height="22"
                    class=""></iconify-icon>
            </a>
        </x-tooltip>
    @endif

    {!! Hook::applyFilters(AdminFilterHook::HEADER_AFTER_ACTIONS, '') !!}
</div>

{!! Hook::applyFilters(AdminFilterHook::USER_DROPDOWN_BEFORE, '') !!}

<div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
    <a class="flex items-center text-gray-700 dark:text-gray-300" href="#"
        @click.prevent="dropdownOpen = ! dropdownOpen">
        <span class="mr-3 h-8 w-8 overflow-hidden rounded-full">
            <img src="{{ auth()->user()->avatar_url ? auth()->user()->avatar_url : auth()->user()->getGravatarUrl() }}" alt="User" />
        </span>
    </a>

    <div x-show="dropdownOpen"
        class="absolute right-0 mt-[17px] flex w-[220px] flex-col rounded-md border bg-white dark:bg-gray-700 border-gray-200  p-3 shadow-theme-lg dark:border-gray-800 z-100"
        style="display: none">
        <div class="border-b border-gray-200 pb-2 dark:border-gray-800 mb-2">
            <span class="block font-medium text-gray-700 dark:text-gray-300">
                {{ auth()->user()->full_name }}
            </span>
            <span class="mt-0.5 block text-theme-sm text-gray-700 dark:text-gray-300">
                {{ auth()->user()->email }}
            </span>
            @if(filter_var(config('settings.auth_enable_email_verification', '0'), FILTER_VALIDATE_BOOLEAN))
                @if(auth()->user()->hasVerifiedEmail())
                    <span class="inline-flex items-center gap-1 mt-1 text-xs text-green-600 dark:text-green-400">
                        <iconify-icon icon="lucide:badge-check" width="12" height="12"></iconify-icon>
                        {{ __('Email Verified') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 mt-1 text-xs text-amber-600 dark:text-amber-400">
                        <iconify-icon icon="lucide:alert-circle" width="12" height="12"></iconify-icon>
                        {{ __('Email Not Verified') }}
                    </span>
                @endif
            @endif
        </div>

        {!! Hook::applyFilters(AdminFilterHook::USER_DROPDOWN_AFTER_USER_INFO, '') !!}

        <ul class="flex flex-col gap-1">
            <li class="border-b border-gray-200 pb-2 dark:border-gray-800">
                <a href="{{ route('profile.edit') }}"
                    class="group flex items-center gap-3 rounded-md px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-gray-300">
                    <iconify-icon icon="lucide:user" width="20" height="20" class="fill-gray-500 group-hover:fill-gray-700 dark:fill-gray-400 dark:group-hover:fill-gray-300"></iconify-icon>
                    {{ __('Edit profile') }}
                </a>
            </li>
            {!! Hook::applyFilters(AdminFilterHook::USER_DROPDOWN_AFTER_PROFILE_LINKS, '') !!}

            @can('settings.view')
            <li class="border-b border-gray-200 pb-2 dark:border-gray-800">
                <a href="{{ route('admin.settings.index') }}"
                    class="group flex items-center gap-3 rounded-md px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-gray-300 mt-2">
                    <iconify-icon icon="lucide:settings" width="20" height="20" class="fill-gray-500 group-hover:fill-gray-700 dark:fill-gray-400 dark:group-hover:fill-gray-300"></iconify-icon>
                    {{ __('Settings') }}
                </a>
            </li>
            @endcan
        </ul>

        {!! Hook::applyFilters(AdminFilterHook::USER_DROPDOWN_AFTER_SETTINGS_LINK, '') !!}

        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit"
                class="group flex items-center gap-3 rounded-md px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-gray-300 mt-2 w-full">
                <iconify-icon icon="lucide:log-out" width="20" height="20" class="fill-gray-500 group-hover:fill-gray-700 dark:group-hover:fill-gray-300"></iconify-icon>
                {{ __('Logout') }}
            </button>
        </form>

        {!! Hook::applyFilters(AdminFilterHook::USER_DROPDOWN_AFTER_LOGOUT, '') !!}

        @if (session()->has('original_user_id'))
            @php
                $originalUser = \App\Models\User::find(session('original_user_id'));
            @endphp
            @if ($originalUser)
                <form method="POST" action="{{ route('admin.users.switch-back') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="group flex items-center gap-3 rounded-md px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-gray-300 mt-1 w-full">
                        <iconify-icon icon="lucide:arrow-left" width="16" height="16"></iconify-icon>
                        {{ __('Switch back to') }} {{ $originalUser->full_name }}
                    </button>
                </form>
            @endif
        @endif
    </div>
</div>

{!! Hook::applyFilters(AdminFilterHook::HEADER_RIGHT_MENU_AFTER, '') !!}