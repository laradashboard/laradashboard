@extends('backend.layouts.app')

@section('title')
   {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')

<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs">
        <x-slot name="title_after">
            @if (request('role'))
                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-800 dark:text-white">{{ ucfirst(request('role')) }}</span>
            @endif
        </x-slot>
    </x-breadcrumbs>

    {!! ld_apply_filters('users_after_breadcrumbs', '') !!}

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="px-5 py-4 sm:px-6 sm:py-5 flex gap-3 md:gap-1 flex-col md:flex-row justify-between items-center">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90 hidden md:block">{{ __('Users') }}</h3>

                @include('backend.partials.search-form', [
                    'placeholder' => __('Search by name or email'),
                ])

                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center">
                        <button id="roleDropdownButton" data-dropdown-toggle="roleDropdown" class="btn-default flex items-center justify-center gap-2" type="button">
                            <i class="bi bi-sliders"></i>
                            {{ __('Filter by Role') }}
                            <i class="bi bi-chevron-down"></i>
                        </button>

                        <!-- Dropdown menu -->
                        <div id="roleDropdown" class="z-10 hidden w-56 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                            <ul class="space-y-2">
                                <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded"
                                    onclick="handleRoleFilter('')">
                                    {{ __('All Roles') }}
                                </li>
                                @foreach ($roles as $id => $name)
                                    <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded {{ request('role') === $name ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                        onclick="handleRoleFilter('{{ $name }}')">
                                        {{ ucfirst($name) }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    @if (auth()->user()->can('user.edit'))
                        <a href="{{ route('admin.users.create') }}" class="btn-primary">
                            <i class="bi bi-plus-circle mr-2"></i>
                            {{ __('New User') }}
                        </a>
                    @endif
                </div>
            </div>
            <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 overflow-x-auto overflow-y-visible">
                <table id="dataTable" class="w-full dark:text-gray-400">
                    <thead class="bg-light text-capitalize">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th width="5%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Sl') }}</th>
                            <th width="15%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">
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
                            <th width="10%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">
                                <div class="flex items-center">
                                    {{ __('Email') }}
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'email' ? '-email' : 'email']) }}" class="ml-1">
                                        @if(request()->sort === 'email')
                                            <i class="bi bi-sort-alpha-down text-primary"></i>
                                        @elseif(request()->sort === '-email')
                                            <i class="bi bi-sort-alpha-up text-primary"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up text-gray-400"></i>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th width="30%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Roles') }}</th>
                            @php ld_apply_filters('user_list_page_table_header_before_action', '') @endphp
                            <th width="15%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Action') }}</th>
                            @php ld_apply_filters('user_list_page_table_header_after_action', '') @endphp
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="{{ $loop->index + 1 != count($users) ?  'border-b border-gray-100 dark:border-gray-800' : '' }}">
                                <td class="px-5 py-4 sm:px-6">{{ $loop->index + 1 }}</td>
                                <td class="px-5 py-4 sm:px-6 flex items-center md:min-w-[200px]">
                                    <a data-tooltip-target="tooltip-user-{{ $user->id }}" href="{{ auth()->user()->canBeModified($user) ? route('admin.users.edit', $user->id) : '#' }}" class="flex items-center">
                                        <img src="{{ ld_apply_filters('user_list_page_avatar_item', $user->getGravatarUrl(40), $user) }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full mr-3">
                                        {{ $user->name }}
                                    </a>
                                    @if (auth()->user()->canBeModified($user))
                                    <div id="tooltip-user-{{ $user->id }}" href="{{ route('admin.users.edit', $user->id) }}" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                        {{ __('Edit User') }}
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:px-6">{{ $user->email }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    @foreach ($user->roles as $role)
                                        <span class="capitalize inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-800 dark:text-white">
                                            @if (auth()->user()->can('role.edit'))
                                                <a href="{{ route('admin.roles.edit', $role->id) }}" data-tooltip-target="tooltip-role-{{ $role->id }}-{{ $user->id }}" class="hover:text-primary">
                                                    {{ $role->name }}
                                                </a>
                                                <div id="tooltip-role-{{ $role->id }}-{{ $user->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                                    {{ __('Edit') }} {{ $role->name }} {{ __('Role') }}
                                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                                </div>
                                            @else
                                                {{ $role->name }}
                                            @endif
                                        </span>
                                    @endforeach
                                </td>
                                @php ld_apply_filters('user_list_page_table_row_before_action', '', $user) @endphp
                                <td class="px-5 py-4 sm:px-6 flex justify-center">
                                    <x-buttons.action-buttons :label="__('Actions')" :show-label="false" align="right">
                                        @if (auth()->user()->canBeModified($user))
                                            <x-buttons.action-item 
                                                :href="route('admin.users.edit', $user->id)" 
                                                icon="pencil" 
                                                :label="__('Edit')" 
                                            />
                                        @endif
                                        
                                        @if (auth()->user()->canBeModified($user, 'user.delete'))
                                            <div x-data="{ deleteModalOpen: false }">
                                                <x-buttons.action-item 
                                                    type="modal-trigger"
                                                    modal-target="deleteModalOpen"
                                                    icon="trash" 
                                                    :label="__('Delete')" 
                                                    class="text-red-600 dark:text-red-400"
                                                />
                                                
                                                <x-modals.confirm-delete
                                                    id="delete-modal-{{ $user->id }}"
                                                    title="{{ __('Delete User') }}"
                                                    content="{{ __('Are you sure you want to delete this user?') }}"
                                                    formId="delete-form-{{ $user->id }}"
                                                    formAction="{{ route('admin.users.destroy', $user->id) }}"
                                                    modalTrigger="deleteModalOpen"
                                                    cancelButtonText="{{ __('No, cancel') }}"
                                                    confirmButtonText="{{ __('Yes, Confirm') }}"
                                                />
                                            </div>
                                        @endif
                                        
                                        @if (auth()->user()->can('user.login_as') && $user->id != auth()->user()->id)
                                            <x-buttons.action-item 
                                                :href="route('admin.users.login-as', $user->id)" 
                                                icon="box-arrow-in-right" 
                                                :label="__('Login as')" 
                                            />
                                        @endif
                                    </x-buttons.action-buttons>
                                </td>
                                @php ld_apply_filters('user_list_page_table_row_after_action', '', $user) @endphp
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <p class="text-gray-500 dark:text-gray-400">{{ __('No users found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="my-4 px-4 sm:px-6">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function handleRoleFilter(value) {
        let currentUrl = new URL(window.location.href);

        // Preserve sort parameter if it exists.
        const sortParam = currentUrl.searchParams.get('sort');

        // Reset the search params but keep the sort if it exists.
        currentUrl.search = '';

        if (value) {
            currentUrl.searchParams.set('role', value);
        }

        // Re-add sort parameter if it existed.
        if (sortParam) {
            currentUrl.searchParams.set('sort', sortParam);
        }

        window.location.href = currentUrl.toString();
    }
</script>
@endpush
@endsection
