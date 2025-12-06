<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(PostFilterHook::POSTS_SHOW_AFTER_BREADCRUMBS, '', $postType) !!}

    <div class="space-y-6">
        <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-700 dark:text-white/90">{{ __('Post Details') }}</h3>
                <div class="flex gap-2">
                    @if (auth()->user()->can('post.edit'))
                        <a href="{{ route('admin.posts.edit', [$postType, $post->id]) }}" class="btn-primary">
                            <iconify-icon icon="lucide:pencil" class="mr-2"></iconify-icon>
                            {{ __('Edit') }}
                        </a>
                    @endif
                    <a href="{{ route('admin.posts.index', $postType) }}" class="btn-default">
                        <iconify-icon icon="lucide:arrow-left" class="mr-2"></iconify-icon>
                        {{ __('Back') }}
                    </a>
                </div>
            </div>

            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <!-- Meta Information -->
                <div class="mb-6 flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <div class="flex items-center">
                        <iconify-icon icon="lucide:user" class="mr-1"></iconify-icon>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Author:') }} {{ $post->user->full_name }}</span>
                    </div>
                    <div class="flex items-center">
                        <iconify-icon icon="lucide:calendar" class="mr-1"></iconify-icon>
                        {{ __('Created:') }} {{ $post->created_at->format('M d, Y h:i A') }}
                    </div>
                    @if($post->created_at != $post->updated_at)
                        <div class="flex items-center">
                            <iconify-icon icon="lucide:clock" class="mr-1"></iconify-icon>
                            {{ __('Updated:') }} {{ $post->updated_at->format('M d, Y h:i A') }}
                        </div>
                    @endif
                    <div class="flex items-center">
                        <iconify-icon icon="lucide:tag" class="mr-1"></iconify-icon>
                        {{ __('Status:') }}
                        <span class="ml-1 {{ get_post_status_class($post->status) }}">{{ ucfirst($post->status) }}</span>
                    </div>
                </div>

                <!-- Featured Image -->
                @if($post->featured_image)
                    <div class="mb-6">
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="max-h-64 rounded-md">
                    </div>
                @endif

                <!-- Excerpt -->
                @if($post->excerpt)
                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-700 dark:text-white/90 mb-2">{{ __('Excerpt') }}</h4>
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-md text-gray-700 dark:text-gray-300">
                            {{ $post->excerpt }}
                        </div>
                    </div>
                @endif

                <!-- Content -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-700 dark:text-white/90 mb-2">{{ __('Content') }}</h4>
                    @if($post->content)
                        @php
                            // Process dynamic blocks (like CRM Contact) through server-side rendering
                            $processedContent = app(\App\Services\Builder\BlockRenderer::class)->processContent($post->content, 'page');
                        @endphp
                        <div class="prose max-w-none dark:prose-invert prose-headings:font-medium prose-headings:text-gray-700 dark:prose-headings:text-white/90 prose-p:text-gray-700 dark:prose-p:text-gray-300 lb-content-preview">
                            {!! $processedContent !!}
                        </div>
                        @if($post->design_json)
                            {{-- LaraBuilder CSS styles for rendered content --}}
                            <style>
                                /* Base block styles - layout styles are now applied directly to each block's main element */
                                .lb-content-preview .lb-block { display: block; margin-bottom: 16px; }
                                .lb-content-preview .lb-content { max-width: 100%; }

                                /* Text blocks */
                                .lb-content-preview .lb-heading { margin-bottom: 16px; }
                                .lb-content-preview .lb-text { margin-bottom: 16px; }
                                .lb-content-preview .lb-text-editor { margin-bottom: 16px; }
                                .lb-content-preview .lb-list { margin-bottom: 16px; }

                                /* Image block */
                                .lb-content-preview .lb-image { margin-bottom: 16px; }
                                .lb-content-preview .lb-image img { max-width: 100%; height: auto; }

                                /* Button block - wrapper has layout styles, inner button has specific styles */
                                .lb-content-preview .lb-button { margin-bottom: 16px; }
                                .lb-content-preview .lb-button a { text-decoration: none; transition: opacity 0.2s ease; }
                                .lb-content-preview .lb-button a:hover { opacity: 0.9; }

                                /* Columns block - layout styles on main element */
                                .lb-content-preview .lb-columns { margin-bottom: 16px; }
                                .lb-content-preview .lb-column { flex: 1; min-width: 0; }

                                /* Divider & Spacer */
                                .lb-content-preview .lb-divider { border: none; }
                                .lb-content-preview .lb-spacer { display: block; }

                                /* Quote block */
                                .lb-content-preview .lb-quote { margin-bottom: 16px; }

                                /* Video block */
                                .lb-content-preview .lb-video { margin-bottom: 16px; }
                                .lb-content-preview .lb-video-container { cursor: pointer; }
                                .lb-content-preview .lb-video-play-btn:hover { background: rgba(0,0,0,0.9) !important; }

                                /* Social block */
                                .lb-content-preview .lb-social { margin-bottom: 16px; }

                                /* Table block */
                                .lb-content-preview .lb-table { margin-bottom: 16px; }
                                .lb-content-preview .lb-table-inner { width: 100%; border-collapse: collapse; }

                                /* Footer block */
                                .lb-content-preview .lb-footer { margin-bottom: 16px; }

                                /* Countdown block */
                                .lb-content-preview .lb-countdown { margin-bottom: 16px; }

                                /* Accordion block */
                                .lb-content-preview .lb-accordion { margin-bottom: 16px; }

                                /* Section block */
                                .lb-content-preview .lb-section { margin-bottom: 16px; }

                                /* Code block */
                                .lb-content-preview .lb-code { margin-bottom: 16px; }

                                /* HTML block */
                                .lb-content-preview .lb-html { margin-bottom: 16px; }

                                /* Responsive */
                                @media (max-width: 768px) {
                                    .lb-content-preview .lb-columns { flex-direction: column; }
                                    .lb-content-preview .lb-column { flex: none !important; width: 100% !important; }
                                }
                            </style>
                        @endif
                    @else
                        <p class="text-gray-400 dark:text-gray-500 italic">{{ __('No content available.') }}</p>
                    @endif
                </div>

                <!-- Taxonomies -->
                @if($post->terms->count() > 0)
                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-700 dark:text-white/90 mb-2">{{ __('Taxonomies') }}</h4>
                        <div class="space-y-3">
                            @php
                                $groupedTerms = $post->terms->groupBy('taxonomy');
                            @endphp

                            @foreach($groupedTerms as $taxonomy => $terms)
                                <div>
                                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ ucfirst($taxonomy) }}</h5>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($terms as $term)
                                            <span class="badge">{{ $term->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {!! Hook::applyFilters(PostFilterHook::POSTS_SHOW_AFTER_CONTENT, '', $postType) !!}
</x-layouts.backend-layout>
