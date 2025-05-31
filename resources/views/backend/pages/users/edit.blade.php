@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
    <div class="p-4 mx-auto max-w-7xl md:p-6">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

        {!! ld_apply_filters('users_after_breadcrumbs', '') !!}

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6"
                        enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Full Name') }}</label>
                                <input type="text" name="name" id="name" required value="{{ $user->name }}"
                                    placeholder="{{ __('Enter Full Name') }}"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            </div>
                            <div>
                                <label for="email"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('User Email') }}</label>
                                <input type="email" name="email" id="email" required value="{{ $user->email }}"
                                    placeholder="{{ __('Enter Email') }}"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            </div>
                            <div>
                                <x-inputs.password
                                    name="password"
                                    label="{{ __('Password (Optional)') }}"
                                    placeholder="{{ __('Enter Password') }}"
                                />
                            </div>
                            <div>
                                <x-inputs.password
                                    name="password_confirmation"
                                    label="{{ __('Confirm Password (Optional)') }}"
                                    placeholder="{{ __('Confirm Password') }}"
                                />
                            </div>
                            <div>
                                <x-inputs.combobox
                                    name="roles[]"
                                    label="{{ __('Assign Roles') }}"
                                    placeholder="{{ __('Select Roles') }}"
                                    :options="collect($roles)->map(fn($name, $id) => ['value' => $name, 'label' => ucfirst($name)])->values()->toArray()"
                                    :selected="$user->roles->pluck('name')->toArray()"
                                    :multiple="true"
                                    :searchable="false"
                                />
                            </div>
                            <div>
                                <label for="username"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Username') }}</label>

                                <input type="text" name="username" id="username" required value="{{ $user->username }}"
                                    placeholder="{{ __('Enter Username') }}"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            </div>
                            {!! ld_apply_filters('after_username_field', '', $user) !!}
                        </div>
                        <div class="mt-6 flex justify-start gap-4">
                            <button type="submit" class="btn-primary">{{ __('Save') }}</button>
                            <a href="{{ route('admin.users.index') }}" class="btn-default">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
