<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportExport extends Component
{
    use WithFileUploads;

    public string $modelClass;
    public array $filters = [];
    public $file;
    public array $columnMappings = [];
    public array $fileHeaders = [];
    public int $imported = 0;
    public array $errors = [];
    public string $activeTab = 'import';

    public function mount(string $modelClass, array $filters = [])
    {
        $this->modelClass = $modelClass;
        $this->filters = $filters;
    }

    public function updatedFile()
    {
        $this->fileHeaders = $this->extractHeaders();
        $this->autoMapColumns();
    }

    public function import()
    {
        $rows = $this->extractRows();
        
        foreach ($rows as $index => $row) {
            $data = $this->mapRowData($row);
            
            try {
                $this->modelClass::create($data);
                $this->imported++;
            } catch (\Exception $e) {
                $this->errors[$index] = $e->getMessage();
            }
        }
        
        $this->reset(['file', 'fileHeaders', 'columnMappings']);
    }

    public function export(): StreamedResponse
    {
        $query = $this->modelClass::query();
        
        foreach ($this->filters as $field => $value) {
            if ($value) {
                $query->where($field, $value);
            }
        }
        
        $data = $query->get();
        $columns = $this->getModelColumns();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        foreach ($columns as $index => $column) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $column);
        }
        
        // Data
        foreach ($data as $rowIndex => $item) {
            foreach ($columns as $colIndex => $column) {
                $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $item->$column ?? '');
            }
        }
        
        $writer = new Csv($spreadsheet);
        
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, strtolower(class_basename($this->modelClass)) . 's_' . date('Y-m-d') . '.csv');
    }

    protected function extractHeaders(): array
    {
        $data = $this->readFile();
        return $data[0] ?? [];
    }

    protected function extractRows(): array
    {
        $data = $this->readFile();
        return array_slice($data, 1);
    }

    protected function readFile(): array
    {
        $ext = $this->file->getClientOriginalExtension();
        
        if (in_array($ext, ['xlsx', 'xls'])) {
            $spreadsheet = IOFactory::load($this->file->getPathname());
            return $spreadsheet->getActiveSheet()->toArray();
        }
        
        $handle = fopen($this->file->getPathname(), 'r');
        $data = [];
        while (($row = fgetcsv($handle)) !== false) {
            $data[] = $row;
        }
        fclose($handle);
        
        return $data;
    }

    protected function autoMapColumns()
    {
        $modelColumns = $this->getModelColumns();
        
        foreach ($modelColumns as $column) {
            foreach ($this->fileHeaders as $header) {
                if (strtolower($column) === strtolower($header)) {
                    $this->columnMappings[$column] = $header;
                    break;
                }
            }
        }
    }

    protected function mapRowData(array $row): array
    {
        $data = [];
        
        foreach ($this->columnMappings as $modelColumn => $fileHeader) {
            $index = array_search($fileHeader, $this->fileHeaders);
            if ($index !== false && isset($row[$index])) {
                $data[$modelColumn] = $row[$index];
            }
        }
        
        return $data;
    }

    protected function getModelColumns(): array
    {
        if (method_exists($this->modelClass, 'validImportColumns')) {
            return $this->modelClass::validImportColumns();
        }
        
        return (new $this->modelClass)->getFillable();
    }

    public function render()
    {
        return view('components.import-export');
    }
}
