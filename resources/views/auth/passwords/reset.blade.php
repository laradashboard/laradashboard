@extends('auth.layouts.app')

@section('title')
    {{ __('Reset Password') }} | {{ config('app.name') }}
@endsection

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-4 text-center">
        <div class="mx-auto w-10 h-10 bg-brand-100 dark:bg-brand-900/30 rounded-full flex items-center justify-center mb-2">
            <iconify-icon icon="lucide:lock-keyhole" class="text-lg text-brand-600 dark:text-brand-400"></iconify-icon>
        </div>
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ __('Set New Password') }}
        </h1>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ __('Enter your new password below.') }}
        </p>
    </div>

    <form action="{{ route('password.update') }}" method="POST" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="space-y-4">
            <x-messages />

            {{-- Email Field --}}
            <div>
                <label class="form-label" for="email">{{ __('Email') }}</label>
                <input
                    autofocus
                    type="email"
                    id="email"
                    name="email"
                    autocomplete="email"
                    placeholder="{{ __('Enter your email') }}"
                    class="form-control @error('email') border-red-500 ring-red-500 @enderror"
                    value="{{ $email ?? old('email') }}"
                    required
                />
                @error('email')
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password Field --}}
            <x-inputs.password
                name="password"
                label="{{ __('New Password') }}"
                placeholder="{{ __('Enter your new password') }}"
                autocomplete="new-password"
                required
            />

            {{-- Confirm Password Field --}}
            <x-inputs.password
                name="password_confirmation"
                label="{{ __('Confirm Password') }}"
                placeholder="{{ __('Confirm your new password') }}"
                autocomplete="new-password"
                required
            />

            {{-- Submit Button --}}
            <div>
                <button type="submit" class="btn-primary w-full" :disabled="loading">
                    <span x-show="!loading">{{ __('Reset Password') }}</span>
                    <iconify-icon x-show="loading" icon="lucide:loader-circle" class="animate-spin"></iconify-icon>
                    <iconify-icon x-show="!loading" icon="lucide:check" class="ml-2"></iconify-icon>
                </button>
            </div>

            {{-- Back to Login --}}
            <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    <iconify-icon icon="lucide:arrow-left" class="mr-1"></iconify-icon>
                    {{ __('Back to login') }}
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
