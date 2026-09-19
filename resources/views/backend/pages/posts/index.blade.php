<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-slot name="breadcrumbsData">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs">
            <x-slot name="title_after">
                @if (request('status'))
                    <span class="badge">{{ ucfirst(request('status')) }}</span>
                @endif
                @if (request('category'))
                    <span class="badge">{{ __('Category: :category', ['category' => request('category')]) }}</span>
                @endif
                @if (request('tag'))
                    <span class="badge">{{ __('Tag: :tag', ['tag' => request('tag')]) }}</span>
                @endif
            </x-slot>
        </x-breadcrumbs>
    </x-slot>

    {!! Hook::applyFilters(PostFilterHook::POSTS_AFTER_BREADCRUMBS, '', $postType) !!}

    @livewire('datatable.post-datatable', ['postType' => $postType ,'lazy' => true])

    {!! Hook::applyFilters(PostFilterHook::POSTS_AFTER_TABLE, '', $postType) !!}
</x-layouts.backend-layout>