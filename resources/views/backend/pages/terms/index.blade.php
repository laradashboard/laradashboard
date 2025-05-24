@extends('backend.layouts.app')

@section('title')
    {{ $taxonomyModel->label }} - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div x-data="{ pageName: '{{ $taxonomyModel->label }}' }">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $taxonomyModel->label }}</h2>
            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('admin.dashboard') }}">
                            {{ __('Home') }}
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li class="text-sm text-gray-800 dark:text-white/90">{{ $taxonomyModel->label }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Two Column Layout: Form and List -->
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
                    @include('backend.layouts.partials.messages')
                    
                    @if($term)
                        <form action="{{ route('admin.terms.update', [$taxonomy, $term->id]) }}" method="POST">
                            @method('PUT')
                    @else
                        <form action="{{ route('admin.terms.store', $taxonomy) }}" method="POST">
                    @endif
                        @csrf
                        
                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Name') }}</label>
                            <input type="text" name="name" id="name" required value="{{ old('name', $term ? $term->name : '') }}" 
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>

                        <!-- Slug -->
                        <div class="mb-4">
                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Slug') }}</label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $term ? $term->slug : '') }}" 
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                placeholder="{{ __('Leave empty to auto-generate') }}">
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Description') }}</label>
                            <textarea name="description" id="description" rows="3" 
                                class="w-full rounded-lg border border-gray-300 bg-transparent p-4 text-sm text-gray-800 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('description', $term ? $term->description : '') }}</textarea>
                        </div>

                        @if($taxonomyModel->hierarchical)
                        <!-- Parent -->
                        <div class="mb-4">
                            <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Parent') }} {{ $taxonomyModel->label_singular }}</label>
                            <select name="parent_id" id="parent_id" 
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">{{ __('None') }}</option>
                                @foreach($parentTerms as $parentTerm)
                                    @if(!$term || $parentTerm->id !== $term->id)
                                        <option value="{{ $parentTerm->id }}" {{ old('parent_id', $term ? $term->parent_id : null) == $parentTerm->id ? 'selected' : '' }}>
                                            {{ $parentTerm->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Submit Button -->
                        <div class="flex justify-between gap-4">
                            <button type="submit" class="btn-primary">
                                {{ $term ? __('Update') : __('Add New') }} {{ $taxonomyModel->label_singular }}
                            </button>
                            @if($term)
                                <a href="{{ route('admin.terms.index', $taxonomy) }}" class="btn-default">
                                    {{ __('Cancel') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Terms List Column -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ $taxonomyModel->label }}</h3>
                    @include('backend.partials.search-form', [
                        'placeholder' => __('Search') . ' ' . strtolower($taxonomyModel->label),
                    ])
                </div>
                <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 overflow-x-auto">
                    <table id="dataTable" class="w-full dark:text-gray-400">
                        <thead class="bg-light text-capitalize">
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th width="30%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Name') }}</th>
                                <th width="25%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Slug') }}</th>
                                <th width="25%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Description') }}</th>
                                <th width="10%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Count') }}</th>
                                <th width="10%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-center px-5">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($terms as $termItem)
                                <tr class="{{ $loop->index + 1 != count($terms) ?  'border-b border-gray-100 dark:border-gray-800' : '' }}">
                                    <td class="px-5 py-4 sm:px-6">
                                        {{ $termItem->name }}
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <code class="text-sm px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded">{{ $termItem->slug }}</code>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        {{ Str::limit($termItem->description, 30) }}
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        {{ $termItem->posts->count() }}
                                    </td>
                                    <td class="px-5 py-4 sm:px-6 text-center flex items-center justify-center gap-1">
                                        @if (auth()->user()->can('term.edit'))
                                            <a data-tooltip-target="tooltip-edit-{{ $termItem->id }}" class="btn-default !p-3" href="{{ route('admin.terms.index', ['taxonomy' => $taxonomy, 'edit' => $termItem->id]) }}">
                                                <i class="bi bi-pencil text-sm"></i>
                                            </a>
                                            <div id="tooltip-edit-{{ $termItem->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                                {{ __('Edit') }}
                                                <div class="tooltip-arrow" data-popper-arrow></div>
                                            </div>
                                        @endif

                                        @if (auth()->user()->can('term.delete'))
                                            <a data-modal-target="delete-modal-{{ $termItem->id }}" data-modal-toggle="delete-modal-{{ $termItem->id }}" data-tooltip-target="tooltip-delete-{{ $termItem->id }}" class="btn-danger !p-3" href="javascript:void(0);">
                                                <i class="bi bi-trash text-sm"></i>
                                            </a>
                                            <div id="tooltip-delete-{{ $termItem->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                                {{ __('Delete') }}
                                                <div class="tooltip-arrow" data-popper-arrow></div>
                                            </div>

                                            <div id="delete-modal-{{ $termItem->id }}" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full z-99999">
                                                <div class="relative p-4 w-full max-w-md max-h-full">
                                                    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                                                        <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-modal-{{ $termItem->id }}">
                                                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                                            </svg>
                                                            <span class="sr-only">{{ __('Close modal') }}</span>
                                                        </button>
                                                        <div class="p-4 md:p-5 text-center">
                                                            <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                                            </svg>
                                                            <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">{{ __('Are you sure you want to delete this') }} {{ strtolower($taxonomyModel->label_singular) }}?</h3>
                                                            <form id="delete-form-{{ $termItem->id }}" action="{{ route('admin.terms.destroy', [$taxonomy, $termItem->id]) }}" method="POST">
                                                                @method('DELETE')
                                                                @csrf

                                                                <button type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
                                                                    {{ __('Yes, Confirm') }}
                                                                </button>
                                                                <button data-modal-hide="delete-modal-{{ $termItem->id }}" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">{{ __('No, cancel') }}</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td colspan="5" class="px-5 py-4 sm:px-6 text-center">
                                        <span class="text-gray-500 dark:text-gray-400">{{ __('No') }} {{ strtolower($taxonomyModel->label) }} {{ __('found') }}</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="my-4 px-4 sm:px-6">
                        {{ $terms->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-generate slug from name
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function() {
                if (!slugInput.value) {
                    // Create slug from name
                    slugInput.value = this.value
                        .toLowerCase()
                        .replace(/\s+/g, '-')           // Replace spaces with -
                        .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                        .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                        .replace(/^-+/, '')             // Trim - from start of text
                        .replace(/-+$/, '');            // Trim - from end of text
                }
            });
        }
    });
</script>
@endpush
@endsection
