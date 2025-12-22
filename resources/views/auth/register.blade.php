@extends('auth.layouts.app')

@section('title')
    {{ $pageTitle ?? __('Create Account') }} | {{ config('app.name') }}
@endsection

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-4 text-center">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ $pageTitle ?? __('Create Account') }}
        </h1>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ $pageDescription ?? __('Fill in the details below') }}
        </p>
    </div>

    {!! Hook::applyFilters(AuthFilterHook::REGISTER_FORM_BEFORE, '') !!}

    <form
        action="{{ route('register') }}"
        method="POST"
        x-data="{ loading: false }"
        @submit="loading = true"
        data-prevent-unsaved-changes
    >
        @csrf
        <div class="space-y-4">
            <x-messages />

            {!! Hook::applyFilters(AuthFilterHook::REGISTER_FORM_FIELDS_BEFORE, '') !!}

            {{-- Name Fields --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="first_name">{{ __('First Name') }}</label>
                    <input
                        autofocus
                        type="text"
                        id="first_name"
                        name="first_name"
                        autocomplete="given-name"
                        placeholder="{{ __('First name') }}"
                        class="form-control @error('first_name') border-red-500 ring-red-500 @enderror"
                        value="{{ old('first_name') }}"
                        required
                    />
                    @error('first_name')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="form-label" for="last_name">{{ __('Last Name') }}</label>
                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        autocomplete="family-name"
                        placeholder="{{ __('Last name') }}"
                        class="form-control @error('last_name') border-red-500 ring-red-500 @enderror"
                        value="{{ old('last_name') }}"
                        required
                    />
                    @error('last_name')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Email Field --}}
            <div>
                <label class="form-label" for="email">{{ __('Email') }}</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    autocomplete="email"
                    placeholder="{{ __('Enter your email') }}"
                    class="form-control @error('email') border-red-500 ring-red-500 @enderror"
                    value="{{ old('email') }}"
                    required
                />
                @error('email')
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password Field --}}
            <x-inputs.password
                name="password"
                label="{{ __('Password') }}"
                placeholder="{{ __('Enter your password') }}"
                autocomplete="new-password"
                required
            />

            {{-- Confirm Password Field --}}
            <x-inputs.password
                name="password_confirmation"
                label="{{ __('Confirm Password') }}"
                placeholder="{{ __('Confirm your password') }}"
                autocomplete="new-password"
                required
            />

            {!! Hook::applyFilters(AuthFilterHook::REGISTER_FORM_FIELDS_AFTER, '') !!}

            <x-recaptcha page="registration" />

            {!! Hook::applyFilters(AuthFilterHook::REGISTER_FORM_FIELDS_BEFORE_SUBMIT, '') !!}

            {{-- Submit Button --}}
            <div>
                <button type="submit" class="btn-primary w-full" :disabled="loading">
                    <span x-show="!loading">{{ __('Create Account') }}</span>
                    <iconify-icon x-show="loading" icon="lucide:loader-circle" class="animate-spin"></iconify-icon>
                    <iconify-icon x-show="!loading" icon="lucide:user-plus" class="ml-2"></iconify-icon>
                </button>
            </div>

            {!! Hook::applyFilters(AuthFilterHook::REGISTER_FORM_FIELDS_AFTER_SUBMIT, '') !!}

            {{-- Login Link --}}
            @if($showLoginLink ?? true)
            <div class="text-center pt-3 mt-1 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 font-medium">
                        {{ __('Sign in') }}
                    </a>
                </p>
            </div>
            @endif
        </div>
    </form>

    {!! Hook::applyFilters(AuthFilterHook::REGISTER_FORM_AFTER, '') !!}
</div>

{!! Hook::doAction(AuthActionHook::AFTER_REGISTER_FORM_RENDER) !!}
@endsection
