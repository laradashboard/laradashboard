<aside
    :class="sidebarToggle ? 'translate-x-0 lg:w-[85px] app-sidebar-minified' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-10 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 transition-all duration-300 ease-in-out {{ config('settings.sidebar_bg_lite') ? '' : 'bg-white' }} dark:border-gray-900 dark:bg-gray-900 lg:static lg:translate-x-0"
    id="appSidebar"
    x-data="{
        isHovered: false,
        init() {
            this.updateBg();
            const observer = new MutationObserver(() => this.updateBg());
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            // Check if sidebarToggle value is present in localStorage and use it
            if (localStorage.getItem('sidebarToggle')) {
                sidebarToggle = JSON.parse(localStorage.getItem('sidebarToggle'));
            }
        },
        updateBg() {
            const htmlHasDark = document.documentElement.classList.contains('dark');
            const liteBg = '{{ config('settings.sidebar_bg_lite') }}';
            const darkBg = '{{ config('settings.sidebar_bg_dark') }}';
            this.$el.style.backgroundColor = htmlHasDark ? darkBg : liteBg;
        }
    }"
    x-init="init()"
    @mouseenter="if(sidebarToggle) { isHovered = true; $el.classList.add('lg:w-[290px]'); $el.classList.remove('lg:w-[85px]', 'app-sidebar-minified'); }"
    @mouseleave="if(sidebarToggle) { isHovered = false; $el.classList.add('lg:w-[85px]', 'app-sidebar-minified'); $el.classList.remove('lg:w-[290px]'); }"
>
    <!-- Sidebar Header -->
    <div class="flex items-center justify-center gap-2 py-5 px-6 h-[100px] transition-all duration-300">
        @php
            $siteName = config('settings.app_name') ?: config('app.name', 'Lara Dashboard');
            $hasLiteLogo = !empty(config('settings.site_logo_lite'));
            $hasDarkLogo = !empty(config('settings.site_logo_dark'));
            $hasIcon = !empty(config('settings.site_icon'));
            $primaryColor = config('settings.theme_primary_color', '#635bff');
        @endphp
        <a href="{{ route('admin.dashboard') }}">
            <span class="logo transition-opacity duration-300" :class="sidebarToggle && !isHovered ? 'hidden opacity-0' : 'opacity-100'">
                @if($hasLiteLogo)
                    <img
                        class="dark:hidden max-h-20"
                        src="{{ config('settings.site_logo_lite') }}"
                        alt="{{ $siteName }}"
                    />
                @else
                    {{-- Text fallback for light mode --}}
                    <span class="dark:hidden text-xl font-bold text-gray-900" style="color: {{ $primaryColor }}">
                        {{ $siteName }}
                    </span>
                @endif
                @if($hasDarkLogo)
                    <img
                        class="hidden dark:block max-h-20"
                        src="{{ config('settings.site_logo_dark') }}"
                        alt="{{ $siteName }}"
                    />
                @else
                    {{-- Text fallback for dark mode --}}
                    <span class="hidden dark:inline text-xl font-bold text-white">
                        {{ $siteName }}
                    </span>
                @endif
            </span>
            @if($hasIcon)
                <img
                    class="logo-icon w-20 lg:w-12 transition-opacity duration-300"
                    :class="sidebarToggle && !isHovered ? 'lg:block opacity-100' : 'hidden opacity-0'"
                    src="{{ config('settings.site_icon') }}"
                    alt="{{ $siteName }}"
                />
            @else
                {{-- Icon fallback: first letter of site name --}}
                <span
                    class="logo-icon w-12 h-12 rounded-lg flex items-center justify-center text-white font-bold text-xl transition-opacity duration-300"
                    :class="sidebarToggle && !isHovered ? 'lg:flex opacity-100' : 'hidden opacity-0'"
                    style="background-color: {{ $primaryColor }}"
                >
                    {{ strtoupper(substr($siteName, 0, 1)) }}
                </span>
            @endif
        </a>
    </div>
    <!-- End Sidebar Header -->

    <div
        class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar"
    >
        @include('backend.layouts.partials.sidebar.menu')
    </div>
</aside>
<!-- End Sidebar -->
