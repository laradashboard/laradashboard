<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ExportService
{
    protected string $modelType;
    protected ?string $modelClass;
    protected ?string $modelNamespace;
    protected ?string $routePrefix;

    /**
     * Create a new ExportService instance.
     *
     * @param string $modelType The model type (e.g., 'Contact', 'Product')
     * @param string|null $modelNamespace Custom namespace for the model (optional)
     * @param string|null $routePrefix Route prefix for download URL (default: 'admin')
     */
    public function __construct(string $modelType, ?string $modelNamespace = null, ?string $routePrefix = 'admin')
    {
        $this->modelType = $modelType;
        $this->modelNamespace = $modelNamespace;
        $this->routePrefix = $routePrefix;
        $this->modelClass = $this->resolveModelClass($modelType, $modelNamespace);
    }

    /**
     * Get available filter options for export.
     */
    public function getFilterOptions(): array
    {
        if (!$this->modelClass) {
            return [];
        }

        $filters = [];
        $table = (new $this->modelClass())->getTable();
        $columns = Schema::getColumnListing($table);

        // Add common filter fields
        if (in_array('type', $columns)) {
            $filters['type'] = $this->getDistinctValues('type');
        }
        if (in_array('status', $columns)) {
            $filters['status'] = $this->getDistinctValues('status');
        }
        if (in_array('is_active', $columns)) {
            $filters['is_active'] = [['value' => '1', 'label' => 'Active'], ['value' => '0', 'label' => 'Inactive']];
        }

        // Add relationship filters - use actual column names (with _id)
        if (in_array('category_id', $columns) && method_exists($this->modelClass, 'category')) {
            $filters['category_id'] = $this->getRelationshipOptions('category', 'name');
        }
        if (in_array('status_id', $columns) && method_exists($this->modelClass, 'status')) {
            $filters['status_id'] = $this->getRelationshipOptions('status', 'name');
        }
        if (in_array('type_id', $columns) && method_exists($this->modelClass, 'contactType')) {
            $filters['type_id'] = $this->getRelationshipOptions('contactType', 'name');
        }

        return $filters;
    }

    /**
     * Get distinct values for a column.
     */
    protected function getDistinctValues(string $column): array
    {
        return $this->modelClass::distinct()
            ->whereNotNull($column)
            ->pluck($column)
            ->map(fn($value) => ['value' => $value, 'label' => ucfirst($value)])
            ->toArray();
    }

    /**
     * Get relationship options for filters.
     */
    protected function getRelationshipOptions(string $relation, string $labelField): array
    {
        try {
            $relatedModel = (new $this->modelClass())->$relation()->getRelated();
            $table = $relatedModel->getTable();
            
            // Check if the label field exists, fallback to common alternatives
            if (!Schema::hasColumn($table, $labelField)) {
                $alternatives = ['title', 'label', 'value', 'id'];
                foreach ($alternatives as $alt) {
                    if (Schema::hasColumn($table, $alt)) {
                        $labelField = $alt;
                        break;
                    }
                }
            }
            
            return $relatedModel::select('id', $labelField)
                ->get()
                ->map(fn($item) => ['value' => $item->id, 'label' => $item->$labelField])
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get available columns for export from the model's table.
     */
    public function getAvailableColumns(): array
    {
        if ($this->modelClass && class_exists($this->modelClass)) {
            $table = (new $this->modelClass())->getTable();
            return Schema::getColumnListing($table);
        }
        return [];
    }
    public function export(array $selectedColumns, array $filters = []): array
    {
        if (! $this->modelClass || empty($selectedColumns)) {
            return ['exportReady' => false, 'downloadUrl' => null];
        }

        $table = (new $this->modelClass())->getTable();
        $actualColumns = Schema::getColumnListing($table);

        $exportColumns = array_values(array_intersect($selectedColumns, $actualColumns));
        if (empty($exportColumns)) {
            return ['exportReady' => false, 'downloadUrl' => null];
        }

        $displayFieldCandidates = ['name', 'title', 'value', 'email'];
        $relatedFields = [];
        foreach ($exportColumns as $col) {
            if (Str::endsWith($col, '_id')) {
                $relation = Str::camel(str_replace('_id', '', $col));
                if (method_exists($this->modelClass, $relation)) {
                    $relatedModel = (new $this->modelClass())->$relation()->getRelated();
                    $relatedTable = $relatedModel->getTable();
                    foreach ($displayFieldCandidates as $candidate) {
                        if (Schema::hasColumn($relatedTable, $candidate)) {
                            $relatedFields[$col] = $candidate;
                            break;
                        }
                    }
                }
            }
        }

        $relations = [];
        foreach (array_keys($relatedFields) as $col) {
            $relations[] = Str::camel(str_replace('_id', '', $col));
        }

        // Apply filters to the query - only when explicitly set
        $query = $this->modelClass::query()->select($exportColumns)->with($relations);
        
        foreach ($filters as $key => $value) {
            if (in_array($key, $actualColumns) && $value !== '' && $value !== null) {
                $query->where($key, $value);
            }
        }
        
        $records = $query->get();

        $header = $exportColumns;
        foreach ($relatedFields as $col => $field) {
            $relationName = Str::camel(str_replace('_id', '', $col));
            $header[] = $relationName . '_' . $field;
        }
        $csv = implode(',', $header) . "\n";

        foreach ($records as $record) {
            $row = [];
            foreach ($exportColumns as $col) {
                $value = $record->$col;
                // Exclude HTML/CSS from content and excerpt fields
                if (in_array($col, ['content', 'excerpt']) && is_string($value)) {
                    $value = strip_tags($value);
                }
                if (is_null($value) || $value === '') {
                    $row[] = '';
                } elseif (is_scalar($value)) {
                    $row[] = (string) $value;
                } elseif (is_object($value) && method_exists($value, '__toString')) {
                    $row[] = (string) $value;
                } elseif (is_object($value) && method_exists($value, 'value')) {
                    $row[] = (string) $value->value;
                } else {
                    $row[] = json_encode($value);
                }
            }
            foreach ($relatedFields as $col => $field) {
                $relationName = Str::camel(str_replace('_id', '', $col));
                $related = $record->$relationName ?? null;
                $relatedValue = '';
                if ($related && isset($related->$field)) {
                    $relatedValue = $related->$field;
                }
                $row[] = $relatedValue;
            }
            $csv .= implode(',', $row) . "\n";
        }

        $filename = strtolower($this->modelType) . '-export-' . now()->format('YmdHis') . '.csv';
        $path = storage_path("app/exports/{$filename}");
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, $csv);

        $downloadUrl = $this->getDownloadUrl($filename);
        return [
            'exportReady' => true,
            'downloadUrl' => $downloadUrl,
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
     * Get the download URL for the exported file.
     */
    protected function getDownloadUrl(string $filename): string
    {
        // Try CRM route first
        if (\Illuminate\Support\Facades\Route::has('admin.crm.export.download')) {
            try {
                return route('admin.crm.export.download', ['filename' => $filename]);
            } catch (\Exception $e) {
                // Fall through to next option
            }
        }

        // Fallback to generic export route
        if (\Illuminate\Support\Facades\Route::has($this->routePrefix . '.export.download')) {
            try {
                return route($this->routePrefix . '.export.download', ['filename' => $filename]);
            } catch (\Exception $e) {
                // Fall through to default
            }
        }

        // Default fallback
        return url($this->routePrefix . '/export/download/' . $filename);
    }
}
