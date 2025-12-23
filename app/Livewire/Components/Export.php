<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Livewire\Component;
use App\Services\ExportService;

class Export extends Component
{
    public $modelType;
    public $modelNamespace;
    public $routePrefix;
    public $availableColumns = [];
    public $selectedColumns = [];
    public $exportReady = false;
    public $downloadUrl = null;
    public $allSelected = false;
    public $filtersItems = [];
    public $filtersValues = [];

    public function mount($modelType = null, $modelNamespace = null, $filtersItems = [], $routePrefix = 'admin')
    {
        $this->modelType = $modelType;
        $this->modelNamespace = $modelNamespace;
        $this->routePrefix = $routePrefix;
        
        $exportService = new ExportService($modelType, $modelNamespace, $routePrefix);
        $this->availableColumns = $exportService->getAvailableColumns();
        
        // Only use filters if explicitly passed
        $this->filtersItems = $filtersItems;
        
        $this->selectedColumns = $this->availableColumns;
        $this->allSelected = count($this->selectedColumns) === count($this->availableColumns);

        // Initialize filter values
        foreach ($this->filtersItems as $field => $options) {
            $this->filtersValues[$field] = '';
        }
    }

    public function updatedAllSelected($value)
    {
        if ($value) {
            $this->selectedColumns = $this->availableColumns;
        } else {
            $this->selectedColumns = [];
        }
        $this->exportReady = count($this->selectedColumns) > 0;
    }

    public function updatedSelectedColumns()
    {
        $this->allSelected = count($this->selectedColumns) === count($this->availableColumns);
        $this->exportReady = count($this->selectedColumns) > 0;
    }

    public function updatedType($value)
    {
        $this->availableColumns = (new ExportService($this->modelType, $this->modelNamespace, $this->routePrefix))->getAvailableColumns();
        $this->selectedColumns = $this->availableColumns;
        $this->allSelected = count($this->selectedColumns) === count($this->availableColumns);
        $this->exportReady = count($this->selectedColumns) > 0;
    }

    public function export()
    {
        $result = (new ExportService($this->modelType, $this->modelNamespace, $this->routePrefix))
            ->export($this->selectedColumns, $this->filtersValues);

        $this->exportReady = $result['exportReady'];
        $this->downloadUrl = $result['downloadUrl'] ?? null;

        if ($this->exportReady && $this->downloadUrl) {
            $this->dispatch('export-download', url: $this->downloadUrl);
        }
    }

    public function render()
    {
        return view('components.export', [
            'availableColumns' => $this->availableColumns,
            'selectedColumns' => $this->selectedColumns,
            'exportReady' => $this->exportReady,
            'modelType' => $this->modelType,
            'downloadUrl' => $this->downloadUrl,
            'filtersItems' => $this->filtersItems,
        ]);
    }
}
