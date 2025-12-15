<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(TermFilterHook::TERM_AFTER_BREADCRUMBS, '', $taxonomyModel) !!}

    @include('backend.pages.terms.partials.form')

    @push('scripts')
        <x-text-editor :editor-id="'description'" :height="'300px'" />
    @endpush
</x-layouts.backend-layout>