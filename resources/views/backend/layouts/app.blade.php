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

    {!! Hook::applyFilters(AdminFilterHook::ADMIN_HEAD, '') !!}
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
        @include('backend.layouts.partials.sidebar.logo')

        <!-- Content Area -->
        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto bg-body dark:bg-gray-900">
            <!-- Small Device Overlay -->
            <div @click="sidebarToggle = false" :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
                class="fixed w-full h-screen z-9 bg-gray-900/50"></div>
            <!-- End Small Device Overlay -->

            @include('backend.layouts.partials.header.index')

            <!-- Main Content -->
            <main>
                @hasSection('admin-content')
                    @yield('admin-content')
                @else
                    @isset($slot) {{ $slot }} @endisset
                @endif
            </main>
            <!-- End Main Content -->
        </div>
    </div>

    <x-toast-notifications />

    {!! Hook::applyFilters(AdminFilterHook::ADMIN_FOOTER_BEFORE, '') !!}

    @stack('scripts')

    @if (!empty(config('settings.global_custom_js')))
    <script>
        {!! config('settings.global_custom_js') !!}
    </script>
    @endif

    @livewireScriptConfig

    {!! Hook::applyFilters(AdminFilterHook::ADMIN_FOOTER_AFTER, '') !!}
</body>
</html>
