<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Squartup module') }} | {{ config('app.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: system-ui, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
        }
        main {
            max-width: 36rem;
            padding: 2rem;
            border: 1px solid #334155;
            border-radius: 1rem;
            background: #1e293b;
        }
        h1 { margin: 0 0 0.75rem; font-size: 1.5rem; }
        p { margin: 0 0 0.75rem; line-height: 1.5; color: #cbd5e1; }
        p:last-child { margin-bottom: 0; }
        code { color: #93c5fd; }
    </style>
</head>
<body>
    <main>
        <h1>{{ __('Squartup module is loaded') }}</h1>
        <p>{{ __('The isolated Squartup module service provider, views, and health route are working.') }}</p>
        <p>{{ __('This page does not replace Laradashboard admin, auth, or layouts.') }}</p>
        <p><code>/squartup</code></p>
    </main>
</body>
</html>
