<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Schema;

class DataManager extends Component
{
    use WithFileUploads;

    public $model;
    public $modelNamespace;
    public $mode = 'both';
    public $file;
    public $filters = [];
    public $filtersValues = [];
    public $columns = [];
    public $selectedColumns = [];
    public $message = '';
    public $importErrors = [];
    public $importStats = [];
    public $fileValidation = [];

    public function mount($model, $mode = 'both', $filters = '[]', $modelNamespace = null)
    {
        $this->model = $model;
        $this->mode = $mode;
        $this->modelNamespace = $modelNamespace;
        $this->filters = is_string($filters) ? json_decode($filters, true) : $filters;
        
        $modelClass = $this->getModelClass();
        
        if (class_exists($modelClass)) {
            $table = (new $modelClass())->getTable();
            $this->columns = Schema::getColumnListing($table);
            $this->selectedColumns = $this->columns;
        }
    }

    public function updatedFile()
    {
        $this->validateFile();
    }

    protected function validateFile()
    {
        if (!$this->file) {
            $this->fileValidation = [];
            return;
        }

        $this->fileValidation = [
            'name' => $this->file->getClientOriginalName(),
            'size' => $this->file->getSize(),
            'sizeFormatted' => $this->formatBytes($this->file->getSize()),
            'type' => $this->file->getClientOriginalExtension(),
            'isValid' => true,
            'errors' => [],
            'warnings' => [],
            'preview' => [],
            'dataTypeErrors' => []
        ];

        // Validate file type
        if (!in_array($this->file->getClientOriginalExtension(), ['csv', 'txt'])) {
            $this->fileValidation['isValid'] = false;
            $this->fileValidation['errors'][] = 'Invalid file type. Only CSV files are allowed.';
        }

        // Validate file size (10MB)
        if ($this->file->getSize() > 10485760) {
            $this->fileValidation['isValid'] = false;
            $this->fileValidation['errors'][] = 'File size exceeds 10MB limit.';
        }

        // Read and validate CSV structure
        try {
            $path = $this->file->getRealPath();
            $handle = fopen($path, 'r');
            $header = fgetcsv($handle);
            
            if (!$header) {
                $this->fileValidation['isValid'] = false;
                $this->fileValidation['errors'][] = 'File is empty or invalid CSV format.';
                fclose($handle);
                return;
            }

            $this->fileValidation['headers'] = $header;
            $this->fileValidation['headerCount'] = count($header);
            
            // Get column types from database
            $modelClass = $this->getModelClass();
            $table = (new $modelClass())->getTable();
            $columnTypes = $this->getColumnTypes($table);
            
            // Check for required columns
            $missingColumns = array_diff($this->columns, $header);
            $extraColumns = array_diff($header, $this->columns);
            
            if (!empty($missingColumns)) {
                $this->fileValidation['warnings'][] = 'Missing columns: ' . implode(', ', $missingColumns);
            }
            
            if (!empty($extraColumns)) {
                $this->fileValidation['warnings'][] = 'Extra columns will be ignored: ' . implode(', ', $extraColumns);
            }

            // Count rows, preview data, and validate data types
            $rowCount = 0;
            $previewRows = [];
            $dataTypeErrors = [];
            
            while (($row = fgetcsv($handle)) !== false && $rowCount < 5) {
                $rowNumber = $rowCount + 2; // +2 because row 1 is header
                $rowData = array_combine($header, $row);
                $previewRows[] = $rowData;
                
                // Validate data types for each column
                foreach ($rowData as $column => $value) {
                    if (!isset($columnTypes[$column]) || $value === '' || $value === null) {
                        continue;
                    }
                    
                    $error = $this->validateDataType($column, $value, $columnTypes[$column], $rowNumber);
                    if ($error) {
                        $dataTypeErrors[] = $error;
                    }
                }
                
                $rowCount++;
            }
            
            // Count remaining rows
            while (fgetcsv($handle) !== false) {
                $rowCount++;
            }
            
            $this->fileValidation['rowCount'] = $rowCount;
            $this->fileValidation['preview'] = $previewRows;
            $this->fileValidation['dataTypeErrors'] = $dataTypeErrors;
            
            if (!empty($dataTypeErrors)) {
                $this->fileValidation['warnings'][] = count($dataTypeErrors) . ' data type mismatch(es) found in preview rows.';
            }
            
            if ($rowCount === 0) {
                $this->fileValidation['warnings'][] = 'File contains headers but no data rows.';
            }
            
            fclose($handle);
        } catch (\Exception $e) {
            $this->fileValidation['isValid'] = false;
            $this->fileValidation['errors'][] = 'Error reading file: ' . $e->getMessage();
        }
    }

    protected function getColumnTypes($table)
    {
        $columns = Schema::getColumnListing($table);
        $columnTypes = [];
        
        foreach ($columns as $column) {
            $type = Schema::getColumnType($table, $column);
            $columnTypes[$column] = $type;
        }
        
        return $columnTypes;
    }

