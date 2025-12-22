<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    @php
        $favicon = config('settings.site_favicon');
    @endphp
    @if($favicon)
        <link rel="icon" href="{{ $favicon }}" type="image/x-icon">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @endif

    @include('backend.layouts.partials.theme-colors')
    @yield('before_vite_build')

    @viteReactRefresh
    @vite(['resources/js/app.js', 'resources/css/app.css'])

    @if (!empty(config('settings.global_custom_css')))
    <style>
        {!! config('settings.global_custom_css') !!}
    </style>
    @endif

    <style>
        [x-cloak] { display: none !important; }

        .auth-container {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        .auth-container.loaded {
            opacity: 1;
        }
    </style>

    @include('backend.layouts.partials.integration-scripts')
    @stack('styles')
</head>

<body x-data="{
    darkMode: false,
}"
x-init="
    darkMode = JSON.parse(localStorage.getItem('darkMode'));
    $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));

    $nextTick(() => {
        document.querySelector('.auth-container').classList.add('loaded');
    });
"
:class="{ 'dark': darkMode === true }"
class="bg-gray-50 dark:bg-gray-900 min-h-screen">

    <div class="auth-container min-h-screen flex flex-col justify-center py-8 px-4 sm:px-6 lg:px-8">
        {{-- Logo --}}
        <div class="mx-auto w-full max-w-sm mb-4">
            <a href="{{ url('/') }}" class="flex justify-center">
                @php
                    $logoLite = config('settings.site_logo_lite');
                    $logoDark = config('settings.site_logo_dark');
                @endphp
                @if($logoLite || $logoDark)
                    @if($logoLite)
                        <img src="{{ $logoLite }}" alt="{{ config('app.name') }}" class="h-8 dark:hidden">
                    @endif
                    @if($logoDark)
                        <img src="{{ $logoDark }}" alt="{{ config('app.name') }}" class="h-8 hidden dark:block">
                    @elseif($logoLite)
                        <img src="{{ $logoLite }}" alt="{{ config('app.name') }}" class="h-8 hidden dark:block">
                    @endif
                @else
                    <span class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ config('app.name') }}
                    </span>
                @endif
            </a>
        </div>

        {{-- Card Container --}}
        <div class="mx-auto w-full max-w-sm">
            <div class="bg-white dark:bg-gray-800 py-5 px-4 shadow-md rounded-lg sm:px-5 border border-gray-200 dark:border-gray-700">
                @yield('content')
            </div>

            {{-- Footer Links --}}
            <div class="mt-3 text-center">
                <a href="{{ url('/') }}" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <iconify-icon icon="lucide:arrow-left" class="mr-1"></iconify-icon>
                    {{ __('Back to Home') }}
                </a>
            </div>
        </div>

        {{-- Dark Mode Toggle --}}
        <div class="fixed bottom-4 right-4 flex gap-2 items-center">
            @include('backend.layouts.partials.locale-switcher', [
                'buttonClass' => 'inline-flex items-center justify-center text-gray-600 dark:text-gray-300 transition-colors rounded-full size-8 bg-white dark:bg-gray-800 shadow-md hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700',
                'iconClass' => 'text-gray-600 dark:text-gray-300',
                'iconSize' => '16',
            ])
            <button
                class="inline-flex items-center justify-center text-gray-600 dark:text-gray-300 transition-colors rounded-full size-8 bg-white dark:bg-gray-800 shadow-md hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700"
                @click.prevent="darkMode = !darkMode">
                <iconify-icon icon="lucide:sun" class="hidden dark:block text-base"></iconify-icon>
                <iconify-icon icon="lucide:moon" class="block dark:hidden text-base"></iconify-icon>
            </button>
        </div>
    </div>

    {!! Hook::applyFilters(AuthFilterHook::AUTH_FOOTER_CONTENT, '') !!}

    @stack('scripts')

    @if (!empty(config('settings.global_custom_js')))
    <script>
        {!! config('settings.global_custom_js') !!}
    </script>
    @endif
</body>

</html>
