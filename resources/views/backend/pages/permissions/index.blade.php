<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(PermissionFilterHook::PERMISSIONS_AFTER_BREADCRUMBS, '') !!}

    {!! Hook::applyFilters(PermissionFilterHook::PERMISSIONS_BEFORE_TABLE, '') !!}

    @livewire('datatable.permission-datatable', ['lazy' => true])

    {!! Hook::applyFilters(PermissionFilterHook::PERMISSIONS_AFTER_TABLE, '') !!}
</x-layouts.backend-layout>
