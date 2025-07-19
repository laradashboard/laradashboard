@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

    {!! ld_apply_filters('posts_show_after_breadcrumbs', '', $postType) !!}

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
                        {{ __('Author:') }} {{ $post->user->name }}
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
                        <span class="ml-1 inline-flex items-center justify-center px-2 py-1 text-xs font-medium
                            {{ $post->status === 'publish' ? 'text-green-800 bg-green-100 dark:bg-green-900/20 dark:text-green-400' : '' }}
                            {{ $post->status === 'draft' ? 'text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-300' : '' }}
                            {{ $post->status === 'pending' ? 'text-orange-800 bg-orange-100 dark:bg-orange-900/20 dark:text-orange-400' : '' }}
                            {{ $post->status === 'future' ? 'text-blue-800 bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400' : '' }}
                            {{ $post->status === 'private' ? 'text-purple-800 bg-purple-100 dark:bg-purple-900/20 dark:text-purple-400' : '' }}
                            rounded-full">
                            {{ ucfirst($post->status) }}
                        </span>
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
                    <div class="prose max-w-none dark:prose-invert prose-headings:font-medium prose-headings:text-gray-700 dark:prose-headings:text-white/90 prose-p:text-gray-700 dark:prose-p:text-gray-300">
                        {!! $post->content !!}
                    </div>
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
                                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-white">
                                                {{ $term->name }}
                                            </span>
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
</div>
@endsection
