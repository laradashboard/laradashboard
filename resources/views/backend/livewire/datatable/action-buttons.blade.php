<x-buttons.action-buttons
    :label="$actionColumnLabel"
    :show-label="$showActionColumnLabel"
    :icon="$actionColumnIcon"
    :deleteAction="$deleteAction"
    align="right"
>
    {!! $beforeActionView !!}

    @if (isset($routes['view']) && $routes['view'] ?? false && $componentPermissions['view'] ?? false)
        <x-buttons.action-item
            :href="$viewRouteUrl"
            :icon="$viewButtonIcon"
            :label="$viewButtonLabel"
        />
    @endif

    {!! $afterActionView !!}

    @if (isset($routes['edit']) && $routes['edit'] ?? false && $componentPermissions['edit'] ?? false && (($componentPermissions['edit'] === true) || auth()->user()->can('update', $item)))
        <x-buttons.action-item
            :href="$editRouteUrl"
            :icon="$editButtonIcon"
            :label="$editButtonLabel"
        />
    @endif

    {!! $afterActionEdit !!}

    {{-- Show delete button if: 1) is_deleteable doesn't exist, OR 2) is_deleteable exists and is true --}}
    @if ((!isset($item->is_deleteable) || $item->is_deleteable === true) && isset($routes['delete']) && $routes['delete'] ?? false && $permissions['delete'])
        <div x-data="{ deleteModalOpen: false }">
            
            <x-buttons.action-item
                type="modal-trigger"
                modal-target="deleteModalOpen"
                :icon="$deleteButtonIcon"
                :label="$deleteButtonLabel"
                class="text-red-600 dark:text-red-400"
            />

            @if($deleteAction['livewire'] ?? false)
                <x-modals.confirm-delete
                    id="delete-modal-{{ $item->id }}"
                    title="{{ __('Delete :model', ['model' => $modelNameSingular]) }}"
                    content="{{ __('Are you sure you want to delete this :model?', ['model' => $modelNameSingular]) }}"
                    :wireClick="'deleteItem(' . $item->id . ')'"
                    modalTrigger="deleteModalOpen"
                    cancelButtonText="{{ __('No, cancel') }}"
                    confirmButtonText="{{ __('Yes, Confirm') }}"
                />
            @else
                <x-modals.confirm-delete
                    id="delete-modal-{{ $item->id }}"
                    title="{{ __('Delete :model', ['model' => $modelNameSingular]) }}"
                    content="{{ __('Are you sure you want to delete this :model?', ['model' => $modelNameSingular]) }}"
                    formId="delete-form-{{ $item->id }}"
                    formAction="{{ $deleteAction['url'] ?? $deleteRouteUrl }}"
                    modalTrigger="deleteModalOpen"
                    cancelButtonText="{{ __('No, cancel') }}"
                    confirmButtonText="{{ __('Yes, Confirm') }}"
                />
            @endif
        </div>
    @endif

    {!! $afterActionDelete !!}
</x-buttons.action-buttons>