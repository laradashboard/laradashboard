<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($templateData) && $templateData ? 'Edit' : 'Create' }} {{ ucfirst($context ?? 'Content') }} - {{ config('app.name', 'Laravel') }}</title>

    {{-- Inject theme colors (brand color from settings) --}}
    @include('backend.layouts.partials.theme-colors')

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/lara-builder/entry.jsx', 'resources/js/app.js'])

    {{-- Inject PHP-registered blocks --}}
    {!! app(\App\Services\Builder\BuilderService::class)->injectToFrontend($context ?? 'email') !!}
</head>
<body class="font-sans antialiased">
    <x-builder.lara-builder
        :context="$context ?? 'email'"
        :initial-data="$initialData ?? null"
        :template-data="$templateData ?? null"
        :save-url="$saveUrl ?? null"
        :list-url="$listUrl ?? null"
        :upload-url="$uploadUrl ?? null"
        :video-upload-url="$videoUploadUrl ?? null"
    />
</body>
</html>
