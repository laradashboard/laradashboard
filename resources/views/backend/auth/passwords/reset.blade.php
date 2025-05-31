@extends('backend.auth.layouts.app')

@section('title')
    {{ __('Reset Password') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div>
    <div class="mb-5 sm:mb-8">
        <h1 class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
            {{ __('Reset Password') }}
        </h1>
    </div>
    <div>
        <form action="{{ route('admin.password.reset.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="space-y-5">
                <x-messages />
                <!-- Email -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{ __('E-Mail Address') }}<span class="text-error-500">*</span>
                    </label>
                    <input autofocus type="text" id="email" name="email" autocomplete="username"
                        value="{{ $email ?? old('email') }}" placeholder="Enter your email address"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800
                        @error('email') is-invalid @enderror">
                </div>
                <!-- Password -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{ __('Password') }}<span class="text-error-500">*</span>
                    </label>
                    <input type="password" name="password" autocomplete="new-password" required
                        placeholder="{{ __('Enter your password') }}"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 @error('password') is-invalid @enderror">

                    @error('password')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{ __('Confirm Password') }}<span class="text-error-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" autocomplete="new-password" required
                        placeholder="{{ __('Confirm your password') }}"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                </div>
                <!-- Button -->
                <div>
                    <button type="submit"
                        class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                        Reset Password
                        <i class="bi bi-box-arrow-in-right ml-2"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
