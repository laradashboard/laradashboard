<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(RoleFilterHook::ROLE_SHOW_AFTER_BREADCRUMBS, '', $role) !!}

    <div class="space-y-6">
        {{-- Header Card --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 dark:bg-primary/20">
                        <iconify-icon icon="lucide:shield" class="text-2xl text-primary"></iconify-icon>
                    </div>
                    <div>
                        <h3 class="text-base font-medium text-gray-700 dark:text-white/90">
                            {{ $role->name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $role->permissions_count }} {{ __('permissions') }} &bull; {{ $role->users_count }} {{ __('users') }}
                        </p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content (Left - 2 columns) --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Permissions by Group --}}
                    <x-card.card bodyClass="!p-5 !space-y-4">
                        <x-slot:header>{{ __('Permissions') }}</x-slot:header>

                        @if($role->permissions->count() > 0)
                            @php
                                $groupedPermissions = $role->permissions->groupBy('group_name');
                            @endphp

                            <div class="space-y-4">
                                @foreach($permission_groups as $group)
                                    @if($groupedPermissions->has($group->name))
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                            <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">{{ $group->name }}</h4>
                                            </div>
                                            <div class="p-4">
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($groupedPermissions[$group->name] as $permission)
                                                        <a href="{{ route('admin.permissions.show', $permission->id) }}" class="badge">
                                                            <iconify-icon icon="lucide:check" class="mr-1.5 text-xs"></iconify-icon>
                                                            {{ $permission->name }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-400 dark:text-gray-500 italic">{{ __('No permissions assigned.') }}</p>
                        @endif
                    </x-card.card>

                    {{-- Users with this Role --}}
                    <x-card.card bodyClass="!p-5">
                        <x-slot:header>
                            <div class="flex items-center justify-between w-full">
                                <span>{{ __('Users with this Role') }}</span>
                                <a href="{{ route('admin.users.index', ['role' => $role->name]) }}" class="text-sm text-primary hover:underline">
                                    {{ __('View All') }} ({{ $role->users_count }})
                                </a>
                            </div>
                        </x-slot:header>

                        @if($role->users->count() > 0)
                            <div class="space-y-3">
                                @foreach($role->users->take(5) as $user)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-md">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" class="w-8 h-8 rounded-full object-cover">
                                            <div>
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $user->full_name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                        @can('user.view')
                                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn-link text-sm">
                                                <iconify-icon icon="lucide:eye" class="mr-1"></iconify-icon>
                                                {{ __('View') }}
                                            </a>
                                        @endcan
                                    </div>
                                @endforeach

                                @if($role->users_count > 5)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center pt-2">
                                        {{ __('And :count more users...', ['count' => $role->users_count - 5]) }}
                                    </p>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-400 dark:text-gray-500 italic">{{ __('No users have this role.') }}</p>
                        @endif
                    </x-card.card>

                    {!! Hook::applyFilters(RoleFilterHook::ROLE_SHOW_AFTER_MAIN_CONTENT, '', $role) !!}
                </div>

                {{-- Sidebar (Right - 1 column) --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Role Details --}}
                    <x-card.card bodyClass="!p-4 !space-y-4">
                        <x-slot:header>{{ __('Role Details') }}</x-slot:header>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Role Name') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $role->name }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Guard Name') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $role->guard_name }}</p>
                        </div>
                    </x-card.card>

                    {{-- Statistics --}}
                    <x-card.card bodyClass="!p-4 !space-y-4">
                        <x-slot:header>{{ __('Statistics') }}</x-slot:header>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Total Users') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                <a href="{{ route('admin.users.index', ['role' => $role->name]) }}" class="text-primary hover:underline">
                                    {{ $role->users_count }} {{ __('users') }}
                                </a>
                            </p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Total Permissions') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $role->permissions_count }} {{ __('permissions') }}</p>
                        </div>
                    </x-card.card>

                    {{-- Metadata --}}
                    <x-card.card bodyClass="!p-4 !space-y-4">
                        <x-slot:header>{{ __('Metadata') }}</x-slot:header>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Role ID') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">#{{ $role->id }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                {{ $role->created_at->format('M d, Y h:i A') }}
                            </p>
                        </div>

                        @if($role->created_at->ne($role->updated_at))
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Last Updated') }}</label>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $role->updated_at->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        @endif
                    </x-card.card>

                    {!! Hook::applyFilters(RoleFilterHook::ROLE_SHOW_AFTER_SIDEBAR, '', $role) !!}
                </div>
            </div>
        </x-card>

        {{-- Danger Zone --}}
        @if ($role->is_deleteable && auth()->user()->can('role.delete'))
            <x-card.card class="border-red-200 dark:border-red-900/50">
                <x-slot:header>
                    <span class="text-red-600 dark:text-red-400">{{ __('Danger Zone') }}</span>
                </x-slot:header>

                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Delete this role') }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Once you delete a role, there is no going back. Please be certain.') }}</p>
                    </div>

                    <div x-data="{ deleteModalOpen: false }">
                        <button
                            @click="deleteModalOpen = true"
                            type="button"
                            class="btn-danger"
                        >
                            <iconify-icon icon="lucide:trash-2" class="mr-2"></iconify-icon>
                            {{ __('Delete Role') }}
                        </button>

                        <x-modals.confirm-delete
                            id="delete-modal-{{ $role->id }}"
                            title="{{ __('Delete Role') }}"
                            content="{{ __('Are you sure you want to delete this role? This action cannot be undone. All users with this role will lose their role assignment.') }}"
                            formId="delete-form-{{ $role->id }}"
                            formAction="{{ route('admin.roles.destroy', $role->id) }}"
                            modalTrigger="deleteModalOpen"
                            cancelButtonText="{{ __('No, cancel') }}"
                            confirmButtonText="{{ __('Yes, Delete') }}"
                        />
                    </div>
                </div>
            </x-card.card>
        @endif
    </div>

    {!! Hook::applyFilters(RoleFilterHook::ROLE_SHOW_AFTER_CONTENT, '', $role) !!}
</x-layouts.backend-layout>
