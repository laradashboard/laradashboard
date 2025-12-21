<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($template) ? 'Edit' : 'Create' }} Email Template - {{ config('app.name', 'Laravel') }}</title>

    {{-- Load iconify for icons --}}
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@2.3.0/dist/iconify-icon.min.js"></script>

    @include('backend.layouts.partials.theme-colors')
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/lara-builder/entry.jsx'])

    {{-- Inject PHP-registered blocks --}}
    {!! app(\App\Services\Builder\BuilderService::class)->injectToFrontend('email') !!}
</head>
<body class="font-sans antialiased">
    <div
        id="lara-builder-root"
        data-context="email"
        data-initial-data="{{ json_encode($initialData ?? null) }}"
        data-template-data="{{ json_encode($templateData ?? null) }}"
        data-save-url="{{ $saveUrl }}"
        data-list-url="{{ route('admin.email-templates.index') }}"
        data-upload-url="{{ route('admin.email-templates.upload-image') }}"
        data-video-upload-url="{{ route('admin.email-templates.upload-video') }}"
        data-redirect-url="{{ $redirectUrl ?? '' }}"
        data-translations='@json(__("*"))'
    ></div>

    {{-- Media Library Modal --}}
    <x-media-modal
        id="laraBuilderMediaModal"
        :title="__('Select Media')"
        :multiple="false"
        allowedTypes="all"
        buttonClass="hidden"
    />

    @stack('scripts')
</body>
</html>
