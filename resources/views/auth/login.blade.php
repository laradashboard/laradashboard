@extends('auth.layouts.app')

@section('title')
    {{ $pageTitle ?? __('Sign In') }} | {{ config('app.name') }}
@endsection

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-4 text-center">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ $pageTitle ?? __('Sign In') }}
        </h1>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ $pageDescription ?? __('Enter your credentials to continue') }}
        </p>
    </div>

    {!! Hook::applyFilters(AuthFilterHook::LOGIN_FORM_BEFORE, '') !!}

    <form action="{{ request()->url() }}" method="POST" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        <div class="space-y-4">
            <x-messages />

            {!! Hook::applyFilters(AuthFilterHook::LOGIN_FORM_FIELDS_BEFORE_EMAIL, '') !!}

            {{-- Email Field --}}
            <div>
                <label class="form-label" for="email">{{ __('Email') }}</label>
                <input
                    autofocus
                    type="email"
                    id="email"
                    name="email"
                    autocomplete="username"
                    placeholder="{{ __('Enter your email') }}"
                    class="form-control @error('email') border-red-500 ring-red-500 @enderror"
                    value="{{ old('email') }}"
                    required
                />
                @error('email')
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            {!! Hook::applyFilters(AuthFilterHook::LOGIN_FORM_FIELDS_AFTER_EMAIL, '') !!}

            {!! Hook::applyFilters(AuthFilterHook::LOGIN_FORM_FIELDS_BEFORE_PASSWORD, '') !!}

            {{-- Password Field --}}
            <x-inputs.password
                name="password"
                label="{{ __('Password') }}"
                placeholder="{{ __('Enter your password') }}"
                required
            />

            {!! Hook::applyFilters(AuthFilterHook::LOGIN_FORM_FIELDS_AFTER_PASSWORD, '') !!}

            {{-- Remember Me & Forgot Password --}}
            <div class="flex items-center justify-between">
                <label for="remember" class="flex items-center gap-2 cursor-pointer">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        class="form-checkbox"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                </label>

                @if($showForgotPassword ?? true)
                    <a href="{{ route('password.request') }}" class="text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <x-recaptcha page="login" />

            {!! Hook::applyFilters(AuthFilterHook::LOGIN_FORM_FIELDS_BEFORE_SUBMIT, '') !!}

            {{-- Submit Button --}}
            <div>
                <button type="submit" class="btn-primary w-full" :disabled="loading">
                    <span x-show="!loading">{{ __('Sign In') }}</span>
                    <iconify-icon x-show="loading" icon="lucide:loader-circle" class="animate-spin"></iconify-icon>
                    <iconify-icon x-show="!loading" icon="lucide:log-in" class="ml-2"></iconify-icon>
                </button>
            </div>

            {!! Hook::applyFilters(AuthFilterHook::LOGIN_FORM_FIELDS_AFTER_SUBMIT, '') !!}

            {{-- Registration Link --}}
            @if($showRegistrationLink ?? false)
            <div class="text-center pt-3 mt-1 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Don't have an account?") }}
                    <a href="{{ route('register') }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 font-medium">
                        {{ __('Create one') }}
                    </a>
                </p>
            </div>
            @endif
        </div>
    </form>

    {!! Hook::applyFilters(AuthFilterHook::LOGIN_FORM_AFTER, '') !!}
</div>

{!! Hook::doAction(AuthActionHook::AFTER_LOGIN_FORM_RENDER) !!}
@endsection
