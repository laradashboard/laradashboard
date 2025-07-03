@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
    <div class="p-4 mx-auto max-w-7xl md:p-6">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

        {!! ld_apply_filters('profile_edit_breadcrumbs', '') !!}

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="px-5 py-2.5 sm:px-6 sm:py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white">{{ __('Edit Profile') }} -
                        {{ $user->name }}</h3>
                </div>
                <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                    <x-messages />
                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                        @method('PUT')
                        @csrf
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Name') }}</label>
                                <input type="text" name="name" id="name" required value="{{ $user->name }}"
                                    class="form-control">
                            </div>
                            <div>
                                <label for="email"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Email') }}</label>
                                <input type="email" name="email" id="email" required value="{{ $user->email }}"
                                    class="form-control">
                            </div>
                            <div>
                                <label for="password"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Password (Optional)') }}</label>
                                <input type="password" name="password" id="password" class="form-control">
                            </div>
                            <div>
                                <label for="password_confirmation"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Confirm Password (Optional)') }}</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control">
                            </div>
                            {!! ld_apply_filters('profile_edit_fields', '', $user) !!}
                        </div>
                        {!! ld_apply_filters('profile_edit_after_fields', '', $user) !!}
                        <div class="mt-6 flex justify-start gap-4">
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600">{{ __('Save') }}</button>
                            <a href="{{ route('admin.dashboard') }}"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-white">
                                <i class="bi bi-x-circle mr-1"></i>
                                {{ __('Cancel') }}</a>
                        </div>
                        {!! ld_apply_filters('profile_edit_fields', '', $user) !!}
                    </div>
                    {!! ld_apply_filters('profile_edit_after_fields', '', $user) !!}
                    <x-buttons.submit-buttons cancelUrl="{{ route('admin.dashboard') }}" />
                </form>
            </div>
        </div>
    </div>
@endsection
