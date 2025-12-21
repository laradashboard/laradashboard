<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Installation') }} | Lara Dashboard</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <style>
        :root {
            --color-primary: #635bff;
            --color-primary-hover: #4f47eb;
            --color-secondary: #1f2937;
        }
    </style>

    @livewireStyles
    @viteReactRefresh
    @vite(['resources/js/app.js', 'resources/css/app.css'], 'build')
</head>

<body x-data="{
    darkMode: false
}"
x-init="
    darkMode = JSON.parse(localStorage.getItem('darkMode')) ?? false;
    $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));
"
:class="{ 'dark bg-gray-900': darkMode === true }"
class="antialiased">

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 py-8 px-4">
        <div class="max-w-2xl mx-auto">
            {{ $slot }}
        </div>
    </div>

    @livewireScriptConfig
</body>
</html>