    protected function validateDataType($column, $value, $type, $rowNumber)
    {
        $error = null;
        
        switch ($type) {
            case 'integer':
            case 'bigint':
            case 'smallint':
                if (!is_numeric($value) || (string)(int)$value !== (string)$value) {
                    $error = [
                        'row' => $rowNumber,
                        'column' => $column,
                        'value' => $value,
                        'expected' => 'integer',
                        'message' => "Column '{$column}' expects integer, got '{$value}'"
                    ];
                }
                break;
                
            case 'decimal':
            case 'float':
            case 'double':
                if (!is_numeric($value)) {
                    $error = [
                        'row' => $rowNumber,
                        'column' => $column,
                        'value' => $value,
                        'expected' => 'numeric',
                        'message' => "Column '{$column}' expects numeric value, got '{$value}'"
                    ];
                }
                break;
                
            case 'date':
                if (!strtotime($value)) {
                    $error = [
                        'row' => $rowNumber,
                        'column' => $column,
                        'value' => $value,
                        'expected' => 'date (YYYY-MM-DD)',
                        'message' => "Column '{$column}' expects valid date, got '{$value}'"
                    ];
                }
                break;
                
            case 'datetime':
            case 'timestamp':
                if (!strtotime($value)) {
                    $error = [
                        'row' => $rowNumber,
                        'column' => $column,
                        'value' => $value,
                        'expected' => 'datetime (YYYY-MM-DD HH:MM:SS)',
                        'message' => "Column '{$column}' expects valid datetime, got '{$value}'"
                    ];
                }
                break;
                
            case 'boolean':
                if (!in_array(strtolower($value), ['0', '1', 'true', 'false', 'yes', 'no'])) {
                    $error = [
                        'row' => $rowNumber,
                        'column' => $column,
                        'value' => $value,
                        'expected' => 'boolean (0/1, true/false, yes/no)',
                        'message' => "Column '{$column}' expects boolean value, got '{$value}'"
                    ];
                }
                break;
        }
        
        return $error;
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    protected function getModelClass()
    {
        if ($this->modelNamespace) {
            return $this->modelNamespace . '\\' . $this->model;
        }
        
        // Try common namespaces
        $namespaces = [
            "Modules\\Crm\\Models\\{$this->model}",
            "App\\Models\\{$this->model}",
        ];
        
        foreach ($namespaces as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }
        
        return "App\\Models\\{$this->model}";
    }

    public function import()
    {
        $this->validate(['file' => 'required|mimes:csv,txt']);

        try {
            $modelClass = $this->getModelClass();
            $path = $this->file->store('temp');
            $handle = fopen(storage_path("app/{$path}"), 'r');
            $header = fgetcsv($handle);
            
            $imported = 0;
            $skipped = 0;
            $this->importErrors = [];
            $rowNumber = 1;
            
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $data = array_combine($header, $row);
                
                // Convert empty strings to null for nullable fields
                foreach ($data as $key => $value) {
                    if ($value === '' || $value === null) {
                        $data[$key] = null;
                    }
                }
                
                try {
                    $modelClass::create($data);
                    $imported++;
                } catch (\Exception $e) {
                    $skipped++;
                    $errorMessage = $e->getMessage();
                    
                    // Parse error type
                    if (str_contains($errorMessage, 'Duplicate entry')) {
                        preg_match("/Duplicate entry '([^']+)'/", $errorMessage, $matches);
                        $value = $matches[1] ?? 'unknown';
                        $this->importErrors[] = [
                            'row' => $rowNumber,
                            'type' => 'Duplicate',
                            'message' => "Duplicate entry: {$value}",
                            'data' => $data
                        ];
                    } elseif (str_contains($errorMessage, 'cannot be null')) {
                        preg_match("/Column '([^']+)'/", $errorMessage, $matches);
                        $column = $matches[1] ?? 'unknown';
                        $this->importErrors[] = [
                            'row' => $rowNumber,
                            'type' => 'Required Field',
                            'message' => "Field '{$column}' is required",
                            'data' => $data
                        ];
                    } elseif (str_contains($errorMessage, 'Data too long')) {
                        preg_match("/column '([^']+)'/", $errorMessage, $matches);
                        $column = $matches[1] ?? 'unknown';
                        $this->importErrors[] = [
                            'row' => $rowNumber,
                            'type' => 'Data Too Long',
                            'message' => "Value too long for field '{$column}'",
                            'data' => $data
                        ];
                    } else {
                        $this->importErrors[] = [
                            'row' => $rowNumber,
                            'type' => 'Validation Error',
                            'message' => $errorMessage,
                            'data' => $data
                        ];
                    }
                }
            }
            
            fclose($handle);
            unlink(storage_path("app/{$path}"));
            
            $this->importStats = [
                'imported' => $imported,
                'skipped' => $skipped,
                'total' => $imported + $skipped
            ];
            
            if ($imported > 0) {
                $this->message = "Successfully imported {$imported} record(s)." . ($skipped > 0 ? " {$skipped} record(s) skipped due to errors." : '');
            } else {
                $this->message = "Error: No records were imported. All {$skipped} record(s) had errors.";
            }
            
            $this->file = null;
        } catch (\Exception $e) {
            $this->message = "Error: " . $e->getMessage();
        }
    }

    public function downloadSample()
    {
        $filename = strtolower($this->model) . '_sample.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, $this->columns);
            fputcsv($file, array_fill(0, count($this->columns), 'sample'));
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function export()
    {
        try {
            $modelClass = $this->getModelClass();
            $query = $modelClass::select($this->selectedColumns);
            
            foreach ($this->filtersValues as $field => $value) {
                if (!empty($value)) {
                    $query->where($field, $value);
                }
            }
            
            $data = $query->get();
            
            if ($data->isEmpty()) {
                $this->message = "No data to export.";
                return;
            }

            $filename = strtolower($this->model) . '_export_' . date('YmdHis') . '.csv';
            $path = storage_path("app/exports/{$filename}");
            
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }
            
            $handle = fopen($path, 'w');
            fputcsv($handle, $this->selectedColumns);
            
            foreach ($data as $row) {
                fputcsv($handle, $row->only($this->selectedColumns));
            }
            
            fclose($handle);
            
            return response()->download($path)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            $this->message = "Error: " . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.data-manager');
    }
}
