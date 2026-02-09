<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\ImportService;

class ImportForm extends Component
{
    use WithFileUploads;

    protected $listeners = ['resetFormDelayed'];

    public $modelType;
    public $modelNamespace;
    public $file;
    public $maxFileSize = 5120; // 5MB in KB
    public $showFileHeaders = false;

    public $validationErrors = [];
    public array $requiredColumns = [];  // actual required columns
    public array $validColumns = [];  // actual valid columns
    public array $mandatoryColumns = [];
    public array $optionalColumns = [];
    public $fileColumns = [];    // uploaded file columns
    public $missingRequiredColumns = [];
    public $missingColumns = [];
    public $unmatchedColumns = [];
    public $columnMappings = [];
    public $fileInfo = null;
    public $updateExisting = false;
    public $isValidating = false;
    public $route;
    public $importProgress = 0;
    public $importTotal = 0;
    public $importing = false;
    public $importFinished = false;
    public $importRowErrors = [];
    public $optionalRequired = [];
    public $optionalRequiredValues = [];

    protected ImportService $importService;

    public function boot()
    {
        $this->importService = new ImportService($this->modelType ?? '', $this->modelNamespace ?? null);
    }

    public function mount($modelType = null, $modelNamespace = null, $optionalRequired = [], $route = null)
    {
        $this->modelType = $modelType;
        $this->modelNamespace = $modelNamespace;
        $this->optionalRequired = $optionalRequired ?? [];
        foreach ($this->optionalRequired as $field => $options) {
            $this->optionalRequiredValues[$field] = null;
        }

        // Auto-generate route if not provided
        if ($route) {
            $this->route = $route;
        } elseif (\Illuminate\Support\Facades\Route::has('admin.crm.' . strtolower($this->modelType) . '.import')) {
            $this->route = route('admin.crm.' . strtolower($this->modelType) . '.import');
        } elseif (\Illuminate\Support\Facades\Route::has('admin.' . strtolower($this->modelType) . 's.import')) {
            $this->route = route('admin.' . strtolower($this->modelType) . 's.import');
        } else {
            // Set a default placeholder route - actual import happens via Livewire component
            $this->route = '#';
        }

        $this->requiredColumns = $this->importService->getRequiredColumns();
        $this->validColumns = $this->importService->getValidColumns();
        $this->mandatoryColumns = $this->requiredColumns;
        $this->optionalColumns = array_diff($this->validColumns, $this->requiredColumns);
    }

    public function updatedFile()
    {
        $this->requiredColumns = $this->importService->getRequiredColumns();
        $this->validColumns = $this->importService->getValidColumns();
        $this->mandatoryColumns = $this->requiredColumns;
        $this->optionalColumns = array_diff($this->validColumns, $this->requiredColumns);

        $fileInfo = $this->importService->validateFile($this->file, $this->validColumns, $this->requiredColumns);
        $this->fileColumns = $fileInfo['headers'] ?? [];
        $this->missingColumns = $fileInfo['missingColumns'] ?? [];
        $this->missingRequiredColumns = $fileInfo['missingRequiredColumns'] ?? [];
        $this->unmatchedColumns = $fileInfo['unmatchedColumns'] ?? [];
        $this->validationErrors = $fileInfo['validationErrors'] ?? [];
        $this->fileInfo = $fileInfo;
        $this->showFileHeaders = true;
        $this->autoSelectColumnMappings();
    }

    public function autoSelectColumnMappings()
    {
        foreach (array_merge($this->mandatoryColumns, $this->optionalColumns) as $tableField) {
            foreach ($this->fileColumns as $csvHeader) {
                if (strtolower(trim($tableField)) === strtolower(trim($csvHeader))) {
                    $this->columnMappings[$tableField] = $csvHeader;
                    break;
                }
            }
        }
    }

    public function validateFile()
    {
        $this->isValidating = true;
        $this->validate([
            'file' => 'required|file|mimes:csv,xls,xlsx|max:' . $this->maxFileSize,
        ]);

        $fileInfo = $this->importService->validateFile($this->file, $this->validColumns, $this->requiredColumns);
        $this->fileColumns = $fileInfo['headers'] ?? [];
        $this->missingColumns = $fileInfo['missingColumns'] ?? [];
        $this->missingRequiredColumns = $fileInfo['missingRequiredColumns'] ?? [];
        $this->unmatchedColumns = $fileInfo['unmatchedColumns'] ?? [];
        $this->validationErrors = $fileInfo['validationErrors'] ?? [];
        $this->fileInfo = $fileInfo;
        $this->showFileHeaders = true;

        return $fileInfo;
    }

    public function resetForm()
    {
        $this->reset(['file', 'showFileHeaders', 'fileColumns', 'missingRequiredColumns', 'unmatchedColumns', 'fileInfo', 'isValidating']);
    }

    public function startImport()
    {
        $this->importing = true;
        $this->importFinished = false;
        $this->importProgress = 0;
        $this->importRowErrors = [];

        $result = $this->importService->import(
            $this->file,
            $this->columnMappings,
            $this->optionalRequiredValues,
            function ($progress) {
                $this->importProgress = $progress;
                $this->dispatch('$refresh');
            }
        );

        $this->importTotal = $result['total'] ?? 0;
        $this->validationErrors = $result['validationErrors'] ?? [];
        $this->importing = false;
        $this->importFinished = true;

        // Reset form after successful import
        if (empty($this->validationErrors)) {
            $this->resetFormAfterImport();
        }
    }

    public function resetFormAfterImport()
    {
        // Show success alert and schedule form reset
        $this->dispatch('show-import-success', [
            'message' => "Import completed successfully! {$this->importTotal} rows imported.",
            'callback' => 'resetFormDelayed',
        ]);
    }

    public function resetFormDelayed()
    {
        $this->reset([
            'file', 'showFileHeaders', 'fileColumns', 'missingRequiredColumns',
            'missingColumns', 'unmatchedColumns', 'columnMappings', 'fileInfo',
            'isValidating', 'importProgress', 'optionalRequiredValues',
        ]);

        // Reset optional required values
        foreach ($this->optionalRequired as $field => $options) {
            $this->optionalRequiredValues[$field] = null;
        }

        $this->importFinished = false;
    }

    public function removeFile()
    {
        $this->reset([
            'file', 'showFileHeaders', 'fileColumns', 'missingRequiredColumns',
            'missingColumns', 'unmatchedColumns', 'fileInfo', 'isValidating',
            'validationErrors', 'importTotal', 'columnMappings',
        ]);
    }

    public function isMappingFilled(): bool
    {
        foreach ($this->unmatchedColumns as $col) {
            if (empty($this->columnMappings[$col])) {
                return false;
            }
        }
        return true;
    }

    public function render()
    {
        return view('components.import-form', [
            'route' => $this->route,
            'unmatchedColumns' => $this->unmatchedColumns,
            'columnMappings' => $this->columnMappings,
            'validColumns' => $this->validColumns,
            'validationErrors' => $this->validationErrors,
            'mandatoryColumns' => $this->mandatoryColumns,
            'optionalColumns' => $this->optionalColumns,
            'optionalRequired' => $this->optionalRequired,
            'optionalRequiredValues' => $this->optionalRequiredValues,
        ]);
    }
}
