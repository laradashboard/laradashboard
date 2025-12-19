<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(PostFilterHook::POSTS_SHOW_AFTER_BREADCRUMBS, '', $postType) !!}

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-card.card bodyClass="!p-5">
                <x-slot:header>{{ __('Content') }}</x-slot:header>

                @if($post->content)
                    <div class="prose max-w-none dark:prose-invert prose-headings:font-medium prose-headings:text-gray-700 dark:prose-headings:text-white/90 prose-p:text-gray-700 dark:prose-p:text-gray-300 lb-content-preview">
                        {!! $post->renderContent() !!}
                    </div>
                    @if($post->design_json)
                        {{-- LaraBuilder CSS styles for rendered content --}}
                        <style>
                            /* Base block styles */
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

                            /* Button block */
                            .lb-content-preview .lb-button { margin-bottom: 16px; }
                            .lb-content-preview .lb-button a { text-decoration: none; transition: opacity 0.2s ease; }
                            .lb-content-preview .lb-button a:hover { opacity: 0.9; }

                            /* Columns block */
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

                            /* Table of Contents block */
                            .lb-content-preview .lb-toc { margin-bottom: 16px; }
                            .lb-content-preview .lb-toc-list { margin: 0; padding: 0; }
                            .lb-content-preview .lb-toc-list li { margin-bottom: 6px; line-height: 1.6; }
                            .lb-content-preview .lb-toc-list a { text-decoration: none; transition: opacity 0.2s; }
                            .lb-content-preview .lb-toc-list a:hover { opacity: 0.8; text-decoration: underline; }

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
            </x-card.card>
        </div>

        {{-- Sidebar (Right - 1 column) --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Featured image --}}
            @if($post->hasFeaturedImage())
                <x-card.card bodyClass="!p-4 !space-y-0">
                    <x-slot:header>{{ __('Featured Image') }}</x-slot:header>

                    <div class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800">
                        <img src="{{ $post->getFeaturedImageUrl() }}" alt="{{ $post->title }}" class="w-full h-auto object-cover max-h-96">
                    </div>
                </x-card.card>
            @endif

            {{-- Excerpt Card --}}
            @if($post->excerpt)
                <x-card.card bodyClass="!p-4 !space-y-0">
                    <x-slot:header>{{ __('Excerpt') }}</x-slot:header>

                    <p class="text-gray-600 dark:text-gray-300 italic leading-relaxed text-sm">{{ $post->excerpt }}</p>
                </x-card.card>
            @endif

            {{-- Status & Info Card --}}
            <x-card.card bodyClass="!space-y-4 pt-2">
                <x-slot:header>{{ __('Status & Info') }}</x-slot:header>
                {{-- Slug --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Slug') }}</label>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 break-words">{{ $post->slug }}</p>
                </div>

                {{-- Status --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</label>
                    <div class="mt-1">
                        <span class="badge {{ get_post_status_class($post->status) }}">
                            {{ ucfirst($post->status) }}
                        </span>
                    </div>
                </div>

                {{-- Published At --}}
                @if($post->published_at)
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Published') }}</label>
                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                            {{ $post->published_at->format('M d, Y h:i A') }}
                        </p>
                    </div>
                @endif

                {{-- Author --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Author') }}</label>
                    <div class="mt-1 flex items-center gap-2">
                        @if(!empty($post->user->avatar_url))
                            <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->full_name }}" class="w-6 h-6 rounded-full">
                        @else
                            <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <iconify-icon icon="lucide:user" class="text-xs text-gray-500 dark:text-gray-400"></iconify-icon>
                            </div>
                        @endif
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $post->user->full_name }}</span>
                    </div>
                </div>

                {{-- Created At --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created') }}</label>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                        {{ $post->created_at->format('M d, Y h:i A') }}
                    </p>
                </div>

                {{-- Updated At --}}
                @if($post->created_at != $post->updated_at)
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Last Updated') }}</label>
                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                            {{ $post->updated_at->format('M d, Y h:i A') }}
                        </p>
                    </div>
                @endif
            </x-card.card>

            {{-- Taxonomies Card --}}
            @if($post->terms->count() > 0)
                <x-card.card bodyClass="!p-4 !space-y-4">
                    <x-slot:header>{{ __('Taxonomies') }}</x-slot:header>

                    @php
                        $groupedTerms = $post->terms->groupBy('taxonomy');
                    @endphp

                    @foreach($groupedTerms as $taxonomy => $terms)
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ ucfirst($taxonomy) }}</label>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach($terms as $term)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $term->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </x-card.card>
            @endif

            {{-- Post Meta Card (if any custom meta exists) --}}
            @if($post->postMeta && $post->postMeta->count() > 0)
                <x-card.card bodyClass="!p-4 !space-y-3">
                    <x-slot:header>{{ __('Custom Fields') }}</x-slot:header>

                    @foreach($post->postMeta as $meta)
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $meta->meta_key }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 break-words">{{ $meta->meta_value }}</p>
                        </div>
                    @endforeach
                </x-card.card>
            @endif
        </div>
    </div>

    {!! Hook::applyFilters(PostFilterHook::POSTS_SHOW_AFTER_CONTENT, '', $postType) !!}
</x-layouts.backend-layout>
