@extends('backend.layouts.app')

@section('title')
Edit Role - Admin Panel
@endsection

@section('admin-content')

<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div x-data="{ pageName: `Edit Role`}">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2
                class="text-xl font-semibold text-gray-800 dark:text-white/90"
                x-text="pageName"
            >
                Edit Role
            </h2>

            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a
                            class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                            href="{{ route('admin.dashboard') }}"
                        >
                            Home
                            <i class="bi bi-chevron-right "></i>
                        </a>
                    </li>
                    <li>
                        <a
                            class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                            href="{{ route('admin.roles.index') }}"
                        >
                            Roles
                            <i class="bi bi-chevron-right "></i>
                        </a>
                    </li>
                    <li
                        class="text-sm text-gray-800 dark:text-white/90"
                        x-text="pageName"
                    >
                        Edit Role
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                Edit Role - {{ $role->name }}
            </h3>
          </div>
          <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
            @include('backend.layouts.partials.messages')
            <form
                action="{{ route('admin.roles.update', $role->id) }}"
                method="POST"
            >
                @method('PUT')
                @csrf
                <div class="w-full mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Role Name</label>
                    <div class="relative">
                        <input required autofocus name="name" value="{{ $role->name }}" type="text" placeholder="Enter a Role Name" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="permissions" class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Permissions</label>
                    <div class="mb-2">
                        <input
                            type="checkbox"
                            class="m-1"
                            id="checkPermissionAll"
                            value="1"
                            {{ App\Models\User::roleHasPermissions($role, $all_permissions) ? 'checked' : '' }}
                        />
                        <label for="checkPermissionAll" class="text-sm text-gray-700 dark:text-gray-400">Select All</label>
                    </div>
                    <hr class="mb-4" />
                    @php $i = 1; @endphp
                    @foreach ($permission_groups as $group)
                    <div class="flex flex-wrap items-start gap-3 mb-4">
                        <div class="basis-1/3">
                            <label
                                for="{{ $i }}Management" class="capitalize flex items-center text-sm font-medium text-gray-700 cursor-pointer select-none dark:text-gray-400"
                                title="Toggle all permissions in this group"
                                id="role-{{ $i }}-management-checkbox"
                                onclick="checkPermissionByGroup('role-{{ $i }}-management-checkbox', this)"
                            >
                                <input
                                    type="checkbox"
                                    class="m-1"
                                    id="{{ $i }}Management"
                                    value="{{ $group->name }}"
                                    {{ App\Models\User::roleHasPermissions($role, App\Models\User::getpermissionsByGroupName($group->name)) ? 'checked' : '' }}
                                />
                                {{ $group->name }}
                            </label>
                        </div>
                        <div class="col-9 role-{{ $i }}-management-checkbox">
                            @php
                                $permissions = App\Models\User::getpermissionsByGroupName($group->name);
                            @endphp
                            @foreach ($permissions as $permission)
                            <label for="checkPermission{{ $permission->id }}" class="capitalize flex items-center text-sm font-medium text-gray-700 cursor-pointer select-none dark:text-gray-400 mt-1">
                                <input
                                    type="checkbox"
                                    class="m-1"
                                    name="permissions[]"
                                    id="checkPermission{{ $permission->id }}"
                                    value="{{ $permission->name }}"
                                    {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                />
                                {{ $permission->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @php $i++; @endphp
                    @endforeach
                </div>

                <div class="mt-6 flex justify-start gap-4">
                    <button type="submit" class="btn-primary">{{ __('Save') }}</button>
                    <a href="{{ route('admin.roles.index') }}" class="btn-default">{{ __('Cancel') }}</a>
                </div>
            </form>
          </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('backend.pages.roles.partials.scripts')
@endpush