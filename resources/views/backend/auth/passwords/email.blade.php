@extends('backend.auth.layouts.app')

@section('title')
    {{ __('Forgot Password') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div>
    <div class="mb-5 sm:mb-8">
        <h1 class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
            {{ __('Forgot Password') }}
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('Enter your email address and we will send you a link to reset your password.') }}
        </p>
    </div>
    <div>
        <form action="{{ route('admin.password.update') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <x-messages />
                <!-- Email -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{ __('Email') }}<span class="text-error-500">*</span>
                    </label>
                    <input autofocus type="text" id="email" name="email" autocomplete="username"
                        placeholder="{{ __('Enter your email address') }}"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                </div>
                <div>
                    <button type="submit"
                        class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                        {{ __('Send Reset Link') }}
                        <i class="bi bi-box-arrow-in-right ml-2"></i>
                    </button>
                </div>
            </div>
        </form>
        <div class="flex justify-center items-center mt-5 text-sm font-normal text-center text-gray-700 dark:text-gray-400 sm:text-start">
            <a href="{{ route('admin.login') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">
                <i class="bi bi-chevron-left mr-2"></i>
                {{ __('Back to Login') }}
            </a>
        </div>
    </div>
</div>
@endsection