{!! Hook::applyFilters(PostFilterHook::INSIDE_POST_FORM_START, '') !!}

<input type="hidden" name="post_id" value="{{ $post->id ?? '' }}" data-post-id="{{ $post->id ?? '' }}">
<input type="hidden" name="post_type" value="{{ $postType }}" data-post-type="{{ $postType }}">

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Main Content Area -->
    <div class="lg:col-span-3 space-y-6">
        <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="p-5 space-y-4 sm:p-6">
                <!-- Title and Slug with Alpine.js -->
                <div x-data="slugGenerator('{{ old('title', $post->title ?? '') }}', '{{ old('slug', $post->slug ?? '') }}')">
                    <!-- Title -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label for="title"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Title') }}</label>
                            @can('ai_content.generate')
                            <div x-data="{ aiModalOpen: false }">
                                @include('backend.pages.posts.partials.ai-content-generator')
                            </div>
                            @endcan
                        </div>
                        <input type="text" name="title" id="title" required x-model="title" maxlength="255"
                            class="form-control">
                    </div>
                    {!! Hook::applyFilters(PostFilterHook::POST_FORM_AFTER_TITLE, '') !!}

                    <!-- Compact Slug UI -->
                    <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-300">
                        <span class="mr-1">{{ __('Permalink') }}:</span>
                        <span class="flex-1 truncate" x-show="!showSlugEdit">
                            @if(isset($frontendUrl) && $frontendUrl)
                                <a href="{{ $frontendUrl }}" target="_blank" class="text-primary hover:underline font-medium truncate">
                                    {{ $frontendUrl }}
                                </a>
                            @else
                                <span class="text-gray-400">{{ url('/') }}/</span><span
                                    class="font-medium text-primary" x-text="slug || '{{ __('auto-generated') }}'"></span>
                            @endif
                        </span>
                        <div class="flex-1" x-show="showSlugEdit">
                            <input type="text" name="slug" id="slug" x-model="slug" maxlength="200"
                                class="h-7 w-full rounded border border-gray-300 bg-transparent px-2 py-1 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                placeholder="{{ __('Leave empty to auto-generate') }}">
                        </div>
                        <div class="ml-2 flex space-x-1">
                            <!-- Edit/Save Button -->
                            <button type="button" @click="toggleSlugEdit()"
                                class="text-xs text-primary hover:underline">
                                <span x-show="!showSlugEdit">{{ __('Edit') }}</span>
                                <span x-show="showSlugEdit">{{ __('OK') }}</span>
                            </button>
                            <!-- Generate Button -->
                            <button type="button" @click="generateSlug()"
                                class="text-xs text-primary hover:underline ml-2">
                                {{ __('Generate') }}
                            </button>
                        </div>
                    </div>
                    {!! Hook::applyFilters(PostFilterHook::POST_FORM_AFTER_SLUG, '') !!}
                </div>

                @if ($postTypeModel->supports_editor)
                    <div class="space-y-1">
                        <label for="content"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Content') }}</label>
                            <textarea name="content" id="content" rows="10">{!! old('content', $post->content ?? '') !!}</textarea>
                    </div>
                @endif
                {!! Hook::applyFilters(PostFilterHook::POST_FORM_AFTER_CONTENT, '') !!}

                @if ($postTypeModel->supports_excerpt)
                    <div class="space-y-1">
                        <label for="excerpt"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Excerpt') }}</label>
                        <textarea name="excerpt" id="excerpt" rows="3"
                            class="form-control-textarea">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                            {{ __('A short summary of the content') }}.
                            {{ __('Leave empty to auto-generate from content') }}</p>
                    </div>
                @endif
                {!! Hook::applyFilters(PostFilterHook::POST_FORM_AFTER_EXCERPT, '') !!}
            </div>
        </div>

        <x-advanced-fields :post-meta="isset($post) ? $post->getAllMeta() : []" />
    </div>

    <!-- Sidebar Area -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Status and Visibility -->
        <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="px-4 py-3 sm:px-6 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-700 dark:text-white">{{ __('Status & Visibility') }}</h3>
            </div>
            <div class="p-3 space-y-2 sm:p-4">
                <!-- Status with Combobox -->
                @php
                    $statusOptions = Hook::applyFilters(PostFilterHook::POST_STATUS_OPTIONS, [
                        ['value' => 'draft', 'label' => __('Draft')],
                        ['value' => 'published', 'label' => __('Published')],
                        ['value' => 'pending', 'label' => __('Pending Review')],
                        ['value' => 'scheduled', 'label' => __('Scheduled')],
                        ['value' => 'private', 'label' => __('Private')],
                    ]);
                    $currentStatus = old('status', $post->status ?? App\Enums\PostStatus::DRAFT->value);
                @endphp

                <x-inputs.combobox
                    name="status"
                    label="{{ __('Status') }}"
                    :options="$statusOptions"
                    :selected="$currentStatus"
                    :multiple="false"
                    :searchable="false"
                    x-model="status"
                />

                {!! Hook::applyFilters(PostFilterHook::POST_FORM_AFTER_STATUS, '') !!}

                <!-- Publish Date (for scheduled posts) -->
                <div x-data="{
                    showSchedule: {{ isset($post) && (old('status', $post->status) === 'scheduled' || $post->published_at) ? 'true' : 'false' }},
                    status: '{{ old('status', $post->status ?? App\Enums\PostStatus::DRAFT->value) }}',
                    init() {
                        this.$watch('status', value => {
                            if (value === 'scheduled') {
                                this.showSchedule = true;
                            }
                        });
                    }
                }">
                    <div class="mb-2">
                        <input type="checkbox" id="schedule_post" name="schedule_post" x-model="showSchedule"
                            x-on:change="if(showSchedule && status !== 'scheduled') status = 'scheduled'; $dispatch('input', status)"
                            class="form-checkbox mr-2">
                        <label for="schedule_post"
                            class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Schedule this post') }}</label>
                    </div>
                    <div x-show="showSchedule" class="mt-2">
                        <x-inputs.datetime-picker id="published_at" name="published_at" :label="__('Publish Date')"
                            :value="old(
                                'published_at',
                                isset($post) && $post->published_at
                                    ? $post->published_at->format('Y-m-d H:i')
                                    : now()->addDay()->format('Y-m-d H:i'),
                            )"
                            :min-date="now()->format('Y-m-d')"
                            :help-text="__('Schedule when this post should be published')"
                        />
                    </div>
                </div>
                {!! Hook::applyFilters(PostFilterHook::POST_FORM_AFTER_PUBLISH_DATE, '') !!}
                <div class="mt-4">
                    <x-buttons.submit-buttons cancelUrl="{{ route('admin.posts.index', $postType) }}" />
                </div>
                {!! Hook::applyFilters(PostFilterHook::POST_FORM_AFTER_SUBMIT_BUTTONS, '') !!}
            </div>
        </div>

        @if ($postTypeModel->supports_thumbnail)
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 p-3 space-y-2 sm:p-4">
                <x-media-selector
                    name="featured_image"
                    label="{{ __('Featured Image') }}"
                    :multiple="false"
                    allowedTypes="images"
                    :existingMedia="isset($post) && $post->hasFeaturedImage() ? $post->getFeaturedImageUrl() : null"
                    :existingAltText="isset($post) ? $post->title : ''"
                    removeCheckboxName="remove_featured_image"
                    removeCheckboxLabel="{{ __('Remove featured image') }}"
                    :showPreview="true"
                    class="mt-1"
                />
            </div>
        @endif
        {!! Hook::applyFilters(PostFilterHook::POST_FORM_AFTER_FEATURED_IMAGE, '') !!}

        @if ($postTypeModel->hierarchical)
            <!-- Parent -->
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="px-4 py-3 sm:px-6 sm:py-3 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-medium text-gray-700 dark:text-white">{{ __('Parent') }}</h3>
                </div>
                <div class="p-3 space-y-2 sm:p-4">
                    @php
                        $parentOptions = [['value' => '', 'label' => __('None')]];
                        foreach ($parentPosts as $id => $title) {
                            $parentOptions[] = [
                                'value' => $id,
                                'label' => $title,
                            ];
                        }
                    @endphp

                    <x-inputs.combobox
                        name="parent_id"
                        :label="__('Parent ' . $postTypeModel->label_singular)"
                        :placeholder="__('Select Parent')"
                        :options="$parentOptions"
                        :selected="old('parent_id', $post->parent_id ?? '')"
                        :searchable="true"
                    />
                </div>
            </div>
        @endif
        {!! Hook::applyFilters(PostFilterHook::POST_FORM_AFTER_CONTENT_PARENT, '') !!}

        <!-- Taxonomies -->
        @if (!empty($taxonomies))
            @foreach ($taxonomies as $taxonomy)
                @include('backend.pages.posts.partials.post-taxonomy-chooser', [
                    'taxonomy' => $taxonomy,
                    'post_type' => $postType,
                ])
            @endforeach
        @endif
    </div>
</div>
