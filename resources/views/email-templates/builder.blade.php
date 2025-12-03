<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($template) ? 'Edit' : 'Create' }} Email Template - {{ config('app.name', 'Laravel') }}</title>

    <!-- <script src="https://cdn.jsdelivr.net/npm/iconify-icon@2.3.0/dist/iconify-icon.min.js"></script> -->

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/email-builder/index.jsx', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div
        id="email-builder-root"
        data-initial-data="{{ json_encode($initialData ?? null) }}"
        data-template-data="{{ json_encode($templateData ?? null) }}"
        data-save-url="{{ $saveUrl }}"
        data-list-url="{{ route('admin.email-templates.index') }}"
        data-upload-url="{{ route('admin.email-templates.upload-image') }}"
        data-video-upload-url="{{ route('admin.email-templates.upload-video') }}"
    ></div>
</body>
</html>
