@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

    {!! ld_apply_filters('terms_edit_breadcrumbs', '', $taxonomyModel) !!}

    <div class="max-w-4xl mx-auto">
        <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-700 dark:text-white/90">
                    {{ __('Edit') }} {{ $taxonomyModel->label_singular }}
                </h3>
            </div>
            <div class="p-5 space-y-5 sm:p-6">
                <form action="{{ route('admin.terms.update', [$taxonomy, $term->id]) }}" method="POST" enctype="multipart/form-data">
                    @method('PUT')
                    @include('backend.pages.terms.partials.form')
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<x-quill-editor :editor-id="'description'" />
@endpush
@endsection
