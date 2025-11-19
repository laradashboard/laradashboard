<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(TermFilterHook::TERM_AFTER_BREADCRUMBS, '', $taxonomyModel) !!}

    <div class="max-w-4xl mx-auto">
        @include('backend.pages.terms.partials.form')
    </div>

    @push('scripts')
        <x-text-editor :editor-id="'description'" />
    @endpush
</x-layouts.backend-layout>