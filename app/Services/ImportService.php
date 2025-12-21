<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportService
{
    protected string $modelType;
    protected ?string $modelClass;
    protected ?string $modelNamespace;

    /**
     * Create a new ImportService instance.
     *
     * @param string $modelType The model type (e.g., 'Contact', 'Product')
     * @param string|null $modelNamespace Custom namespace for the model (optional)
     */
    public function __construct(string $modelType, ?string $modelNamespace = null)
    {
        $this->modelType = $modelType;
        $this->modelNamespace = $modelNamespace;
        $this->modelClass = $this->resolveModelClass($modelType, $modelNamespace);
    }

    /**
     * Get the required columns for import from the model.
     */
    public function getRequiredColumns(): array
    {
        if ($this->modelClass && method_exists($this->modelClass, 'requiredImportColumns')) {
            return $this->modelClass::requiredImportColumns();
        }
        return [];
    }

    /**
     * Get all valid columns for import from the model.
     */
    public function getValidColumns(): array
    {
        if ($this->modelClass && method_exists($this->modelClass, 'validImportColumns')) {
            return $this->modelClass::validImportColumns();
        }
        return [];
    }

    /**
     * Validate the uploaded file against expected columns.
     */
    public function validateFile($file, array $validColumns, array $requiredColumns): array
    {
        $headers = [];
        $rows = [];
        $missingColumns = [];
        $missingRequiredColumns = [];
        $unmatchedColumns = [];
        $validationErrors = [];

        if ($file) {
            $fileInfo = $this->uploadedFileInfo($file);
            $headers = $fileInfo['headers'] ?? [];
            $rows = $fileInfo['rows'] ?? [];

            // Normalize headers for comparison
            $normalizedHeaders = array_map(fn ($h) => strtolower(trim($h)), $headers);

            foreach ($validColumns as $column) {
                if (! in_array(strtolower(trim($column)), $normalizedHeaders)) {
                    $missingColumns[] = $column;
                }
            }
            foreach ($requiredColumns as $column) {
                if (! in_array(strtolower(trim($column)), $normalizedHeaders)) {
                    $missingRequiredColumns[] = $column;
                }
            }

            $normalizedValid = array_map(fn ($c) => strtolower(trim($c)), $validColumns);
            foreach ($headers as $col) {
                if (! in_array(strtolower(trim($col)), $normalizedValid)) {
                    $unmatchedColumns[] = $col;
                }
            }

            $validationErrors = $this->validateRows($rows, $headers);
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
            'missingColumns' => $missingColumns,
            'missingRequiredColumns' => $missingRequiredColumns,
            'unmatchedColumns' => $unmatchedColumns,
            'validationErrors' => $validationErrors,
        ];
    }

    /**
     * Extract file information (headers and rows).
     */
    public function uploadedFileInfo(UploadedFile $file): array
    {
        try {
            $extension = $file->getClientOriginalExtension();

            if (in_array($extension, ['xlsx', 'xls'])) {
                // Check if ZipArchive is available for Excel files
                if (! class_exists('ZipArchive')) {
                    throw new \Exception('ZipArchive extension is required for Excel file processing. Please install php-zip extension or use CSV files instead.');
                }
                $spreadsheet = IOFactory::load($file->getPathname());
                $worksheet = $spreadsheet->getActiveSheet();
                $data = $worksheet->toArray();
            } else {
                $handle = fopen($file->getPathname(), 'r');
                $data = [];
                while (($row = fgetcsv($handle)) !== false) {
                    $data[] = $row;
                }
                fclose($handle);
            }

            $headers = [];
            $rows = [];
            if (is_array($data) && count($data) > 0) {
                $headers = $data[0];
                if ($extension === 'csv') {
                    $headers = array_map(function ($h) {
                        $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);
                        return trim($h);
                    }, $headers);
                }
                $rows = array_slice($data, 1);
            }

            return [
                'headers' => $headers,
                'rows' => $rows,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error extracting headers: ' . $e->getMessage());

            // If it's a ZipArchive error, provide specific guidance
            if (strpos($e->getMessage(), 'ZipArchive') !== false) {
                throw new \Exception('Excel file processing requires PHP ZIP extension. Please install php-zip extension or use CSV files instead. Error: ' . $e->getMessage());
            }

            throw new \Exception('Error reading file: ' . $e->getMessage());
        }
    }

    /**
     * Validate all rows against form request rules.
     */
    public function validateRows(array $rows, array $headers): array
    {
        $results = [];
        $formRequestClass = $this->resolveFormRequestClass($this->modelType);

        if (! class_exists($formRequestClass)) {
            return ['error' => "FormRequest class not found for model type: {$this->modelType}"];
        }

        $rules = (new $formRequestClass())->rules();
        $messages = method_exists($formRequestClass, 'messages') ? (new $formRequestClass())->messages() : [];

        foreach ($rows as $index => $row) {
            $rowResult = [];
            $data = $this->extractRowData($row, $headers);

            $validator = Validator::make($data, $rules, $messages);

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $field => $errs) {
                    $value = isset($data[$field]) ? $data[$field] : '';
                    $rowResult[$field] = implode(', ', array_map(function ($msg) use ($value) {
                        return $msg . ($value !== '' ? " ($value)" : "");
                    }, $errs));
                }
                $results[$index] = $rowResult;
            }
        }
        return $results;
    }

    /**
     * Import data from file into the database.
     */
    public function import($file, array $columnMappings, array $optionalRequiredValues, $progressCallback = null): array
    {
        $fileInfo = $this->uploadedFileInfo($file);
        $headers = $fileInfo['headers'] ?? [];
        $rows = $fileInfo['rows'] ?? [];
        $total = count($rows);
        $validationErrors = [];

        $modelClass = $this->modelClass;
        if (! $modelClass) {
            return ['total' => $total, 'validationErrors' => ['error' => 'Model class not found']];
        }

        $requiredColumns = $this->getRequiredColumns();

        foreach ($rows as $i => $row) {
            $data = [];
            foreach ($columnMappings as $modelField => $fileColumn) {
                $fileColIndex = array_search($fileColumn, $headers);
                if ($fileColIndex !== false && isset($row[$fileColIndex])) {
                    $value = $row[$fileColIndex];
                    // Convert empty strings to null
                    $data[$modelField] = ($value === '') ? null : $value;
                }
            }

            // Assign optional required values
            foreach ($optionalRequiredValues as $field => $value) {
                if ($value !== null && $value !== '') {
                    $data[$field] = $value;
                }
            }

            // Clean empty values to null
            $data = $this->cleanEmptyValues($data);

            // Check for missing required fields after mapping
            $missingRequired = [];
            foreach ($requiredColumns as $requiredField) {
                if (! array_key_exists($requiredField, $data) || $data[$requiredField] === null || $data[$requiredField] === '') {
                    $missingRequired[] = $requiredField;
                }
            }
            if (! empty($missingRequired)) {
                $validationErrors[$i] = ['missing_required' => implode(', ', $missingRequired)];
                continue;
            }

            $formRequestClass = $this->resolveFormRequestClass($this->modelType);
            $rowErrors = [];
            if (class_exists($formRequestClass)) {
                $rules = (new $formRequestClass())->rules();
                $messages = method_exists($formRequestClass, 'messages') ? (new $formRequestClass())->messages() : [];
                $validator = Validator::make($data, $rules, $messages);

                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $field => $errs) {
                        $value = isset($data[$field]) ? $data[$field] : '';
                        $rowErrors[$field] = implode(', ', array_map(function ($msg) use ($value) {
                            return $msg . ($value !== '' ? " ($value)" : "");
                        }, $errs));
                    }
                    $validationErrors[$i] = $rowErrors;
                    continue;
                }
            }

            try {
                $modelClass::create($data);
                if ($progressCallback) {
                    $progressCallback($i + 1);
                }
            } catch (\Exception $e) {
                $validationErrors[$i] = ['import' => $e->getMessage()];
            }
        }

        return [
            'total' => $total,
            'validationErrors' => $validationErrors,
        ];
    }

    /**
     * Resolve the model class from model type and optional namespace.
     */
    protected function resolveModelClass(string $modelType, ?string $modelNamespace = null): ?string
    {
        $normalized = ucfirst(strtolower(str_ends_with($modelType, 's') ? substr($modelType, 0, -1) : $modelType));

        // Try custom namespace first if provided
        if ($modelNamespace) {
            $customClass = rtrim($modelNamespace, '\\') . '\\' . $normalized;
            if (class_exists($customClass)) {
                return $customClass;
            }
        }

        // Try common namespaces
        $namespaces = [
            'Modules\\Crm\\Models\\',
            'App\\Models\\',
        ];

        foreach ($namespaces as $namespace) {
            $class = $namespace . $normalized;
            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Resolve the Form Request class for validation.
     */
    protected function resolveFormRequestClass(string $modelType): string
    {
        $normalized = ucfirst(strtolower(str_ends_with($modelType, 's') ? substr($modelType, 0, -1) : $modelType));

        // Try CRM module patterns
        $patterns = [
            "Modules\\Crm\\Http\\Requests\\{$normalized}Request",
            "Modules\\Crm\\Http\\Requests\\{$normalized}FormRequest",
        ];
        foreach ($patterns as $crmClass) {
            if (class_exists($crmClass)) {
                return $crmClass;
            }
        }

        // Try core app patterns
        $corePatterns = [
            "App\\Http\\Requests\\{$normalized}Request",
            "App\\Http\\Requests\\{$normalized}FormRequest",
        ];
        foreach ($corePatterns as $coreClass) {
            if (class_exists($coreClass)) {
                return $coreClass;
            }
        }

        // Special fallback for Post model
        if ($normalized === 'Post' && class_exists('App\\Http\\Requests\\Post\\PostFormRequest')) {
            return 'App\\Http\\Requests\\Post\\PostFormRequest';
        }

        return "App\\Http\\Requests\\{$normalized}FormRequest";
    }

    /**
     * Extract row data based on headers.
     */
    public function extractRowData(array $row, array $headers): array
    {
        $data = [];
        foreach ($headers as $i => $header) {
            if (isset($row[$i]) && $header) {
                $data[$header] = $row[$i];
            }
        }
        return $this->cleanEmptyValues($data);
    }

    /**
     * Clean empty string values to null for better database handling.
     */
    protected function cleanEmptyValues(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value === '' || $value === null) {
                $data[$key] = null;
            }
        }
        return $data;
    }

    /**
     * Find missing columns from the uploaded file.
     */
    public function findMissingColumns(array $headers, array $validColumns, array $requiredColumns): array
    {
        $foundMissingColumns = [];
        $foundMissingRequiredColumns = [];
        $normalizedHeaders = array_map(fn ($h) => strtolower(trim($h)), $headers);

        foreach ($validColumns as $column) {
            if (! in_array(strtolower(trim($column)), $normalizedHeaders)) {
                $foundMissingColumns[] = $column;
            }
        }
        foreach ($requiredColumns as $column) {
            if (! in_array(strtolower(trim($column)), $normalizedHeaders)) {
                $foundMissingRequiredColumns[] = $column;
            }
        }

        $normalizedValid = array_map(fn ($c) => strtolower(trim($c)), $validColumns);
        $unmatchedColumns = [];
        foreach ($headers as $col) {
            if (! in_array(strtolower(trim($col)), $normalizedValid)) {
                $unmatchedColumns[] = $col;
            }
        }

        return [
            'missingColumns' => $foundMissingColumns,
            'missingRequiredColumns' => $foundMissingRequiredColumns,
            'unmatchedColumns' => $unmatchedColumns,
        ];
    }

    /**
     * Auto-select column mappings based on name matching.
     */
    public function autoSelectColumnMappings(array $mandatoryColumns, array $optionalColumns, array $fileColumns): array
    {
        $columnMappings = [];
        foreach (array_merge($mandatoryColumns, $optionalColumns) as $tableField) {
            foreach ($fileColumns as $csvHeader) {
                if (strtolower(trim($tableField)) === strtolower(trim($csvHeader))) {
                    $columnMappings[$tableField] = $csvHeader;
                    break;
                }
            }
        }
        return $columnMappings;
    }
}
