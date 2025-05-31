{!! ld_apply_filters('inside_post_form_start', '') !!}

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Main Content Area -->
    <div class="lg:col-span-3 space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="p-5 space-y-4 sm:p-6">
                <!-- Title and Slug with Alpine.js -->
                <div x-data="slugGenerator('{{ old('title', $post->title ?? '') }}', '{{ old('slug', $post->slug ?? '') }}')">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Title') }}</label>
                        <input type="text" name="title" id="title" required x-model="title" maxlength="255"
                            class="form-control">
                    </div>
                    {!! ld_apply_filters('post_form_after_title', '') !!}

                    <!-- Compact Slug UI -->
                    <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                        <span class="mr-1">{{ __('Permalink:') }}</span>
                        <span class="flex-1 truncate" x-show="!showSlugEdit">
                            <span class="text-gray-400">{{ url('/') }}/</span><span class="font-medium text-primary" x-text="slug || '{{ __('auto-generated') }}'"></span>
                        </span>
                        <div class="flex-1" x-show="showSlugEdit">
                            <input type="text" name="slug" id="slug" x-model="slug" maxlength="200"
                                class="h-7 w-full rounded border border-gray-300 bg-transparent px-2 py-1 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                placeholder="{{ __('Leave empty to auto-generate') }}">
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
                    {!! ld_apply_filters('post_form_after_slug', '') !!}
                </div>

                @if($postTypeModel->supports_excerpt)
                <div class="mt-1">
                    <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Excerpt') }}</label>
                    <textarea name="excerpt" id="excerpt" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-transparent p-4 text-sm text-gray-800 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('A short summary of the content') }}</p>
                </div>
                @endif
                {!! ld_apply_filters('post_form_after_excerpt', '') !!}

                @if($postTypeModel->supports_editor)
                <div class="mt-1">
                    <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Content') }}</label>
                    <textarea name="content" id="content" rows="10">{!! old('content', $post->content ?? '') !!}</textarea>
                </div>
                @endif
                {!! ld_apply_filters('post_form_after_content', '') !!}

                @if($postTypeModel->supports_thumbnail)
                <div class="mt-1">
                    <label for="featured_image" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Featured Image') }}</label>
                    @if(isset($post) && $post->featured_image)
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
                @endif
                {!! ld_apply_filters('post_form_after_featured_image', '') !!}
            </div>
        </div>

        <x-advanced-fields :post-meta="isset($post) ? $post->getAllMeta() : []" />
    </div>

    <!-- Sidebar Area -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Status and Visibility -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white">{{ __('Status & Visibility') }}</h3>
            </div>
            <div class="p-3 space-y-2 sm:p-4">
                <!-- Status with Combobox -->
                @php
                    $statusOptions = ld_apply_filters('post_status_options', [
                        ['value' => 'draft', 'label' => __('Draft')],
                        ['value' => 'publish', 'label' => __('Published')],
                        ['value' => 'pending', 'label' => __('Pending Review')],
                        ['value' => 'future', 'label' => __('Scheduled')],
                        ['value' => 'private', 'label' => __('Private')],
                    ]);
                    $currentStatus = old('status', $post->status ?? 'draft');
                @endphp

                <x-inputs.combobox
                    name="status"
                    label="{{ __('Status') }}"
                    :options="$statusOptions"
                    :selected="$currentStatus"
                    :multiple="false"
                    :searchable="false" />

                {!! ld_apply_filters('post_form_after_status', '') !!}

                <!-- Publish Date (for scheduled posts) -->
                <div x-data="{ showSchedule: {{ isset($post) && (old('status', $post->status) === 'future' || $post->published_at) ? 'true' : 'false' }} }">
                    <div class="mb-2">
                        <input type="checkbox" id="schedule_post" name="schedule_post" x-model="showSchedule" class="mr-2">
                        <label for="schedule_post" class="text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Schedule this post') }}</label>
                    </div>
                    <div x-show="showSchedule" class="mt-2">
                        <label for="published_at" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Publish Date') }}</label>
                        <input type="datetime-local" name="published_at" id="published_at"
                            value="{{ old('published_at', isset($post) && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
                            class="form-control">
                    </div>
                </div>
                {!! ld_apply_filters('post_form_after_publish_date', '') !!}

                <div class="flex justify-between gap-4 mt-3">
                    <button type="submit" class="btn-primary">{{ isset($post) ? __('Update') : __('Save') }}</button>
                    <a href="{{ route('admin.posts.index', $postType) }}" class="btn-default">{{ __('Cancel') }}</a>
                </div>
                {!! ld_apply_filters('post_form_after_submit_buttons', '') !!}
            </div>
        </div>

        @if($postTypeModel->hierarchical)
        <!-- Parent -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white">{{ __('Parent') }}</h3>
            </div>
            <div class="p-3 space-y-2 sm:p-4">
                @php
                    $parentOptions = [['value' => '', 'label' => __('None')]];
                    foreach($parentPosts as $id => $title) {
                        $parentOptions[] = [
                            'value' => $id,
                            'label' => $title
                        ];
                    }
                @endphp
                
                <x-inputs.combobox 
                    name="parent_id"
                    :label="__('Parent') . ' ' . $postTypeModel->label_singular"
                    :placeholder="__('Select Parent')"
                    :options="$parentOptions"
                    :selected="old('parent_id', $post->parent_id ?? '')"
                    :searchable="false"
                />
            </div>
        </div>
        @endif
        {!! ld_apply_filters('post_form_after_content_parent', '') !!}

        <!-- Taxonomies -->
        @if(!empty($taxonomies))
            @foreach($taxonomies as $taxonomy)
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white">{{ $taxonomy->label }}</h3>
                    </div>
                    <div class="p-3 space-y-2 sm:p-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Select') }} {{ strtolower($taxonomy->label) }}</label>
                            <div class="mt-2 max-h-60 overflow-y-auto">
                                @php
                                    if ($taxonomy->hierarchical) {
                                        // For hierarchical taxonomies, get parent terms first (terms with no parent)
                                        $parentTerms = App\Models\Term::where('taxonomy', $taxonomy->name)
                                            ->whereNull('parent_id')
                                            ->orderBy('name', 'asc')
                                            ->get();
                                        
                                        // Check if we have any terms
                                        $hasTerms = $parentTerms->count() > 0 || 
                                            App\Models\Term::where('taxonomy', $taxonomy->name)->count() > 0;
                                    } else {
                                        // For flat taxonomies, get all terms
                                        $terms = App\Models\Term::where('taxonomy', $taxonomy->name)
                                            ->orderBy('name', 'asc')
                                            ->get();
                                        $hasTerms = $terms->count() > 0;
                                    }
                                @endphp
                                
                                @if($hasTerms)
                                    @if($taxonomy->hierarchical)
                                        <div class="space-y-1 px-1">
                                            @foreach($parentTerms as $parentTerm)
                                                @include('backend.pages.posts.partials.hierarchical-terms', [
                                                    'term' => $parentTerm,
                                                    'taxonomy' => $taxonomy,
                                                    'level' => 0,
                                                    'selectedTerms' => $selectedTerms[$taxonomy->name] ?? []
                                                ])
                                            @endforeach
                                        </div>
                                    @else
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
                                    @endif
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No') }} {{ strtolower($taxonomy->label) }} {{ __('found.') }}</p>
                                    <a href="{{ route('admin.terms.index', $taxonomy->name) }}" class="text-sm text-primary hover:underline mt-2 inline-block">{{ __('Add a new') }} {{ strtolower($taxonomy->label_singular) }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                {!! ld_apply_filters('post_form_after_taxonomy_' . $taxonomy->name, '') !!}
            @endforeach
        @endif
    </div>
</div>

{!! ld_apply_filters('inside_post_form_end', '') !!}
