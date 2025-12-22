@extends('backend.auth.layouts.app')

@section('title')
    {{ __('Reset Password') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div>
    <div class="mb-5 sm:mb-8">
        <h1 class="mb-2 font-semibold text-gray-700 text-title-sm dark:text-white/90 sm:text-title-md">
            {{ __('Reset Password') }}
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-300">
            {{ __('Enter your new password below.') }}
        </p>
    </div>

    {!! Hook::applyFilters(AuthFilterHook::PASSWORD_RESET_FORM_BEFORE, '') !!}

    <div>
        <form action="{{ route('password.update') }}" method="POST" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="space-y-5">
                <x-messages />

                <!-- Email -->
                <div>
                    <label class="form-label">
                        {{ __('Email') }}<span class="text-error-500">*</span>
                    </label>
                    <input autofocus type="email" id="email" name="email" autocomplete="email"
                        value="{{ $email ?? old('email') }}" placeholder="{{ __('Enter your email address') }}"
                        class="form-control @error('email') border-red-500 ring-red-500 @enderror"
                        required>
                    @error('email')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <x-inputs.password
                    name="password"
                    label="{{ __('New Password') }}"
                    placeholder="{{ __('Enter your new password') }}"
                    autocomplete="new-password"
                    required
                />

                <x-inputs.password
                    name="password_confirmation"
                    label="{{ __('Confirm Password') }}"
                    placeholder="{{ __('Confirm your new password') }}"
                    autocomplete="new-password"
                    required
                />

                <!-- Button -->
                <div>
                    <button type="submit" class="btn-primary w-full" :disabled="loading">
                        <span x-show="!loading">{{ __('Reset Password') }}</span>
                        <iconify-icon x-show="loading" icon="lucide:loader-circle" class="animate-spin"></iconify-icon>
                        <iconify-icon x-show="!loading" icon="lucide:check" class="ml-2"></iconify-icon>
                    </button>
                </div>
            </div>
        </form>
        <div class="flex justify-center items-center mt-5 text-sm font-normal text-center text-gray-700 dark:text-gray-300 sm:text-start">
            <a href="{{ route('login') }}" class="btn text-primary">
                <iconify-icon icon="lucide:chevron-left" class="mr-2"></iconify-icon>
                {{ __('Back to Login') }}
            </a>
        </div>
    </div>

    {!! Hook::applyFilters(AuthFilterHook::PASSWORD_RESET_FORM_AFTER, '') !!}
</div>
@endsection
