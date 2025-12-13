<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(PostFilterHook::POSTS_EDIT_AFTER_BREADCRUMBS, '', $postType) !!}

    <x-card>
        <form
            action="{{ route('admin.posts.update', [$postType, $post->id]) }}"
            method="POST"
            class="space-y-6"
            enctype="multipart/form-data"
            data-prevent-unsaved-changes
        >
            @csrf
            @method('PUT')

            @include('backend.pages.posts.partials.form', [
                'post' => $post,
                'selectedTerms' => $selectedTerms ?? [],
                'postType' => $postType,
                'postTypeModel' => $postTypeModel,
                'taxonomies' => $taxonomies ?? [],
                'parentPosts' => $parentPosts ?? [],
                'mode' => 'edit',
            ])
        </form>
    </x-card>

    {!! Hook::applyFilters(PostFilterHook::AFTER_POST_FORM, '', $postType) !!}

    @push('scripts')
        <x-text-editor :editor-id="'content'" :minHeight="'200px'" :maxHeight="'1200px'" type="full" />
    @endpush
</x-layouts.backend-layout>
