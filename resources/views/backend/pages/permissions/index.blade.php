@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

    {!! ld_apply_filters('permissions_after_breadcrumbs', '') !!}

    <div class="space-y-6">
        <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5 flex flex-col md:flex-row justify-between items-center gap-3">
                {{-- <h3 class="text-base font-medium text-gray-700 dark:text-white/90">{{ __('Permissions') }}</h3> --}}

                @include('backend.partials.search-form', [
                    'placeholder' => __('Search by name or group'),
                ])
            </div>
            <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 overflow-x-auto overflow-y-visible">
                <table id="dataTable" class="w-full dark:text-gray-300">
                    <thead class="bg-light text-capitalize">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th width="5%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5 sm:px-6">{{ __('Sl') }}</th>
                            <th width="20%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">
                                <div class="flex items-center">
                                    {{ __('Name') }}
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'name' ? '-name' : 'name']) }}" class="ml-1">
                                        @if(request()->sort === 'name')
                                            <iconify-icon icon="lucide:sort-asc" class="text-primary"></iconify-icon>
                                        @elseif(request()->sort === '-name')
                                            <iconify-icon icon="lucide:sort-desc" class="text-primary"></iconify-icon>
                                        @else
                                            <iconify-icon icon="lucide:arrow-up-down" class="text-gray-400"></iconify-icon>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th width="15%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">
                                <div class="flex items-center">
                                    {{ __('Group') }}
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'group_name' ? '-group_name' : 'group_name']) }}" class="ml-1">
                                        @if(request()->sort === 'group_name')
                                            <iconify-icon icon="lucide:sort-asc" class="text-primary"></iconify-icon>
                                        @elseif(request()->sort === '-group_name')
                                            <iconify-icon icon="lucide:sort-desc" class="text-primary"></iconify-icon>
                                        @else
                                            <iconify-icon icon="lucide:arrow-up-down" class="text-gray-400"></iconify-icon>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th width="45%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">
                                <div class="flex items-center">
                                    {{ __('Roles') }}
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'role_count' ? '-role_count' : 'role_count']) }}" class="ml-1">
                                        @if(request()->sort === 'role_count')
                                            <iconify-icon icon="lucide:sort-asc" class="text-primary"></iconify-icon>
                                        @elseif(request()->sort === '-role_count')
                                            <iconify-icon icon="lucide:sort-desc" class="text-primary"></iconify-icon>
                                        @else
                                            <iconify-icon icon="lucide:arrow-up-down" class="text-gray-400"></iconify-icon>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th width="10%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permissions as $permission)
                            <tr class="{{ $loop->index + 1 != count($permissions) ?  'border-b border-gray-100 dark:border-gray-800' : '' }}">
                                <td class="px-5 py-4 sm:px-6">{{ $loop->index + 1 }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    {{ ucfirst($permission->name) }}
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-white">
                                        {{ ucfirst($permission->group_name) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    @if ($permission->role_count > 0)
                                        <div class="flex items-center">
                                            <a href="{{ route('admin.permissions.show', $permission->id) }}" class="text-primary hover:underline">
                                                <span class="inline-flex items-center justify-center px-2 py-1 mr-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-white">
                                                    {{ $permission->role_count }}
                                                </span>
                                                {{ $permission->roles_list }}
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-gray-400">{{ __('No roles assigned') }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:px-6 flex justify-center">
                                    <x-buttons.action-buttons :label="__('Actions')" :show-label="false" align="right">
                                        <x-buttons.action-item
                                            :href="route('admin.permissions.show', $permission->id)"
                                            icon="eye"
                                            :label="__('View Details')"
                                        />
                                    </x-buttons.action-buttons>
                                </td>
                            </tr>
                        @empty
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td colspan="5" class="px-5 py-4 sm:px-6 text-center">
                                    <span class="text-gray-500 dark:text-gray-300">{{ __('No permissions found') }}</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="my-4 px-4 sm:px-6">
                    {{ $permissions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
