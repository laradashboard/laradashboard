<header id="appHeader"

x-data="{
    menuToggle: false,
    textColor: '',
    isDark: document.documentElement.classList.contains('dark'),
    init() {
        this.updateBg();
        this.updateColor();
        const observer = new MutationObserver(() => {
            this.updateBg();
            this.updateColor();
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    },
    updateBg() {
        this.isDark = document.documentElement.classList.contains('dark');
        const liteBg = '{{ config('settings.navbar_bg_lite') }}';
        const darkBg = '{{ config('settings.navbar_bg_dark') }}';
        this.$el.style.backgroundColor = this.isDark ? darkBg : liteBg;
    },
    updateColor() {
        this.isDark = document.documentElement.classList.contains('dark');
        this.textColor = this.isDark
            ? '{{ config('settings.navbar_text_dark') }}'
            : '{{ config('settings.navbar_text_lite') }}';
    }
}"
x-init="init()"
    class="sticky top-0 flex w-full border-gray-200 lg:border-b dark:border-gray-800 transition-all duration-300 z-9">
    <div class="flex grow flex-col items-center justify-between lg:flex-row lg:px-6">
        <div
            class="flex w-full items-center justify-between gap-2 border-b border-gray-200 px-3 py-2 sm:gap-4 lg:justify-normal lg:border-b-0 lg:px-0 dark:border-gray-800">
            <button
                :class="sidebarToggle ? 'lg:bg-transparent dark:lg:bg-transparent bg-gray-100 dark:bg-gray-800' : ''"
                class="z-99999 flex h-10 w-10 items-center justify-center rounded-md border-gray-200 text-gray-700 lg:h-11 lg:w-11 dark:border-gray-800 dark:text-gray-300 transition-all duration-300"
                id="sidebar-toggle-button"
                @click.stop="sidebarToggle = !sidebarToggle; localStorage.setItem('sidebarToggle', sidebarToggle);">
                <iconify-icon
                 :icon="sidebarToggle ? 'mdi:menu-close' : 'mdi:menu-open'"
                width="26" height="26" class=""></iconify-icon>
            </button>

            <a href="{{ route('admin.dashboard') }}" class="lg:hidden">
                <img class="dark:hidden w-32" src="/images/logo/lara-dashboard.png" alt="Logo" />
                <img class="hidden dark:block w-32" src="/images/logo/lara-dashboard-dark.png" alt="Logo" />
            </a>
        </div>

        <!-- Adjusted Mobile Menu -->
        <div :class="menuToggle ? 'flex' : 'hidden'"
            class="w-full items-center justify-between gap-4 px-5 py-1 shadow-theme-md lg:flex lg:justify-end lg:px-0 lg:shadow-none">
            <div class="flex items-center gap-1">
                @include('backend.layouts.partials.demo-mode-notice')
                @include('backend.layouts.partials.locale-switcher')
                @php echo ld_apply_filters('dark_mode_toggler_before_button', ''); @endphp
                <button id="darkModeToggle"
                    class="hover:text-dark-900 relative flex items-center justify-center rounded-full text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white p-2 dark-mode-toggle"
                    @click.prevent="darkMode = !darkMode" @click="menuToggle = true">
                    <iconify-icon icon="lucide:moon" width="24" height="24" class="hidden dark:block"></iconify-icon>
                    <iconify-icon icon="lucide:sun" width="24" height="24" class="dark:hidden"></iconify-icon>
                </button>
                @php ld_apply_filters('dark_mode_toggler_after_button', '') @endphp

                @if (env('GITHUB_LINK') )
                    <a href="{{ env('GITHUB_LINK') }}" target="_blank"
                        class="hover:text-dark-900 relative flex p-2 items-center justify-center rounded-full text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                        <iconify-icon icon="lucide:github" width="22" height="22"
                            class=""></iconify-icon>
                    </a>
                @endif
            </div>

            <div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                <a class="flex items-center text-gray-700 dark:text-gray-300" href="#"
                    @click.prevent="dropdownOpen = ! dropdownOpen">
                    <span class="mr-3 h-8 w-8 overflow-hidden rounded-full">
                        <img src="{{ auth()->user()->getGravatarUrl() }}" alt="User" />
                    </span>
                </a>

                <!-- Dropdown Start -->
                <div x-show="dropdownOpen"
                    class="absolute right-0 mt-[17px] flex w-[220px] flex-col rounded-md border bg-white dark:bg-gray-700 border-gray-200  p-3 shadow-theme-lg dark:border-gray-800 z-100"
                    style="display: none">
                    <div class="border-b border-gray-200 pb-2 dark:border-gray-800 mb-2">
                        <span class="block font-medium text-gray-700 dark:text-gray-300">
                            {{ auth()->user()->name }}
                        </span>
                        <span class="mt-0.5 block text-theme-sm text-gray-700 dark:text-gray-300">
                            {{ auth()->user()->email }}
                        </span>
                    </div>

                    <ul class="flex flex-col gap-1 border-b border-gray-200 pb-2 dark:border-gray-800">
                        <li>
                            <a href="{{ route('profile.edit') }}"
                                class="group flex items-center gap-3 rounded-md px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                <iconify-icon icon="lucide:user" width="20" height="20" class="fill-gray-500 group-hover:fill-gray-700 dark:fill-gray-400 dark:group-hover:fill-gray-300"></iconify-icon>
                                {{ __('Edit profile') }}
                            </a>
                        </li>
                    </ul>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="group flex items-center gap-3 rounded-md px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-gray-300 mt-2 w-full">
                            <iconify-icon icon="lucide:log-out" width="20" height="20" class="fill-gray-500 group-hover:fill-gray-700 dark:group-hover:fill-gray-300"></iconify-icon>
                            {{ __('Logout') }}
                        </button>
                    </form>

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
                                    {{ __('Switch back to') }} {{ $originalUser->name }}
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
                <!-- Dropdown End -->
            </div>
        </div>
    </div>
</header>
<!-- End Header -->
