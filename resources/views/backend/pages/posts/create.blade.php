<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(PostFilterHook::POSTS_CREATE_AFTER_BREADCRUMBS, '', $postType) !!}

    <form
        action="{{ route('admin.posts.store', $postType) }}"
        method="POST"
        enctype="multipart/form-data"
        data-prevent-unsaved-changes
    >
        @csrf
        @include('backend.pages.posts.partials.form', [
            'post' => null,
            'selectedTerms' => [],
            'postType' => $postType,
            'postTypeModel' => $postTypeModel,
            'taxonomies' => $taxonomies ?? [],
            'parentPosts' => $parentPosts ?? [],
            'mode' => 'create',
        ])
    </form>

    {!! Hook::applyFilters(PostFilterHook::AFTER_POST_FORM, '', $postType) !!}

    @push('scripts')
        <x-text-editor :editor-id="'content'" :minHeight="'200px'" :maxHeight="'1200px'" type="full" />
    @endpush
</x-layouts.backend-layout>
