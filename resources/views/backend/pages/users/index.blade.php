<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-slot name="breadcrumbsData">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs">
            <x-slot name="title_after">
                @if (request('role'))
                    <span class="badge">{{ ucfirst(request('role')) }}</span>
                @endif
            </x-slot>
        </x-breadcrumbs>
    </x-slot>

    {!! Hook::applyFilters(UserFilterHook::USER_AFTER_BREADCRUMBS, '') !!}

    @livewire('datatable.user-datatable', ['lazy' => true])

    {!! Hook::applyFilters(UserFilterHook::USER_AFTER_TABLE, '') !!}
</x-layouts.backend-layout>
