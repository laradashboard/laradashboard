<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(PermissionFilterHook::PERMISSION_SHOW_AFTER_BREADCRUMBS, '', $permission) !!}

    <div class="space-y-6">
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 dark:bg-primary/20">
                        <iconify-icon icon="lucide:key" class="text-2xl text-primary"></iconify-icon>
                    </div>
                    <div>
                        <h3 class="text-base font-medium text-gray-700 dark:text-white/90">
                            {{ $permission->name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $permission->group_name }} &bull; {{ $roles->count() }} {{ __('roles') }}
                        </p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content (Left - 2 columns) --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Assigned Roles --}}
                    <x-card.card bodyClass="!p-5">
                        <x-slot:header>{{ __('Assigned Roles') }}</x-slot:header>

                        @if($roles->count() > 0)
                            <div class="space-y-2">
                                @foreach($roles as $role)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-md">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10">
                                                <iconify-icon icon="lucide:shield" class="text-sm text-primary"></iconify-icon>
                                            </div>
                                            <span class="text-gray-700 dark:text-white font-medium">{{ $role->name }}</span>
                                        </div>
                                        @can('role.view')
                                            <a href="{{ route('admin.roles.show', $role->id) }}" class="btn-link text-sm">
                                                <iconify-icon icon="lucide:eye"></iconify-icon> {{ __('View Role') }}
                                            </a>
                                        @endcan
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-md text-center">
                                <iconify-icon icon="lucide:shield-off" class="text-4xl text-gray-300 dark:text-gray-600 mb-2"></iconify-icon>
                                <p class="text-gray-500 dark:text-gray-400">{{ __('No roles have this permission') }}</p>
                            </div>
                        @endif
                    </x-card.card>

                    {!! Hook::applyFilters(PermissionFilterHook::PERMISSION_SHOW_AFTER_MAIN_CONTENT, '', $permission) !!}
                </div>

                {{-- Sidebar (Right - 1 column) --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Permission Details --}}
                    <x-card.card bodyClass="!p-4 !space-y-4">
                        <x-slot:header>{{ __('Permission Details') }}</x-slot:header>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Permission Name') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 font-mono">{{ $permission->name }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Group') }}</label>
                            <p class="mt-1">
                                <span class="badge">{{ $permission->group_name }}</span>
                            </p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Guard Name') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $permission->guard_name }}</p>
                        </div>
                    </x-card.card>

                    {{-- Metadata --}}
                    <x-card.card bodyClass="!p-4 !space-y-4">
                        <x-slot:header>{{ __('Metadata') }}</x-slot:header>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Permission ID') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">#{{ $permission->id }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                {{ $permission->created_at->format('M d, Y h:i A') }}
                            </p>
                        </div>

                        @if($permission->created_at->ne($permission->updated_at))
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Last Updated') }}</label>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $permission->updated_at->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        @endif
                    </x-card.card>

                    {!! Hook::applyFilters(PermissionFilterHook::PERMISSION_SHOW_AFTER_SIDEBAR, '', $permission) !!}
                </div>
            </div>
        </x-card>
    </div>

    {!! Hook::applyFilters(PermissionFilterHook::PERMISSION_SHOW_AFTER_CONTENT, '', $permission) !!}
</x-layouts.backend-layout>
