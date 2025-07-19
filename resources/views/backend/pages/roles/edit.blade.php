@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')

<div class="p-4 mx-auto max-w-[var(--breakpoint-2xl)] md:p-6">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

    {!! ld_apply_filters('roles_edit_after_breadcrumbs', '') !!}

    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
        @method('PUT')
        @csrf
        <div class="space-y-8">
            <!-- Role Details Section -->
            <div class="rounded-md border border-gray-200 bg-white shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-white">
                        {{ __('Role Details') }}
                    </h3>
                    <x-buttons.submit-buttons
                        :classNames="['wrapper' => 'flex gap-4']"
                        cancelUrl="{{ route('admin.roles.index') }}"
                    />
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Role Name') }}
                            </label>
                            <input required autofocus name="name" value="{{ $role->name }}" type="text" placeholder="{{ __('Enter a Role Name') }}" class="mt-2 form-control">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions Section -->
            <div class="rounded-md border border-gray-200 bg-white shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-white">
                        {{ __('Permissions') }}
                    </h3>
                </div>
                <div class="p-4">
                    <div class="mb-4">
                        <input type="checkbox" id="checkPermissionAll" class="form-checkbox mr-2" {{ $roleService->roleHasPermissions($role, $all_permissions) ? 'checked' : '' }}>
                        <label for="checkPermissionAll" class="text-sm text-gray-700 dark:text-gray-300">
                            {{ __('Select All') }}
                        </label>
                    </div>
                    <hr class="mb-6">
                    @php $i = 1; @endphp
                    @foreach ($permission_groups as $group)
                    <div class="mb-6">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="group{{ $i }}Management" class="form-checkbox mr-2" {{ $roleService->roleHasPermissions($role, $roleService->getPermissionsByGroupName($group->name)) ? 'checked' : '' }}>
                            <label for="group{{ $i }}Management" class="capitalize text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ ucfirst($group->name) }}
                            </label>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 xl:grid-cols-6 gap-4" data-group="group{{ $i }}Management">
                            @php
                                $permissions = $roleService->getPermissionsByGroupName($group->name);
                            @endphp
                            @foreach ($permissions as $permission)
                            <div>
                                <input type="checkbox" id="checkPermission{{ $permission->id }}" name="permissions[]" value="{{ $permission->name }}" class="form-checkbox mr-2"
                                       {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                <label for="checkPermission{{ $permission->id }}" class="capitalize text-sm text-gray-700 dark:text-gray-300">
                                    {{ $permission->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @php $i++; @endphp
                    @endforeach

                    <x-buttons.submit-buttons cancelUrl="{{ route('admin.roles.index') }}" />
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    @include('backend.pages.roles.partials.scripts')
@endpush
