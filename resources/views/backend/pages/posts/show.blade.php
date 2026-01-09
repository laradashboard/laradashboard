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

                            /* Markdown block */
                            .lb-content-preview .lb-markdown { margin-bottom: 16px; }
                            .lb-content-preview .markdown-source { margin-bottom: 12px; padding: 8px 12px; background: #f9fafb; border-radius: 6px; font-size: 12px; color: #6b7280; }
                            .lb-content-preview .markdown-source a { color: #6366f1; text-decoration: underline; }
                            .lb-content-preview .markdown-body { line-height: 1.6; }
                            .lb-content-preview .markdown-body h1 { font-size: 2em; font-weight: 700; margin: 1em 0 0.5em; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.3em; }
                            .lb-content-preview .markdown-body h2 { font-size: 1.5em; font-weight: 600; margin: 1em 0 0.5em; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.3em; }
                            .lb-content-preview .markdown-body h3 { font-size: 1.25em; font-weight: 600; margin: 1em 0 0.5em; }
                            .lb-content-preview .markdown-body h4 { font-size: 1em; font-weight: 600; margin: 1em 0 0.5em; }
                            .lb-content-preview .markdown-body p { margin: 0 0 1em; }
                            .lb-content-preview .markdown-body ul, .lb-content-preview .markdown-body ol { margin: 0 0 1em; padding-left: 2em; }
                            .lb-content-preview .markdown-body li { margin: 0.25em 0; }
                            .lb-content-preview .markdown-body code { background: #f3f4f6; padding: 0.2em 0.4em; border-radius: 4px; font-size: 0.875em; font-family: ui-monospace, monospace; }
                            .lb-content-preview .markdown-body pre { background: #1f2937; color: #e5e7eb; padding: 1em; border-radius: 8px; overflow-x: auto; margin: 0 0 1em; }
                            .lb-content-preview .markdown-body pre code { background: transparent; padding: 0; color: inherit; }
                            .lb-content-preview .markdown-body blockquote { border-left: 4px solid #6366f1; padding-left: 1em; margin: 0 0 1em; color: #6b7280; font-style: italic; }
                            .lb-content-preview .markdown-body a { color: #6366f1; text-decoration: underline; }
                            .lb-content-preview .markdown-body a:hover { color: #4f46e5; }
                            .lb-content-preview .markdown-body table { border-collapse: collapse; width: 100%; margin: 0 0 1em; }
                            .lb-content-preview .markdown-body th, .lb-content-preview .markdown-body td { border: 1px solid #e5e7eb; padding: 0.5em 1em; text-align: left; }
                            .lb-content-preview .markdown-body th { background: #f9fafb; font-weight: 600; }
                            .lb-content-preview .markdown-body hr { border: none; border-top: 1px solid #e5e7eb; margin: 2em 0; }
                            .lb-content-preview .markdown-body img { max-width: 100%; height: auto; border-radius: 8px; }
                            .lb-content-preview .markdown-body input[type="checkbox"] { margin-right: 0.5em; }

                            /* Markdown empty state */
                            .lb-content-preview .markdown-empty { padding: 24px; padding-top: 10px; text-align: center; color: #9ca3af; background: #f9fafb; border: 1px dashed #e5e7eb; border-radius: 8px; }
                            .lb-content-preview .markdown-error { margin-bottom: 16px; }

                            /* Syntax highlighting overrides for Prism */
                            .lb-content-preview .markdown-body pre { background: #1e1e1e; padding: 0; }
                            .lb-content-preview .markdown-body pre code { display: block; padding: 1em; background: transparent; font-size: 0.875em; line-height: 1.5; }
                            .lb-content-preview .markdown-body code:not([class*="language-"]) { background: #f3f4f6; color: #e83e8c; }

                            /* Responsive */
                            @media (max-width: 768px) {
                                .lb-content-preview .lb-columns { flex-direction: column; }
                                .lb-content-preview .lb-column { flex: none !important; width: 100% !important; }
                            }
                        </style>

                        {{-- Prism.js for syntax highlighting in markdown blocks --}}
                        @if(str_contains($post->content ?? '', 'markdown'))
                            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" />
                            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.css" />
                            <style>
                                /* Copy button styling */
                                .code-toolbar .toolbar { opacity: 1; }
                                .code-toolbar .toolbar-item button {
                                    background: #3b3b3b !important;
                                    color: #e5e7eb !important;
                                    border-radius: 4px !important;
                                    padding: 4px 10px !important;
                                    font-size: 12px !important;
                                    box-shadow: none !important;
                                    border: 1px solid #4b4b4b !important;
                                    transition: all 0.2s ease !important;
                                }
                                .code-toolbar .toolbar-item button:hover {
                                    background: #4b4b4b !important;
                                    color: #fff !important;
                                }
                                .code-toolbar .toolbar-item button:focus {
                                    outline: none !important;
                                }
                            </style>
                            <script>
                                (function() {
                                    // Load Prism core first, then languages sequentially
                                    function loadScript(src) {
                                        return new Promise(function(resolve, reject) {
                                            var script = document.createElement('script');
                                            script.src = src;
                                            script.onload = resolve;
                                            script.onerror = reject;
                                            document.head.appendChild(script);
                                        });
                                    }

                                    var baseUrl = 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/';
                                    // Order matters: markup -> markup-templating -> php (dependency chain)
                                    var languages = ['markup', 'css', 'clike', 'javascript', 'markup-templating', 'php', 'typescript', 'jsx', 'tsx', 'scss', 'bash', 'json', 'yaml', 'sql', 'python'];

                                    // Load core first
                                    loadScript(baseUrl + 'prism.min.js').then(function() {
                                        // Load languages sequentially to handle dependencies
                                        return languages.reduce(function(promise, lang) {
                                            return promise.then(function() {
                                                return loadScript(baseUrl + 'components/prism-' + lang + '.min.js').catch(function() {
                                                    console.warn('Failed to load Prism language: ' + lang);
                                                });
                                            });
                                        }, Promise.resolve());
                                    }).then(function() {
                                        // Load toolbar plugin (required for copy button)
                                        return loadScript(baseUrl + 'plugins/toolbar/prism-toolbar.min.js');
                                    }).then(function() {
                                        // Load copy-to-clipboard plugin
                                        return loadScript(baseUrl + 'plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js');
                                    }).then(function() {
                                        // Highlight all code blocks
                                        if (window.Prism) {
                                            Prism.highlightAll();
                                        }
                                    });
                                })();
                            </script>
                        @endif
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
