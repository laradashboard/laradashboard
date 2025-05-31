@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

    {!! ld_apply_filters('terms_after_breadcrumbs', '', $taxonomyModel) !!}

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Column -->
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        {{ $term ? __('Edit') : __('Add New') }} {{ $taxonomyModel->label_singular }}
                    </h3>
                </div>
                <div class="p-5 space-y-5 sm:p-6">
                    <form action="{{ route('admin.terms.store', $taxonomy) }}" method="POST" enctype="multipart/form-data">
                        @include('backend.pages.terms.partials.form')
                    </form>
                </div>
            </div>
        </div>

        <!-- Terms List Column -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ $taxonomyModel->label }}</h3>
                    <div class="flex items-center gap-2">
                        @include('backend.partials.search-form', [
                            'placeholder' => __('Search') . ' ' . strtolower($taxonomyModel->label),
                        ])
                    </div>
                </div>
                <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 overflow-x-auto overflow-y-visible">
                    <table id="dataTable" class="w-full dark:text-gray-400">
                        <thead class="bg-light text-capitalize">
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th width="40%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">
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
                                @if($taxonomyModel->hierarchical)
                                <th width="15%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Parent') }}</th>
                                @endif
                                <th width="10%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">
                                    <div class="flex items-center">
                                        {{ __('Count') }}
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'post_count' ? '-post_count' : 'post_count']) }}" class="ml-1">
                                            @if(request()->sort === 'post_count')
                                                <i class="bi bi-sort-numeric-down text-primary"></i>
                                            @elseif(request()->sort === '-post_count')
                                                <i class="bi bi-sort-numeric-up text-primary"></i>
                                            @else
                                                <i class="bi bi-arrow-down-up text-gray-400"></i>
                                            @endif
                                        </a>
                                    </div>
                                </th>
                                <th width="10%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-center px-5">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($terms as $termItem)
                                <tr class="{{ $loop->index + 1 != count($terms) ?  'border-b border-gray-100 dark:border-gray-800' : '' }}">
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex items-center">
                                            <div>
                                            @if($taxonomyModel->show_featured_image && $termItem->featured_image)
                                                    <img src="{{ Storage::url($termItem->featured_image) }}" alt="{{ $termItem->name }}" class="h-10 w-auto rounded mr-3">
                                                @elseif($taxonomyModel->show_featured_image)
                                                    <div class="h-10 w-10 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center mr-3">
                                                        <i class="bi bi-image text-gray-400"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                @if (auth()->user()->can('term.edit'))
                                                    <a href="{{ route('admin.terms.edit', ['taxonomy' => $taxonomy, 'term' => $termItem->id]) }}" class="hover:text-primary transition-colors">
                                                        {{ $termItem->name }}
                                                    </a>
                                                @else
                                                    {{ $termItem->name }}
                                                @endif
                                                <code class="text-sm px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded">{{ $termItem->slug }}</code>
                                            </div>
                                        </div>
                                    </td>
                                    @if($taxonomyModel->hierarchical)
                                    <td class="px-5 py-4 sm:px-6">
                                        @if($termItem->parent)
                                            @if (auth()->user()->can('term.edit'))
                                                <a href="{{ route('admin.terms.edit', ['taxonomy' => $taxonomy, 'term' => $termItem->parent->id]) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary transition-colors">
                                                    {{ $termItem->parent->name }}
                                                </a>
                                            @else
                                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $termItem->parent->name }}</span>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400">{{ __('None') }}</span>
                                        @endif
                                    </td>
                                    @endif
                                    <td class="px-5 py-4 sm:px-6">
                                        {{ $termItem->posts_count ?? $termItem->posts->count() }}
                                    </td>
                                    <td class="px-5 py-4 sm:px-6 text-center">
                                        <x-buttons.action-buttons :label="__('Actions')" :show-label="false" align="right">
                                            @if (auth()->user()->can('term.edit'))
                                                <x-buttons.action-item
                                                    :href="route('admin.terms.edit', ['taxonomy' => $taxonomy, 'term' => $termItem->id])"
                                                    icon="pencil"
                                                    :label="__('Edit')"
                                                />
                                            @endif

                                            @if (auth()->user()->can('term.delete'))
                                                <div x-data="{ deleteModalOpen: false }">
                                                    <x-buttons.action-item 
                                                        type="modal-trigger"
                                                        modal-target="deleteModalOpen"
                                                        icon="trash"
                                                        :label="__('Delete')"
                                                        class="text-red-600 dark:text-red-400"
                                                    />

                                                    <x-modals.confirm-delete
                                                        id="delete-modal-{{ $termItem->id }}"
                                                        title="{{ __('Delete') }} {{ strtolower($taxonomyModel->label_singular) }}"
                                                        content="{{ __('Are you sure you want to delete this') }} {{ strtolower($taxonomyModel->label_singular) }}?"
                                                        formId="delete-form-{{ $termItem->id }}"
                                                        formAction="{{ route('admin.terms.destroy', [$taxonomy, $termItem->id]) }}"
                                                        modalTrigger="deleteModalOpen"
                                                        cancelButtonText="{{ __('No, cancel') }}"
                                                        confirmButtonText="{{ __('Yes, Confirm') }}"
                                                    />
                                                </div>
                                            @endif
                                        </x-buttons.action-buttons>
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td colspan="{{ $taxonomyModel->hierarchical && $taxonomyModel->show_featured_image ? '6' : ($taxonomyModel->hierarchical || $taxonomyModel->show_featured_image ? '5' : '4') }}" class="px-5 py-4 sm:px-6 text-center">
                                        <span class="text-gray-500 dark:text-gray-400">{{ __('No') }} {{ strtolower($taxonomyModel->label) }} {{ __('found') }}</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="my-4 px-4 sm:px-6">
                        {{ $terms->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
