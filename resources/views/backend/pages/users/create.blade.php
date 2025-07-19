@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

        {!! ld_apply_filters('users_after_breadcrumbs', '') !!}

        <div class="space-y-6">
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="space-y-1">
                                <label for="name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Full Name') }}</label>
                                <input type="text" name="name" id="name" required autofocus
                                    value="{{ old('name') }}" placeholder="{{ __('Enter Full Name') }}"
                                    class="form-control">
                            </div>
                            <div class="space-y-1">
                                <label for="email"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('User Email') }}</label>
                                <input type="email" name="email" id="email" required value="{{ old('email') }}"
                                    placeholder="{{ __('Enter Email') }}" class="form-control">
                            </div>
                            <div>
                                <x-inputs.password name="password" label="{{ __('Password') }}"
                                    placeholder="{{ __('Enter Password') }}" required />
                            </div>
                            <div>
                                <x-inputs.password name="password_confirmation" label="{{ __('Confirm Password') }}"
                                    placeholder="{{ __('Confirm Password') }}" required />
                            </div>
                            <div>
                                <x-inputs.combobox name="roles[]" label="{{ __('Assign Roles') }}"
                                    placeholder="{{ __('Select Roles') }}" :options="collect($roles)
                                        ->map(fn($name, $id) => ['value' => $name, 'label' => ucfirst($name)])
                                        ->values()
                                        ->toArray()" :selected="old('roles', [])"
                                    :multiple="true" :searchable="false" />
                            </div>
                            <div class="space-y-1">
                                <label for="username"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Username') }}</label>

                                <input type="text" name="username" id="username" required value="{{ old('username') }}"
                                    placeholder="{{ __('Enter Username') }}" class="form-control">
                            </div>
                            {!! ld_apply_filters('after_username_field', '', null) !!}
                        </div>

                        <div class="mt-6">
                            <x-buttons.submit-buttons cancelUrl="{{ route('admin.users.index') }}" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
