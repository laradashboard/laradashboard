<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(ActionLogFilterHook::ACTION_LOG_AFTER_BREADCRUMBS, '') !!}

    @livewire('datatable.action-log-datatable', ['lazy' => true])

    {!! Hook::applyFilters(ActionLogFilterHook::ACTION_LOG_AFTER_TABLE, '') !!}
</x-layouts.backend-layout>
