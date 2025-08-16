@extends('backend.layouts.app')

@section('title')
    {{ __($breadcrumbs['title']) }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6" x-data="{ selectedTerms: [], selectAll: false, bulkDeleteModalOpen: false }">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

    {!! ld_apply_filters('terms_after_breadcrumbs', '', $taxonomyModel) !!}

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Column -->
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-medium text-gray-700 dark:text-white/90">
                        {{ $term ? __("Edit {$taxonomyModel->label_singular}") : __("Add New {$taxonomyModel->label_singular}") }}
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
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-medium text-gray-700 dark:text-white/90">{{ __($taxonomyModel->label) }}</h3>
                    <div class="flex items-center gap-2">
                        <!-- Bulk Actions dropdown -->
                        <div class="relative flex items-center justify-center" x-show="selectedTerms.length > 0" x-data="{ open: false }">
                            <button @click="open = !open" class="btn-danger flex items-center justify-center gap-2 text-sm" type="button">
                                <iconify-icon icon="lucide:trash"></iconify-icon>
                                <span>{{ __('Bulk Actions') }} (<span x-text="selectedTerms.length"></span>)</span>
                                <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="absolute right-0 top-10 mt-2 w-48 rounded-md shadow bg-white dark:bg-gray-700 z-10 p-3">
                                <h6 class="mb-2 text-sm font-medium text-gray-700 dark:text-white">{{ __('Bulk Actions') }}</h6>
                                <ul class="space-y-2">
                                    <li class="cursor-pointer text-sm text-red-600 dark:text-red-400 hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded"
                                        @click="open = false; bulkDeleteModalOpen = true">
                                        <iconify-icon icon="lucide:trash" class="mr-1"></iconify-icon> {{ __('Delete Selected') }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        @include('backend.partials.search-form', [
                            'placeholder' => __("Search {$taxonomyModel->label}"),
                        ])
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="dataTable" class="table">
                        <thead class="table-thead">
                            <tr class="table-tr">
                                <th width="3%" class="table-thead-th">
                                    <div class="flex items-center">
                                        <input
                                            type="checkbox"
                                            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                            x-model="selectAll"
                                            @click="
                                                selectAll = !selectAll;
                                                selectedTerms = selectAll ?
                                                    [...document.querySelectorAll('.term-checkbox')].map(cb => cb.value) :
                                                    [];
                                            "
                                        >
                                    </div>
                                </th>
                                <th width="40%" class="table-thead-th">
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
                                @if($taxonomyModel->hierarchical)
                                <th width="15%" class="table-thead-th">{{ __('Parent') }}</th>
                                @endif
                                <th width="10%" class="table-thead-th">
                                    <div class="flex items-center">
                                        {{ __('Count') }}
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'post_count' ? '-post_count' : 'post_count']) }}" class="ml-1">
                                            @if(request()->sort === 'post_count')
                                                <iconify-icon icon="lucide:sort-asc" class="text-primary"></iconify-icon>
                                            @elseif(request()->sort === '-post_count')
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
                            @forelse ($terms as $termItem)
                                <tr class="{{ $loop->index + 1 != count($terms) ?  'table-tr' : '' }}">
                                    <td class="table-td table-td-checkbox">
                                        <input
                                            type="checkbox"
                                            class="term-checkbox form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                            value="{{ $termItem->id }}"
                                            x-model="selectedTerms"
                                        >
                                    </td>
                                    <td class="table-td">
                                        <div class="flex items-center">
                                            <div>
                                                @if($taxonomyModel->show_featured_image && $termItem->hasFeaturedImage())
                                                    <img src="{{ $termItem->getFeaturedImageUrl('thumb') }}" alt="{{ $termItem->name }}" class="w-10 rounded mr-3">
                                                @elseif($taxonomyModel->show_featured_image)
                                                    <div class="w-10 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center mr-3">
                                                        <iconify-icon icon="lucide:image" class="text-gray-400"></iconify-icon>
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
                                    <td class="table-td">
                                        @if($termItem->parent)
                                            @if (auth()->user()->can('term.edit'))
                                                <a href="{{ route('admin.terms.edit', ['taxonomy' => $taxonomy, 'term' => $termItem->parent->id]) }}" class="text-sm text-gray-600 dark:text-gray-300 hover:text-primary transition-colors">
                                                    {{ $termItem->parent->name }}
                                                </a>
                                            @else
                                                <span class="text-sm text-gray-600 dark:text-gray-300">{{ $termItem->parent->name }}</span>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400">{{ __('None') }}</span>
                                        @endif
                                    </td>
                                    @endif
                                    <td class="table-td">
                                        {{ $termItem->posts_count ?? $termItem->posts->count() }}
                                    </td>
                                    <td class="table-td text-center">
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
                                <tr class="table-tr">
                                    <td colspan="{{ $taxonomyModel->hierarchical ? '5' : '4' }}" class="table-td text-center">
                                        <span class="text-gray-500 dark:text-gray-300">{{ __('No') }} {{ strtolower($taxonomyModel->label) }} {{ __('found') }}</span>
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
            class="flex max-w-md flex-col gap-4 overflow-hidden rounded-md border border-outline border-gray-100 dark:border-gray-800 bg-white text-on-surface dark:border-outline-dark dark:bg-gray-700 dark:text-gray-300"
        >
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2 dark:border-gray-800">
                <div class="flex items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 p-1">
                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <h3 id="bulk-delete-modal-title" class="font-semibold tracking-wide text-gray-700 dark:text-white">
                    {{ __('Delete Selected') }} {{ $taxonomyModel->label }}
                </h3>
                <button
                    x-on:click="bulkDeleteModalOpen = false"
                    aria-label="close modal"
                    class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-md p-1 dark:hover:bg-gray-600 dark:hover:text-white"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="1.4" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-4 text-center">
                <p class="text-gray-500 dark:text-gray-300">
                    {{ __('Are you sure you want to delete the selected') }} {{ strtolower($taxonomyModel->label) }}?
                    {{ __('This action cannot be undone.') }}
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 p-4 dark:border-gray-800">
                <form id="bulk-delete-form" action="{{ route('admin.terms.bulk-delete', $taxonomy) }}" method="POST">
                    @method('DELETE')
                    @csrf

                    <template x-for="id in selectedTerms" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>

                    <button
                        type="button"
                        x-on:click="bulkDeleteModalOpen = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                    >
                        {{ __('No, Cancel') }}
                    </button>

                    <button
                        type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-300 dark:focus:ring-red-800"
                    >
                        {{ __('Yes, Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
