@extends('backend.auth.layouts.app')

@section('title')
    404 - Page Not Found - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="relative z-1 flex min-h-screen flex-col items-center justify-center overflow-hidden p-6">
    <div class="mx-auto w-full max-w-[242px] text-center sm:max-w-[472px]">
        <h1 class="mb-8 text-title-md font-bold text-gray-800 dark:text-white/90 xl:text-title-2xl">
          ERROR
        </h1>
        <h1 class="mb-8 text-title-md font-bold text-gray-800 dark:text-white/90 xl:text-title-2xl">
            404
        </h1>

        <p class="mt-2">
            Sorry! Page Not Found!
        </p>

        <p class="mb-6 mt-10 text-base text-gray-700 dark:text-gray-400 sm:text-lg">
            The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>

        <a href="{{ url()->previous() }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            <i class="bi bi-arrow-left mr-2"></i>
            {{ __('Back') }}
        </a>

        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            <i class="bi bi-grid mr-2"></i>
            {{ __('Back to Dashboard') }}
        </a>

        <a href="{{ route('admin.login') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            {{ __('Login Again') }}
            <i class="bi bi-arrow-right ml-2"></i>
        </a>
    </div>
</div>
@endsection