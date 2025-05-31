@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

    {!! ld_apply_filters('posts_create_after_breadcrumbs', '', $postType) !!}

    <form action="{{ route('admin.posts.store', $postType) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('backend.pages.posts.partials.form', [
            'post' => null,
            'selectedTerms' => [],
        ])
    </form>

    {!! ld_apply_filters('after_post_form', '') !!}
</div>
@endsection

@push('scripts')
<x-quill-editor :editor-id="'content'" />
@endpush
