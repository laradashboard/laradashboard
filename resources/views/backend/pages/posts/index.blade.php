@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

    {!! ld_apply_filters('posts_list_after_breadcrumbs', '', $postType) !!}

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ $postTypeModel->label }}</h3>

                @include('backend.partials.search-form', [
                    'placeholder' => __('Search by title'),
                ])

                <div class="flex items-center gap-2">
                    <!-- Filters dropdown -->
                    <div class="flex items-center justify-center">
                        <button id="statusDropdownButton" data-dropdown-toggle="statusDropdown" class="btn-default flex items-center justify-center gap-2" type="button">
                            <i class="bi bi-sliders"></i>
                            {{ __('Filters') }}
                            <i class="bi bi-chevron-down"></i>
                        </button>

                        <!-- Status dropdown menu -->
                        <div id="statusDropdown" class="z-10 hidden w-56 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                            <h6 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Status') }}</h6>
                            <ul class="space-y-2">
                                <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded {{ !request('status') ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                    onclick="window.location.href='{{ route('admin.posts.index', ['postType' => $postType, 'search' => request('search'), 'category' => request('category')]) }}'">
                                    {{ __('All Status') }}
                                </li>
                                @foreach (['draft', 'publish', 'pending', 'future', 'private'] as $status)
                                    <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded {{ $status === request('status') ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                        onclick="window.location.href='{{ route('admin.posts.index', ['postType' => $postType, 'status' => $status, 'search' => request('search'), 'category' => request('category')]) }}'">
                                        {{ ucfirst($status) }}
                                    </li>
                                @endforeach
                            </ul>
                            
                            @if($postType === 'post' && count($categories) > 0)
                                <h6 class="mb-2 mt-4 text-sm font-medium text-gray-900 dark:text-white">{{ __('Categories') }}</h6>
                                <ul class="space-y-2">
                                    <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded {{ !request('category') ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                        onclick="window.location.href='{{ route('admin.posts.index', ['postType' => $postType, 'status' => request('status'), 'search' => request('search')]) }}'">
                                        {{ __('All Categories') }}
                                    </li>
                                    @foreach ($categories as $category)
                                        <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded {{ $category->id == request('category') ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                            onclick="window.location.href='{{ route('admin.posts.index', ['postType' => $postType, 'status' => request('status'), 'search' => request('search'), 'category' => $category->id]) }}'">
                                            {{ $category->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                    @if (auth()->user()->can('post.create'))
                        <a href="{{ route('admin.posts.create', $postType) }}" class="btn-primary">
                            <i class="bi bi-plus-circle mr-2"></i>
                            {{ __('New') }} {{ $postTypeModel->label_singular }}
                        </a>
                    @endif
                </div>
            </div>
            <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 overflow-x-auto overflow-y-visible">
                <table id="dataTable" class="w-full dark:text-gray-400">
                    <thead class="bg-light text-capitalize">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th width="5%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Sl') }}</th>
                            <th width="30%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">
                                <div class="flex items-center">
                                    {{ __('Title') }}
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'title' ? '-title' : 'title']) }}" class="ml-1">
                                        @if(request()->sort === 'title')
                                            <i class="bi bi-sort-alpha-down text-primary"></i>
                                        @elseif(request()->sort === '-title')
                                            <i class="bi bi-sort-alpha-up text-primary"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up text-gray-400"></i>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th width="15%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Author') }}</th>
                            @if($postType === 'post')
                                <th width="15%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Categories') }}</th>
                            @endif
                            <th width="10%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">
                                <div class="flex items-center">
                                    {{ __('Status') }}
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'status' ? '-status' : 'status']) }}" class="ml-1">
                                        @if(request()->sort === 'status')
                                            <i class="bi bi-sort-alpha-down text-primary"></i>
                                        @elseif(request()->sort === '-status')
                                            <i class="bi bi-sort-alpha-up text-primary"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up text-gray-400"></i>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th width="10%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">
                                <div class="flex items-center">
                                    {{ __('Date') }}
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'created_at' ? '-created_at' : 'created_at']) }}" class="ml-1">
                                        @if(request()->sort === 'created_at')
                                            <i class="bi bi-sort-numeric-down text-primary"></i>
                                        @elseif(request()->sort === '-created_at')
                                            <i class="bi bi-sort-numeric-up text-primary"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up text-gray-400"></i>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th width="15%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-center px-5">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr class="{{ $loop->index + 1 != count($posts) ?  'border-b border-gray-100 dark:border-gray-800' : '' }}">
                                <td class="px-5 py-4 sm:px-6">{{ $loop->index + 1 }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex gap-0.5 items-center">
                                        @if($post->featured_image)
                                            <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="h-8 w-12 object-cover rounded mr-3">
                                        @else
                                            <div class="h-8 w-12 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center mr-3">
                                                <i class="bi bi-image text-gray-400"></i>
                                            </div>
                                        @endif
                                        @if (auth()->user()->can('post.edit'))
                                            <a href="{{ route('admin.posts.edit', [$postType, $post->id]) }}" class="text-gray-800 dark:text-white font-medium hover:text-primary dark:hover:text-primary">
                                                {{ $post->title }}
                                            </a>
                                        @else
                                            {{ $post->title }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    {{ $post->user->name }}
                                </td>
                                @if($postType === 'post')
                                    <td class="px-5 py-4 sm:px-6">
                                        @foreach($post->categories as $category)
                                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full mr-1 mb-1 dark:bg-gray-700 dark:text-white">
                                                {{ $category->name }}
                                            </span>
                                        @endforeach
                                    </td>
                                @endif
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium 
                                        {{ $post->status === 'publish' ? 'text-green-800 bg-green-100 dark:bg-green-900/20 dark:text-green-400' : '' }}
                                        {{ $post->status === 'draft' ? 'text-gray-800 bg-gray-100 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                        {{ $post->status === 'pending' ? 'text-orange-800 bg-orange-100 dark:bg-orange-900/20 dark:text-orange-400' : '' }}
                                        {{ $post->status === 'future' ? 'text-blue-800 bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400' : '' }}
                                        {{ $post->status === 'private' ? 'text-purple-800 bg-purple-100 dark:bg-purple-900/20 dark:text-purple-400' : '' }}
                                        rounded-full">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    @if($post->published_at)
                                        {{ $post->published_at->format('M d, Y') }}
                                    @else
                                        {{ $post->created_at->format('M d, Y') }}
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:px-6 flex justify-center">
                                    <x-buttons.action-buttons :label="__('Actions')" :show-label="false" align="right">
                                        @if (auth()->user()->can('post.edit'))
                                            <x-buttons.action-item 
                                                :href="route('admin.posts.edit', [$postType, $post->id])" 
                                                icon="pencil" 
                                                :label="__('Edit')" 
                                            />
                                        @endif
                                        {!! ld_apply_filters('admin_post_actions_after_edit', '', $post, $postType) !!}
                                        
                                        @if (auth()->user()->can('post.view'))
                                            <x-buttons.action-item 
                                                :href="route('admin.posts.show', [$postType, $post->id])" 
                                                icon="eye" 
                                                :label="__('View')" 
                                            />
                                        @endif
                                        {!! ld_apply_filters('admin_post_actions_after_view', '', $post, $postType) !!}

                                        @if (auth()->user()->can('post.delete'))
                                            <div x-data="{ deleteModalOpen: false }">
                                                <x-buttons.action-item 
                                                    type="modal-trigger"
                                                    modal-target="deleteModalOpen"
                                                    icon="trash" 
                                                    :label="__('Delete')" 
                                                    class="text-red-600 dark:text-red-400"
                                                />
                                                
                                                <x-modals.confirm-delete
                                                    id="delete-modal-{{ $post->id }}"
                                                    title="{{ __('Delete') }} {{ strtolower($postTypeModel->label_singular) }}"
                                                    content="{{ __('Are you sure you want to delete this') }} {{ strtolower($postTypeModel->label_singular) }}?"
                                                    formId="delete-form-{{ $post->id }}"
                                                    formAction="{{ route('admin.posts.destroy', [$postType, $post->id]) }}"
                                                    modalTrigger="deleteModalOpen"
                                                    cancelButtonText="{{ __('No, cancel') }}"
                                                    confirmButtonText="{{ __('Yes, Confirm') }}"
                                                />
                                            </div>
                                        @endif
                                        {!! ld_apply_filters('admin_post_actions_after_delete', '', $post, $postType) !!}
                                    </x-buttons.action-buttons>
                                </td>
                            </tr>
                        @empty
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td colspan="7" class="px-5 py-4 sm:px-6 text-center">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('No') }} {{ strtolower($postTypeModel->label) }} {{ __('found') }}</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="my-4 px-4 sm:px-6">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
