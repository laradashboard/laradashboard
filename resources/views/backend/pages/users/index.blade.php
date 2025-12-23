<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-slot name="breadcrumbsData">
        <div
            x-data="{ exportModalOpen: false }"
            @exportmodalopen.window="exportModalOpen = $event.detail"
        >
            <x-breadcrumbs :breadcrumbs="$breadcrumbs">
                <x-slot name="title_after">
                    @if (request('role'))
                        <span class="badge">{{ ucfirst(request('role')) }}</span>
                    @endif
                </x-slot>
                <x-slot name="actions_before">
                    @can('user.create')
                        <x-actions-dropdown
                            :label="__('Actions')"
                            icon="lucide:arrow-up-from-line"
                            position="right"
                        >
                            <x-actions-dropdown-item
                                icon="lucide:upload"
                                :label="__('Import')"
                                :description="__('Import users from a CSV file')"
                                :href="route('admin.users.import.form')"
                            />
                            <x-actions-dropdown-item
                                icon="lucide:download"
                                :label="__('Export')"
                                :description="__('Export users as a CSV file')"
                                click="$dispatch('exportmodalopen', true); isOpen = false"
                            />
                        </x-actions-dropdown>
                    @endcan
                </x-slot>
            </x-breadcrumbs>

            {!! Hook::applyFilters(UserFilterHook::USER_AFTER_BREADCRUMBS, '') !!}

            @livewire('datatable.user-datatable', ['lazy' => true])

            {!! Hook::applyFilters(UserFilterHook::USER_AFTER_TABLE, '') !!}

            <x-modals.export
                id="export-modal"
                title="{{ __('Export Users') }}"
                modalTrigger="exportModalOpen"
                modelType="User"
                modelNamespace="App\Models"
                :availableColumns="[]"
                :filtersItems="[]"
            />
        </div>
    </x-slot>
</x-layouts.backend-layout>
