@extends('backend.auth.layouts.app')

@section('title')
    {{ __('Verify Your Email') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div>
    <div class="mb-5 sm:mb-8">
        <div class="mx-auto w-14 h-14 bg-brand-100 dark:bg-brand-900/30 rounded-full flex items-center justify-center mb-4">
            <iconify-icon icon="lucide:mail-check" class="text-2xl text-brand-600 dark:text-brand-400"></iconify-icon>
        </div>
        <h1 class="mb-2 font-semibold text-gray-700 text-title-sm dark:text-white/90 sm:text-title-md text-center">
            {{ __('Verify Your Email') }}
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-300 text-center">
            {{ __("We've sent a verification link to your email address.") }}
        </p>
    </div>

    @if (session('resent'))
        <div class="mb-4 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 text-sm">
            <div class="flex items-center gap-2">
                <iconify-icon icon="lucide:check-circle" class="text-lg"></iconify-icon>
                {{ __("A fresh verification link has been sent to your email address.") }}
            </div>
        </div>
    @endif

    <div class="space-y-5">
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-sm">
            <p class="mb-2">
                {{ __("Before proceeding, please check your email for a verification link.") }}
            </p>
            <p>
                {{ __("If you did not receive the email, click the button below to request another.") }}
            </p>
        </div>

        <form method="POST" action="{{ route('verification.resend') }}" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <button type="submit" class="btn-primary w-full" :disabled="loading">
                <span x-show="!loading">{{ __('Resend Verification Email') }}</span>
                <iconify-icon x-show="loading" icon="lucide:loader-circle" class="animate-spin"></iconify-icon>
                <iconify-icon x-show="!loading" icon="lucide:mail" class="ml-2"></iconify-icon>
            </button>
        </form>

        <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-700">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    <iconify-icon icon="lucide:log-out" class="mr-1"></iconify-icon>
                    {{ __('Sign out') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
