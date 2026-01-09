<x-dashboard-collapsible-card
    :title="__('Recent Posts')"
    icon="heroicons:document-text"
    icon-bg="bg-amber-100 dark:bg-amber-900/30"
    icon-color="text-amber-600 dark:text-amber-400"
    storage-key="dashboard_recent_posts"
>
    <x-slot:headerActions>
        @can('post.view')
        <a href="{{ route('admin.posts.index', 'post') }}"
           class="text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
            {{ __('View all') }}
        </a>
        @endcan
    </x-slot:headerActions>

    <div wire:poll.60s>
        @if($posts->isEmpty())
            <div class="py-4 text-center">
                <iconify-icon icon="heroicons:document" class="text-3xl text-gray-300 dark:text-gray-600 mb-2"></iconify-icon>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No posts yet') }}</p>
                @can('post.create')
                <a href="{{ route('admin.posts.create', 'post') }}"
                   class="btn-link mt-2 text-xs">
                    <iconify-icon icon="heroicons:plus" class="text-sm"></iconify-icon>
                    {{ __('Create your first post') }}
                </a>
                @endcan
            </div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-700 overflow-y-auto -mx-4">
                @foreach($posts as $post)
                    <a href="{{ route('admin.posts.edit', ['postType' => $post->post_type, 'post' => $post->id]) }}"
                       class="flex items-start gap-2 py-3 px-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                        <div class="flex-shrink-0">
                            @if($post->hasFeaturedImage())
                                <img src="{{ $post->getFeaturedImageUrl('thumb') }}"
                                     alt="{{ $post->title }}"
                                     class="w-8 h-8 rounded object-cover">
                            @else
                                <div class="flex h-8 w-8 items-center justify-center rounded bg-gray-100 dark:bg-gray-700">
                                    <iconify-icon icon="heroicons:document-text" class="text-sm text-gray-400 dark:text-gray-500"></iconify-icon>
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate group-hover:text-primary transition-colors">
                                {{ $post->title }}
                            </p>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $post->created_at->diffForHumans() }}
                                </span>
                                @if($post->user)
                                    <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ $post->user->full_name }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Status Badge --}}
                        <div class="flex-shrink-0">
                            @php
                                $badgeClass = match($post->status) {
                                    'published' => 'badge-success',
                                    'draft' => 'badge-warning',
                                    'pending' => 'badge-info',
                                    'scheduled' => 'badge-primary',
                                    'private' => 'badge-secondary',
                                    default => 'badge',
                                };
                            @endphp
                            <span class="{{ $badgeClass }} text-xs py-0.5 px-1.5">
                                {{ __(ucfirst($post->status)) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-dashboard-collapsible-card>
