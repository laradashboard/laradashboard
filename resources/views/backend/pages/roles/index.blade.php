@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

    {!! ld_apply_filters('roles_after_breadcrumbs', '') !!}

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5 flex flex-col md:flex-row justify-between items-center gap-1">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ __('Roles') }}</h3>

                @include('backend.partials.search-form', [
                    'placeholder' => __('Search by role name'),
                ])

                @if (auth()->user()->can('role.create'))
                    <a href="{{ route('admin.roles.create') }}" class="btn-primary">
                        <i class="bi bi-plus-circle mr-2"></i>
                        {{ __('New Role') }}
                    </a>
                @endif
            </div>
            <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 overflow-x-auto overflow-y-visible">
                <table id="dataTable" class="w-full dark:text-gray-400">
                    <thead class="bg-light text-capitalize">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th width="5%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Sl') }}</th>
                            <th width="10%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">
                                <div class="flex items-center">
                                    {{ __('Name') }}
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'name' ? '-name' : 'name']) }}" class="ml-1">
                                        @if(request()->sort === 'name')
                                            <i class="bi bi-sort-alpha-down text-primary"></i>
                                        @elseif(request()->sort === '-name')
                                            <i class="bi bi-sort-alpha-up text-primary"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up text-gray-400"></i>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th width="8%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">
                                <div class="flex items-center">
                                    {{ __('Users') }}
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'user_count' ? '-user_count' : 'user_count']) }}" class="ml-1">
                                        @if(request()->sort === 'user_count')
                                            <i class="bi bi-sort-numeric-down text-primary"></i>
                                        @elseif(request()->sort === '-user_count')
                                            <i class="bi bi-sort-numeric-up text-primary"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up text-gray-400"></i>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th width="35%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white">{{ __('Permissions') }}</th>
                            <th width="12%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr class="{{ $loop->index + 1 != count($roles) ?  'border-b border-gray-100 dark:border-gray-800' : '' }}">
                                <td class="px-5 py-4 sm:px-6">{{ $loop->index + 1 }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    @if (auth()->user()->can('role.edit'))
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" data-tooltip-target="tooltip-role-name-{{ $role->id }}" class="text-gray-800 dark:text-white hover:text-primary dark:hover:text-primary">
                                            {{ $role->name }}
                                        </a>
                                        <div id="tooltip-role-name-{{ $role->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                            {{ __('Edit Role') }}
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    @else
                                        {{ $role->name }}
                                    @endif

                                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('Total Permissions:') }} {{ $role->permissions->count() }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <a href="{{ route('admin.users.index', ['role' => $role->name]) }}" class="inline-flex items-center gap-1 text-sm text-primary hover:underline" data-tooltip-target="tooltip-users-role-{{ $role->id }}">
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-white">
                                            {{ $role->user_count }}
                                        </span>
                                    </a>
                                    <div id="tooltip-users-role-{{ $role->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                        {{ __('View') }} {{ $role->name }} {{ __('Users') }}
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div x-data="{ showAll: false }">
                                        <div>
                                            @foreach ($role->permissions->take(7) as $permission)
                                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-800 dark:text-white">
                                                    {{ $permission->name }}
                                                </span>
                                            @endforeach
                                            <template x-if="showAll">
                                                <div>
                                                    @foreach ($role->permissions->skip(7) as $permission)
                                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-800 dark:text-white">
                                                            {{ $permission->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </template>
                                        </div>
                                        @if ($role->permissions->count() > 7)
                                            <button @click="showAll = !showAll" class="text-primary text-sm mt-2">
                                                <span x-show="!showAll">+{{ $role->permissions->count() - 7 }} {{ __('more') }}</span>
                                                <span x-show="showAll">{{ __('Show less') }}</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6 flex justify-center">
                                    <x-buttons.action-buttons :label="__('Actions')" :show-label="false" align="right">
                                        @if (auth()->user()->can('role.edit') && $role->name != 'Superadmin')
                                            <x-buttons.action-item 
                                                :href="route('admin.roles.edit', $role->id)" 
                                                icon="pencil" 
                                                :label="__('Edit')" 
                                            />
                                        @endif

                                        @if (auth()->user()->can('role.delete') && $role->name != 'Superadmin')
                                            <div x-data="{ deleteModalOpen: false }">
                                                <x-buttons.action-item 
                                                    type="modal-trigger"
                                                    modal-target="deleteModalOpen"
                                                    icon="trash" 
                                                    :label="__('Delete')" 
                                                    class="text-red-600 dark:text-red-400"
                                                />
                                                
                                                <x-modals.confirm-delete
                                                    id="delete-modal-{{ $role->id }}"
                                                    title="{{ __('Delete Role') }}"
                                                    content="{{ __('Are you sure you want to delete this role?') }}"
                                                    formId="delete-form-{{ $role->id }}"
                                                    formAction="{{ route('admin.roles.destroy', $role->id) }}"
                                                    modalTrigger="deleteModalOpen"
                                                    cancelButtonText="{{ __('No, cancel') }}"
                                                    confirmButtonText="{{ __('Yes, Confirm') }}"
                                                />
                                            </div>
                                        @endif
                                        
                                        <x-buttons.action-item 
                                            :href="route('admin.users.index', ['role' => $role->name])" 
                                            icon="people" 
                                            :label="__('View Users')" 
                                        />
                                    </x-buttons.action-buttons>
                                </td>
                            </tr>
                        @empty
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td colspan="4" class="px-5 py-4 sm:px-6 text-center">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('No roles found') }}</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="my-4 px-4 sm:px-6">
                    {{ $roles->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
