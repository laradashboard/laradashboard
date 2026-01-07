<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(ModuleFilterHook::MODULES_AFTER_BREADCRUMBS, '') !!}

    {!! Hook::applyFilters(ModuleFilterHook::MODULES_BEFORE_LIST, '') !!}

    <livewire:datatable.module-datatable />

    {!! Hook::applyFilters(ModuleFilterHook::MODULES_AFTER_LIST, '') !!}
</x-layouts.backend-layout>
