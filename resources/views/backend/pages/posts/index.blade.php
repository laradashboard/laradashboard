@extends('backend.layouts.app')

@section('title')
    {{ __($breadcrumbs['title']) }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6" x-data="{ selectedPosts: [], selectAll: false, bulkDeleteModalOpen: false }">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs">
        <x-slot name="title_after">
            @if (auth()->user()->can('post.create'))
                <a href="{{ route('admin.posts.create', $postType) }}" class="btn-primary ml-2">
                    <i class="bi bi-plus-circle mr-2"></i>
                    {{ __("New {$postTypeModel->label_singular}") }}
                </a>
            @endif
        </x-slot>
    </x-breadcrumbs>

    {!! ld_apply_filters('posts_list_after_breadcrumbs', '', $postType) !!}

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ __($postTypeModel->label) }}</h3>

                <div class="w-full sm:w-auto">
                    @include('backend.partials.search-form', [
                        'placeholder' => __('Search by title'),
                    ])
                </div>

                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                    <!-- Bulk Actions dropdown -->
                    <div class="flex items-center justify-center" x-show="selectedPosts.length > 0">
                        <button id="bulkActionsButton" data-dropdown-toggle="bulkActionsDropdown" class="btn-danger flex items-center justify-center gap-2 text-sm" type="button">
                            <i class="bi bi-trash"></i>
                            <span>{{ __('Bulk Actions') }} (<span x-text="selectedPosts.length"></span>)</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>

                        <!-- Bulk Actions dropdown menu -->
                        <div id="bulkActionsDropdown" class="z-10 hidden w-48 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                            <h6 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Bulk Actions') }}</h6>
                            <ul class="space-y-2">
                                <li class="cursor-pointer text-sm text-red-600 dark:text-red-400 hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded"
                                    @click="bulkDeleteModalOpen = true">
                                    <i class="bi bi-trash mr-1"></i> {{ __('Delete Selected') }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Status Filter dropdown -->
                    <div class="flex items-center justify-center">
                        <button id="statusDropdownButton" data-dropdown-toggle="statusDropdown" class="btn-default flex items-center justify-center gap-2 text-sm" type="button">
                            <i class="bi bi-funnel"></i>
                            <span class="hidden sm:inline">{{ __('Status') }}</span>
                            @if(request('status'))
                                <span class="px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded-full dark:bg-red-900/20 dark:text-red-400">
                                    {{ ucfirst(request('status')) }}
                                </span>
                            @endif
                            <i class="bi bi-chevron-down"></i>
                        </button>

                        <!-- Status dropdown menu -->
                        <div id="statusDropdown" class="z-10 hidden w-48 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                            <h6 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Filter by Status') }}</h6>
                            <ul class="space-y-2">
                                <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded {{ !request('status') ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                    onclick="window.location.href='{{ route('admin.posts.index', ['postType' => $postType, 'search' => request('search'), 'category' => request('category'), 'tag' => request('tag')]) }}'">
                                    {{ __('All Status') }}
                                </li>
                                @foreach (['draft', 'publish', 'pending', 'future', 'private'] as $status)
                                    <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded {{ $status === request('status') ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                        onclick="window.location.href='{{ route('admin.posts.index', ['postType' => $postType, 'status' => $status, 'search' => request('search'), 'category' => request('category'), 'tag' => request('tag')]) }}'">
                                        {{ ucfirst($status) }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    @if($postType === 'post' && count($categories) > 0)
                        <!-- Category Filter dropdown -->
                        <div class="flex items-center justify-center">
                            <button id="categoryDropdownButton" data-dropdown-toggle="categoryDropdown" class="btn-default flex items-center justify-center gap-2 text-sm" type="button">
                                <i class="bi bi-grid"></i>
                                <span class="hidden sm:inline">{{ __('Category') }}</span>
                                @if(request('category'))
                                    <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900/20 dark:text-blue-400">
                                        {{ $categories->where('id', request('category'))->first()?->name }}
                                    </span>
                                @endif
                                <i class="bi bi-chevron-down"></i>
                            </button>

                            <!-- Category dropdown menu -->
                            <div id="categoryDropdown" class="z-10 hidden w-56 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                                <h6 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Filter by Category') }}</h6>
                                <ul class="space-y-2 max-h-48 overflow-y-auto">
                                    <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded {{ !request('category') ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                        onclick="window.location.href='{{ route('admin.posts.index', ['postType' => $postType, 'status' => request('status'), 'search' => request('search'), 'tag' => request('tag')]) }}'">
                                        {{ __('All Categories') }}
                                    </li>
                                    @foreach ($categories as $category)
                                        <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded {{ $category->id == request('category') ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                            onclick="window.location.href='{{ route('admin.posts.index', ['postType' => $postType, 'status' => request('status'), 'search' => request('search'), 'category' => $category->id, 'tag' => request('tag')]) }}'">
                                            {{ $category->name }}
                                            <span class="text-xs text-gray-500 dark:text-gray-400">({{ $category->posts->count() ?? 0 }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if($postType === 'post' && isset($tags) && count($tags) > 0)
                        <div class="flex items-center justify-center">
                            <button id="tagDropdownButton" data-dropdown-toggle="tagDropdown" class="btn-default flex items-center justify-center gap-2 text-sm" type="button">
                                <i class="bi bi-tags"></i>
                                <span class="hidden sm:inline">{{ __('Tags') }}</span>
                                @if(request('tag'))
                                    <span class="px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded-full dark:bg-green-900/20 dark:text-green-400">
                                        {{ $tags->where('id', request('tag'))->first()?->name }}
                                    </span>
                                @endif
                                <i class="bi bi-chevron-down"></i>
                            </button>

                            <!-- Tags dropdown menu -->
                            <div id="tagDropdown" class="z-10 hidden w-56 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                                <h6 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Filter by Tags') }}</h6>
                                <ul class="space-y-2 max-h-48 overflow-y-auto">
                                    <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded {{ !request('tag') ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                        onclick="window.location.href='{{ route('admin.posts.index', ['postType' => $postType, 'status' => request('status'), 'search' => request('search'), 'category' => request('category')]) }}'">
                                        {{ __('All Tags') }}
                                    </li>
                                    @foreach ($tags as $tag)
                                        <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded {{ $tag->id == request('tag') ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                            onclick="window.location.href='{{ route('admin.posts.index', ['postType' => $postType, 'status' => request('status'), 'search' => request('search'), 'category' => request('category'), 'tag' => $tag->id]) }}'">
                                            {{ $tag->name }}
                                            <span class="text-xs text-gray-500 dark:text-gray-400">({{ $tag->posts->count() ?? 0 }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 overflow-x-auto overflow-y-visible">
                <div class="overflow-x-auto">
                    <table id="dataTable" class="w-full dark:text-gray-400 min-w-full">
                        <thead class="bg-light text-capitalize">
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th width="5%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" 
                                            x-model="selectAll"
                                            @click="
                                                selectAll = !selectAll;
                                                selectedPosts = selectAll ? 
                                                    [...document.querySelectorAll('.post-checkbox')].map(cb => cb.value) : 
                                                    [];
                                            "
                                        >
                                    </div>
                                </th>
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
                                    <th width="20%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Categories') }}</th>
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
                                    <td class="px-5 py-4 sm:px-6">
                                        <input 
                                            type="checkbox" 
                                            class="post-checkbox form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" 
                                            value="{{ $post->id }}"
                                            x-model="selectedPosts"
                                        >
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex gap-0.5 items-center">
                                            @if($post->featured_image)
                                                <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-12 object-cover rounded mr-3">
                                            @else
                                                <div class="bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center mr-3">
                                                    <i class="w-12 text-center bi bi-image text-gray-400"></i>
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
                                            <span 
                                                class="cursor-help" 
                                                title="{{ $post->published_at->format('F d, Y \a\t g:i A') }}"
                                            >
                                                {{ $post->published_at->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span 
                                                class="cursor-help" 
                                                title="{{ $post->created_at->format('F d, Y \a\t g:i A') }}"
                                            >
                                                {{ $post->created_at->format('M d, Y') }}
                                            </span>
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
                                            {!! ld_apply_filters('admin_post_actions_after_edit', '', $post) !!}
                                            
                                            @if (auth()->user()->can('post.view'))
                                                <x-buttons.action-item 
                                                    :href="route('admin.posts.show', [$postType, $post->id])" 
                                                    icon="eye" 
                                                    :label="__('View')" 
                                                />
                                            @endif
                                            {!! ld_apply_filters('admin_post_actions_after_view', '', $post) !!}

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
                                            {!! ld_apply_filters('admin_post_actions_after_delete', '', $post) !!}
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
                </div>

                <div class="my-4 px-4 sm:px-6">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div 
        x-cloak 
        x-show="bulkDeleteModalOpen" 
        x-transition.opacity.duration.200ms 
        x-trap.inert.noscroll="bulkDeleteModalOpen" 
        x-on:keydown.esc.window="bulkDeleteModalOpen = false" 
        x-on:click.self="bulkDeleteModalOpen = false" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 p-4 backdrop-blur-md" 
        role="dialog" 
        aria-modal="true" 
        aria-labelledby="bulk-delete-modal-title"
    >
        <div 
            x-show="bulkDeleteModalOpen" 
            x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity" 
            x-transition:enter-start="opacity-0 scale-50" 
            x-transition:enter-end="opacity-100 scale-100" 
            class="flex max-w-md flex-col gap-4 overflow-hidden rounded-lg border border-outline border-gray-100 dark:border-gray-800 bg-white text-on-surface dark:border-outline-dark dark:bg-gray-700 dark:text-gray-400"
        >
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2 dark:border-gray-800">
                <div class="flex items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 p-1">
                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <h3 id="bulk-delete-modal-title" class="font-semibold tracking-wide text-gray-800 dark:text-white">
                    {{ __('Delete Selected') }} {{ $postTypeModel->label }}
                </h3>
                <button 
                    x-on:click="bulkDeleteModalOpen = false" 
                    aria-label="close modal" 
                    class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg p-1 dark:hover:bg-gray-600 dark:hover:text-white"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="1.4" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-4 text-center">
                <p class="text-gray-500 dark:text-gray-400">
                    {{ __('Are you sure you want to delete the selected') }} {{ strtolower($postTypeModel->label) }}? 
                    {{ __('This action cannot be undone.') }}
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 p-4 dark:border-gray-800">
                <form id="bulk-delete-form" action="{{ route('admin.posts.bulk-delete', $postType) }}" method="POST">
                    @method('DELETE')
                    @csrf
                    
                    <template x-for="id in selectedPosts" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>

                    <button 
                        type="button" 
                        x-on:click="bulkDeleteModalOpen = false" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                    >
                        {{ __('No, Cancel') }}
                    </button>

                    <button 
                        type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-300 dark:focus:ring-red-800"
                    >
                        {{ __('Yes, Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
