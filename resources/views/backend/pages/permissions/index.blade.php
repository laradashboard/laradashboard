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
                @include('backend.partials.search-form', [
                    'placeholder' => __('Search by name or group'),
                ])
            </div>
            <div class="table-responsive">
                <table id="dataTable" class="table">
                    <thead class="table-thead">
                        <tr class="table-tr">
                            <th width="3%" class="table-thead-th">{{ __('Sl') }}</th>
                            <th width="20%" class="table-thead-th">
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
                            <th width="15%" class="table-thead-th">
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
                            <th width="45%" class="table-thead-th">
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
                            <th width="10%" class="table-thead-th table-thead-th-last">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permissions as $permission)
                            <tr class="{{ $loop->index + 1 != count($permissions) ?  'table-tr' : '' }}">
                                <td class="table-td table-td-checkbox">{{ $loop->index + 1 }}</td>
                                <td class="table-td">
                                    {{ ucfirst($permission->name) }}
                                </td>
                                <td class="table-td">
                                    <span class="badge">{{ ucfirst($permission->group_name) }}</span>
                                </td>
                                <td class="table-td">
                                    @if ($permission->role_count > 0)
                                        <div class="flex items-center">
                                            <a href="{{ route('admin.permissions.show', $permission->id) }}" class="text-primary hover:underline">
                                                <span class="badge">{{ $permission->role_count }}</span>
                                                {{ $permission->roles_list }}
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-gray-400">{{ __('No roles assigned') }}</span>
                                    @endif
                                </td>
                                <td class="table-td flex justify-center">
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
                            <tr class="table-tr">
                                <td colspan="5" class="table-td text-center">
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
