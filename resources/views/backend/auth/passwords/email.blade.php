@extends('backend.auth.layouts.app')

@section('title')
    {{ __('Forgot Password') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div>
    <div class="mb-5 sm:mb-8">
        <h1 class="mb-2 font-semibold text-gray-700 text-title-sm dark:text-white/90 sm:text-title-md">
            {{ __('Forgot Password') }}
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-300">
            {{ __('Enter your email address and we will send you a link to reset your password.') }}
        </p>
    </div>
    <div>
        <form action="{{ route('admin.password.email') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <x-messages />
                <!-- Email -->
                <div>
                    <label class="form-label">
                        {{ __('Email') }}<span class="text-error-500">*</span>
                    </label>
                    <input autofocus type="text" id="email" name="email" autocomplete="username"
                        placeholder="{{ __('Enter your email address') }}"
                        class="form-control">
                </div>
                <div>
                    <button type="submit" class="btn-primary w-full">
                        {{ __('Send Reset Link') }}
                        <iconify-icon icon="lucide:log-in" class="ml-2"></iconify-icon>
                    </button>
                </div>
            </div>
        </form>
        <div class="flex justify-center items-center mt-5 text-sm font-normal text-center text-gray-700 dark:text-gray-300 sm:text-start">
            <a href="{{ route('admin.login') }}" class="btn text-primary">
                <iconify-icon icon="lucide:chevron-left" class="mr-2"></iconify-icon>
                {{ __('Back to Login') }}
            </a>
        </div>
    </div>
</div>
@endsection

