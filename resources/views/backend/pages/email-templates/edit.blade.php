<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-slot name="breadcrumbsData">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs">
            <x-slot name="title_after">
                <a href="{{ route('admin.email-templates.show', $template->id) }}" class="btn-primary rounded-full text-xs">
                    {{ __('View Template') }}
                    <iconify-icon icon="lucide:eye" class="ml-1"></iconify-icon>
                </a>
            </x-slot>
        </x-breadcrumbs>
    </x-slot>

    @include('backend.pages.email-templates.partials._form')
</x-layouts.backend-layout>
