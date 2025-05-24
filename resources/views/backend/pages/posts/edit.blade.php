@extends('backend.layouts.app')

@section('title')
    {{ __('Edit') }} {{ $postTypeModel->label_singular }} - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div x-data="{ pageName: '{{ __('Edit') }} {{ $postTypeModel->label_singular }}' }">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Edit') }} {{ $postTypeModel->label_singular }}</h2>
            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('admin.dashboard') }}">
                            {{ __('Home') }}
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li>
                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('admin.posts.index', $postType) }}">
                            {{ $postTypeModel->label }}
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li class="text-sm text-gray-800 dark:text-white/90">{{ __('Edit') }} {{ $postTypeModel->label_singular }}</li>
                </ol>
            </nav>
        </div>
    </div>

    @include('backend.layouts.partials.messages')

    <!-- Edit Post Form -->
    <form action="{{ route('admin.posts.update', [$postType, $post->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Content Area -->
            <div class="lg:col-span-3 space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white">{{ __('Content') }}</h3>
                    </div>
                    <div class="p-5 space-y-6 sm:p-6">
                        <!-- Title and Slug with Alpine.js -->
                        <div x-data="slugGenerator('{{ old('title', $post->title) }}', '{{ old('slug', $post->slug) }}')">
                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Title') }}</label>
                                <input type="text" name="title" id="title" required x-model="title" 
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            </div>

                            <!-- Compact Slug UI -->
                            <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <span class="mr-1">{{ __('Permalink:') }}</span>
                                <span class="flex-1 truncate" x-show="!showSlugEdit">
                                    <span class="text-gray-400">{{ url('/') }}/</span><span class="font-medium text-primary" x-text="slug"></span>
                                </span>
                                <div class="flex-1" x-show="showSlugEdit">
                                    <input type="text" name="slug" id="slug" x-model="slug" 
                                        class="h-7 w-full rounded border border-gray-300 bg-transparent px-2 py-1 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <div class="ml-2 flex space-x-1">
                                    <!-- Edit/Save Button -->
                                    <button type="button" @click="toggleSlugEdit()" class="text-xs text-primary hover:underline">
                                        <span x-show="!showSlugEdit">{{ __('Edit') }}</span>
                                        <span x-show="showSlugEdit">{{ __('OK') }}</span>
                                    </button>
                                    <!-- Generate Button -->
                                    <button type="button" @click="generateSlug()" class="text-xs text-primary hover:underline ml-2">
                                        {{ __('Generate') }}
                                    </button>
                                </div>
                            </div>

                            @if($postTypeModel->supports_excerpt)
                            <div class="mt-2">
                                <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Excerpt') }}</label>
                                <textarea name="excerpt" id="excerpt" rows="3" 
                                    class="w-full rounded-lg border border-gray-300 bg-transparent p-4 text-sm text-gray-800 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('excerpt', $post->excerpt) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('A short summary of the content') }}</p>
                            </div>
                            @endif

                            @if($postTypeModel->supports_editor)
                            <div class="mt-2">
                                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Content') }}</label>
                                <textarea name="content" id="content" rows="10">{!! old('content', $post->content) !!}</textarea>
                                <div id="quill-content"></div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($postTypeModel->supports_thumbnail)
                <!-- Featured Image -->
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white">{{ __('Featured Image') }}</h3>
                    </div>
                    <div class="p-5 space-y-6 sm:p-6">
                        <div>
                            @if($post->featured_image)
                                <div class="mb-4">
                                    <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="max-h-48 rounded-lg">
                                    <div class="mt-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="remove_featured_image" id="remove_featured_image" class="mr-2">
                                            <span class="text-sm text-gray-700 dark:text-gray-400">{{ __('Remove featured image') }}</span>
                                        </label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="featured_image" id="featured_image" accept="image/*" 
                                class="focus:border-ring-brand-300 cursor-pointer focus:file:ring-brand-300 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:px-4 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 px-4">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Select an image to represent this post') }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar Area -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Status and Visibility -->
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white">{{ __('Status & Visibility') }}</h3>
                    </div>
                    <div class="p-5 space-y-4 sm:p-6">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Status') }}</label>
                            <select name="status" id="status" 
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                <option value="publish" {{ old('status', $post->status) === 'publish' ? 'selected' : '' }}>{{ __('Published') }}</option>
                                <option value="pending" {{ old('status', $post->status) === 'pending' ? 'selected' : '' }}>{{ __('Pending Review') }}</option>
                                <option value="future" {{ old('status', $post->status) === 'future' ? 'selected' : '' }}>{{ __('Scheduled') }}</option>
                                <option value="private" {{ old('status', $post->status) === 'private' ? 'selected' : '' }}>{{ __('Private') }}</option>
                            </select>
                        </div>

                        <!-- Publish Date (for scheduled posts) -->
                        <div x-data="{ showSchedule: {{ old('status', $post->status) === 'future' || $post->published_at ? 'true' : 'false' }} }">
                            <div class="mb-2">
                                <input type="checkbox" id="schedule_post" name="schedule_post" x-model="showSchedule" class="mr-2">
                                <label for="schedule_post" class="text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Schedule this post') }}</label>
                            </div>
                            <div x-show="showSchedule" class="mt-2">
                                <label for="published_at" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Publish Date') }}</label>
                                <input type="datetime-local" name="published_at" id="published_at"
                                    value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            </div>
                        </div>
                    </div>
                </div>

                @if($postTypeModel->hierarchical)
                <!-- Parent -->
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white">{{ __('Parent') }}</h3>
                    </div>
                    <div class="p-5 space-y-4 sm:p-6">
                        <div>
                            <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Parent') }} {{ $postTypeModel->label_singular }}</label>
                            <select name="parent_id" id="parent_id" 
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">{{ __('None') }}</option>
                                @foreach($parentPosts as $id => $title)
                                    <option value="{{ $id }}" {{ old('parent_id', $post->parent_id) == $id ? 'selected' : '' }}>{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Taxonomies -->
                @if(!empty($taxonomies))
                    @foreach($taxonomies as $taxonomy)
                        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                            <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
                                <h3 class="text-base font-medium text-gray-800 dark:text-white">{{ $taxonomy->label }}</h3>
                            </div>
                            <div class="p-5 space-y-4 sm:p-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Select') }} {{ strtolower($taxonomy->label) }}</label>
                                    <div class="mt-2 max-h-40 overflow-y-auto">
                                        @php
                                            $terms = App\Models\Term::where('taxonomy', $taxonomy->name)->orderBy('name', 'asc')->get();
                                        @endphp
                                        
                                        @if($terms->count() > 0)
                                            @foreach($terms as $term)
                                                <div class="flex items-start mb-2">
                                                    <input type="checkbox" name="taxonomy_{{ $taxonomy->name }}[]" id="term_{{ $term->id }}" value="{{ $term->id }}" 
                                                        class="mt-1 h-4 w-4 text-brand-500 border-gray-300 rounded focus:ring-brand-400 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-brand-500" 
                                                        {{ in_array($term->id, old('taxonomy_' . $taxonomy->name, $selectedTerms[$taxonomy->name] ?? [])) ? 'checked' : '' }}>
                                                    <label for="term_{{ $term->id }}" class="ml-2 block text-sm text-gray-700 dark:text-gray-400">
                                                        {{ $term->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No') }} {{ strtolower($taxonomy->label) }} {{ __('found.') }}</p>
                                            <a href="{{ route('admin.terms.index', $taxonomy->name) }}" class="text-sm text-primary hover:underline mt-2 inline-block">{{ __('Add a new') }} {{ strtolower($taxonomy->label_singular) }}</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                <!-- Action Buttons -->
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="p-5 space-y-4 sm:p-6">
                        <div class="flex justify-between gap-4">
                            <button type="submit" class="btn-primary">{{ __('Update') }}</button>
                            <a href="{{ route('admin.posts.index', $postType) }}" class="btn-default">{{ __('Cancel') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/post-content.js') }}"></script>
@include('backend.partials.quill-scripts', ['editorId' => 'content'])
@endpush
