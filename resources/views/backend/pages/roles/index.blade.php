<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(RoleFilterHook::ROLES_AFTER_BREADCRUMBS, '') !!}

    {!! Hook::applyFilters(RoleFilterHook::ROLES_BEFORE_TABLE, '') !!}

    @livewire('datatable.role-datatable', ['lazy' => true])

    {!! Hook::applyFilters(RoleFilterHook::ROLES_AFTER_TABLE, '') !!}
</x-layouts.backend-layout>
