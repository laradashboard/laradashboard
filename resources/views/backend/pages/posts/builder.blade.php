<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($post) ? 'Edit' : 'Create' }} {{ $postTypeModel->label_singular }} - {{ config('app.name', 'Laravel') }}</title>

    {{-- Load iconify for icons --}}
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@2.3.0/dist/iconify-icon.min.js"></script>

    @include('backend.layouts.partials.theme-colors')
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/lara-builder/post-entry.jsx'])

    {{-- Inject PHP-registered blocks --}}
    {!! app(\App\Services\Builder\BuilderService::class)->injectToFrontend('page') !!}
</head>
<body class="font-sans antialiased">
    <div
        id="lara-builder-root"
        data-context="page"
        data-initial-data="{{ json_encode($initialData ?? null) }}"
        data-post-data="{{ json_encode($postData) }}"
        data-save-url="{{ $saveUrl }}"
        data-list-url="{{ route('admin.posts.index', $postType) }}"
        data-upload-url="{{ route('admin.posts.upload-image', $postType) }}"
        data-video-upload-url="{{ route('admin.posts.upload-video', $postType) }}"
        data-taxonomies="{{ json_encode($taxonomies ?? []) }}"
        data-selected-terms="{{ json_encode($selectedTerms ?? []) }}"
        data-parent-posts="{{ json_encode($parentPosts ?? []) }}"
        data-post-type="{{ $postType }}"
        data-post-type-model="{{ json_encode([
            'label' => $postTypeModel->label,
            'label_singular' => $postTypeModel->label_singular,
            'hierarchical' => $postTypeModel->hierarchical,
            'supports_editor' => $postTypeModel->supports_editor,
            'supports_excerpt' => $postTypeModel->supports_excerpt,
            'supports_thumbnail' => $postTypeModel->supports_thumbnail,
            'icon' => $postTypeModel->icon ?? 'lucide:file-text',
        ]) }}"
        data-statuses="{{ json_encode(\App\Models\Post::getPostStatuses()) }}"
        data-translations='@json(__("*"))'
    ></div>

    {{-- Media Library Modal --}}
    <x-media-modal
        id="laraBuilderMediaModal"
        :title="__('Select Media')"
        :multiple="false"
        allowedTypes="all"
        buttonClass="hidden"
    />

    @stack('scripts')
</body>
</html>
