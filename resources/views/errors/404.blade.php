@extends('backend.auth.layouts.app')

@section('title')
    404 - {{ __('Page Not Found') }} - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="relative z-1 flex min-h-screen flex-col items-center justify-center overflow-hidden p-6">
    <div class="mx-auto w-full max-w-[242px] text-center sm:max-w-[472px]">
        <h1 class="mb-8 text-title-md font-bold text-gray-700 dark:text-white/90 xl:text-title-2xl">
            {{ __('ERROR') }}
        </h1>
        <h1 class="mb-8 text-title-md font-bold text-gray-700 dark:text-white/90 xl:text-title-2xl">
            404
        </h1>

        <p class="mt-2">
            {{ __('Sorry! Page Not Found!') }}
        </p>

        <p class="mb-6 mt-10 text-base text-gray-700 dark:text-gray-300 sm:text-lg">
            {{ __('The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.') }}
        </p>

        @include('errors.partials.links')
    </div>
</div>
@endsection
