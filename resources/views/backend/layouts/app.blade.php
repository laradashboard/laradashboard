<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', config('settings.app_name') !== '' ? config('settings.app_name') : config('app.name'))</title>

    <link rel="icon" href="{{ config('settings.site_favicon') ?? asset('favicon.ico') }}" type="image/x-icon">
    @yield('before_vite_build')

    @viteReactRefresh
    @vite(['resources/js/app.js', 'resources/css/app.css'], 'build')
    @stack('styles')
    @yield('before_head')

    @php echo ld_apply_filters('admin_head', ''); @endphp
</head>

<body
    x-data="{ page: 'ecommerce', loaded: true, darkMode: false, stickyMenu: false, sidebarToggle: false, scrollTop: false }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode'));
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{ 'dark bg-gray-900': darkMode === true }"
>
    <!-- Preloader -->
    <div
        x-show="loaded"
        x-init="window.addEventListener('DOMContentLoaded', () => { setTimeout(() => loaded = false, 500) })"
        class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black"
    >
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
    </div>
    <!-- End Preloader -->

    <!-- Page Wrapper -->
    <div class="flex h-screen overflow-hidden">
        @include('backend.layouts.partials.sidebar-logo')

        <!-- Content Area -->
        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
            <!-- Small Device Overlay -->
            <div
                @click="sidebarToggle = false"
                :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
                class="fixed w-full h-screen z-9 bg-gray-900/50"
            ></div>
            <!-- End Small Device Overlay -->

            @include('backend.layouts.partials.header')

            <!-- Main Content -->
            <main>
                @yield('admin-content')
            </main>
            <!-- End Main Content -->
        </div>
    </div>

    @stack('scripts')
</body>

</html>
