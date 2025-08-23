<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" href="{{ config('settings.site_favicon') ?? asset('favicon.ico') }}" type="image/x-icon">

    @include('backend.layouts.partials.theme-colors')
    @yield('before_vite_build')

    @livewireStyles
    @viteReactRefresh
    @vite(['resources/js/app.js', 'resources/css/app.css'], 'build')
    @stack('styles')
    @yield('before_head')

    @if (!empty(config('settings.global_custom_css')))
    <style>
        {!! config('settings.global_custom_css') !!}
    </style>
    @endif

    @include('backend.layouts.partials.integration-scripts')

    @php echo ld_apply_filters('admin_head', ''); @endphp
</head>

<body x-data="{
    page: 'ecommerce',
    darkMode: false,
    stickyMenu: false,
    sidebarToggle: $persist(false),
    scrollTop: false
}"
x-init="
    darkMode = JSON.parse(localStorage.getItem('darkMode')) ?? false;
    $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));
    $watch('sidebarToggle', value => localStorage.setItem('sidebarToggle', JSON.stringify(value)));
    
    // Add loaded class for smooth fade-in
    $nextTick(() => {
        document.querySelector('.app-container').classList.add('loaded');
    });
"
:class="{ 'dark bg-gray-900': darkMode === true }">

    <!-- Page Wrapper with smooth fade-in -->
    <div class="app-container flex h-screen overflow-hidden">
        @include('backend.layouts.partials.sidebar-logo')

        <!-- Content Area -->
        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto bg-body dark:bg-gray-900">
            <!-- Small Device Overlay -->
            <div @click="sidebarToggle = false" :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
                class="fixed w-full h-screen z-9 bg-gray-900/50"></div>
            <!-- End Small Device Overlay -->

            @include('backend.layouts.partials.header.index')

            <!-- Main Content -->
            <main>
                @yield('admin-content')

                @isset($slot)
                    {{ $slot }}
                @endisset
            </main>
            <!-- End Main Content -->
        </div>
    </div>

    {!! ld_apply_filters('admin_footer_before', '') !!}

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const html = document.documentElement;
            const darkModeToggle = document.getElementById('darkModeToggle');
            const header = document.getElementById('appHeader');

            // Update header background based on current mode
            function updateHeaderBg() {
                if (!header) return;
                const isDark = html.classList.contains('dark');
            }

            // Initialize dark mode
            const savedDarkMode = localStorage.getItem('darkMode');
            if (savedDarkMode === 'true') {
                html.classList.add('dark');
            } else if (savedDarkMode === 'false') {
                html.classList.remove('dark');
            }

            updateHeaderBg();

            const observer = new MutationObserver(updateHeaderBg);
            observer.observe(html, {
                attributes: true,
                attributeFilter: ['class']
            });

            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isDark = html.classList.toggle('dark');
                    localStorage.setItem('darkMode', isDark);
                    updateHeaderBg();
                });
            }

            // Initialize sidebar state from localStorage if it exists
            if (window.Alpine) {
                const sidebarState = localStorage.getItem('sidebarToggle');
                if (sidebarState !== null) {
                    document.addEventListener('alpine:initialized', () => {
                        // Ensure the Alpine.js instance is ready
                        setTimeout(() => {
                            const alpineData = document.querySelector('body').__x;
                            if (alpineData && typeof alpineData.$data !== 'undefined') {
                                alpineData.$data.sidebarToggle = JSON.parse(sidebarState);
                            }
                        }, 0);
                    });
                }
            }
        });
    </script>

    @if (!empty(config('settings.global_custom_js')))
    <script>
        {!! config('settings.global_custom_js') !!}
    </script>
    @endif

    <!-- Global drawer handling script -->
    <script>
        // Define the global drawer opener function
        window.openDrawer = function(drawerId) {
            // Method 1: Try using the LaraDrawers registry if available
            if (window.LaraDrawers && window.LaraDrawers[drawerId]) {
                window.LaraDrawers[drawerId].open = true;
                return;
            }

            // Method 2: Try using Alpine.js directly
            const drawerEl = document.querySelector(`[data-drawer-id="${drawerId}"]`);
            if (drawerEl && window.Alpine) {
                try {
                    const alpineInstance = Alpine.getComponent(drawerEl);
                    if (alpineInstance) {
                        alpineInstance.open = true;
                        return;
                    }
                } catch (e) {
                    console.error('Alpine error:', e);
                }
            }

            // Method 3: Dispatch a custom event as fallback
            console.log('Opening drawer via event dispatch');
            window.dispatchEvent(new CustomEvent('open-drawer-' + drawerId));
        };

        // Initialize all drawer triggers on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-drawer-trigger]').forEach(function(element) {
                element.addEventListener('click', function(e) {
                    const drawerId = this.getAttribute('data-drawer-trigger');
                    if (drawerId) {
                        e.preventDefault();
                        window.openDrawer(drawerId);
                        return false;
                    }
                });
            });
        });
    </script>

    <x-toast-notifications />

    @livewireScriptConfig
    {!! ld_apply_filters('admin_footer_after', '') !!}
</body>
</html>
