@props([
    'id' => 'export-modal',
    'title' => __('Export ') . $modelType,
    'modalTrigger' => 'exportModalOpen',
    'modelType' => null,
    'modelNamespace' => null,
    'availableColumns' => [],
    'filtersItems' => [],
])

<x-modals.base :id="$id" :title="$title" :modalTrigger="$modalTrigger" size="md">
    <livewire:components.export
        :modelType="$modelType"
        :modelNamespace="$modelNamespace"
        :filtersItems="$filtersItems"
    />
</x-modals.base>
