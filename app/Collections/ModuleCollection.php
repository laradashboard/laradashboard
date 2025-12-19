<?php

declare(strict_types=1);

namespace App\Collections;

use App\Models\Module;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * A queryable collection for filesystem-based modules.
 * Provides Eloquent-like query capabilities for non-database data.
 */
class ModuleCollection
{
    protected Collection $items;

    protected ?string $search = null;

    /** @var array<string> */
    protected array $searchableFields = ['title', 'description', 'name'];

    protected ?string $sortField = null;

    protected string $sortDirection = 'asc';

    /** @var array<string, mixed> */
    protected array $filters = [];

    protected string $modulesPath;

    protected string $modulesStatusesPath;

    public function __construct()
    {
        $this->modulesPath = config('modules.paths.modules');
        $this->modulesStatusesPath = base_path('modules_statuses.json');
        $this->items = collect();
    }

    /**
     * Initialize and load all modules from filesystem.
     */
    public static function query(): self
    {
        $instance = new self();
        $instance->loadModules();

        return $instance;
    }

    /**
     * Load all modules from the filesystem.
     */
    protected function loadModules(): void
    {
        $modules = [];

        if (! File::exists($this->modulesPath)) {
            $this->items = collect();

            return;
        }

        $moduleStatuses = $this->getModuleStatuses();
        $moduleDirectories = File::directories($this->modulesPath);

        foreach ($moduleDirectories as $moduleDirectory) {
            $module = $this->loadModuleFromPath($moduleDirectory, $moduleStatuses);
            if ($module) {
                $modules[] = $module;
            }
        }

        $this->items = collect($modules);
    }

    /**
     * Load a single module from its directory path.
     */
    protected function loadModuleFromPath(string $path, array $moduleStatuses): ?Module
    {
        $moduleJsonPath = $path . '/module.json';

        if (! File::exists($moduleJsonPath)) {
            return null;
        }

        $moduleData = json_decode(File::get($moduleJsonPath), true);

        // Use lowercase name from module.json as the canonical identifier
        $jsonName = strtolower(trim($moduleData['name'] ?? basename($path)));

        return new Module([
            'id' => $jsonName,
            'name' => $jsonName,
            'title' => $moduleData['title'] ?? $moduleData['name'] ?? basename($path),
            'description' => $moduleData['description'] ?? '',
            'icon' => $moduleData['icon'] ?? 'lucide:box',
            'logo_image' => $moduleData['logo_image'] ?? null,
            'banner_image' => $moduleData['banner_image'] ?? null,
            'status' => $moduleStatuses[$jsonName] ?? false,
            'version' => $moduleData['version'] ?? '1.0.0',
            'author' => $moduleData['author'] ?? null,
            'author_url' => $moduleData['author_url'] ?? null,
            'documentation_url' => $moduleData['documentation_url'] ?? null,
            'tags' => $moduleData['keywords'] ?? [],
            'category' => $moduleData['category'] ?? null,
            'priority' => $moduleData['priority'] ?? 0,
        ]);
    }

    /**
     * Get module statuses from the modules_statuses.json file.
     *
     * @return array<string, bool>
     */
    protected function getModuleStatuses(): array
    {
        if (! File::exists($this->modulesStatusesPath)) {
            return [];
        }

        return json_decode(File::get($this->modulesStatusesPath), true) ?? [];
    }

    /**
     * Apply search filter.
     */
    public function search(?string $term): self
    {
        $this->search = $term;

        return $this;
    }

    /**
     * Set searchable fields.
     *
     * @param  array<string>  $fields
     */
    public function searchable(array $fields): self
    {
        $this->searchableFields = $fields;

        return $this;
    }

    /**
     * Apply sorting.
     */
    public function orderBy(string $field, string $direction = 'asc'): self
    {
        $this->sortField = $field;
        $this->sortDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $this;
    }

    /**
     * Filter by a specific field value.
     */
    public function where(string $field, mixed $value): self
    {
        $this->filters[$field] = $value;

        return $this;
    }

    /**
     * Filter by status (enabled/disabled).
     */
    public function whereStatus(?bool $status): self
    {
        if ($status !== null) {
            $this->filters['status'] = $status;
        }

        return $this;
    }

    /**
     * Apply all filters and sorting, then return the processed collection.
     */
    protected function applyFiltersAndSorting(): Collection
    {
        $items = $this->items;

        // Apply search
        if ($this->search) {
            $searchTerm = strtolower($this->search);
            $items = $items->filter(function (Module $module) use ($searchTerm) {
                foreach ($this->searchableFields as $field) {
                    $value = $this->getModuleFieldValue($module, $field);
                    if (is_string($value) && str_contains(strtolower($value), $searchTerm)) {
                        return true;
                    }
                    if (is_array($value)) {
                        foreach ($value as $item) {
                            if (is_string($item) && str_contains(strtolower($item), $searchTerm)) {
                                return true;
                            }
                        }
                    }
                }

                return false;
            });
        }

        // Apply filters
        foreach ($this->filters as $field => $value) {
            $items = $items->filter(function (Module $module) use ($field, $value) {
                $fieldValue = $this->getModuleFieldValue($module, $field);

                if (is_bool($value)) {
                    return $fieldValue === $value;
                }

                return $fieldValue == $value;
            });
        }

        // Apply sorting
        if ($this->sortField) {
            $items = $items->sortBy(function (Module $module) {
                return $this->getModuleFieldValue($module, $this->sortField);
            }, SORT_REGULAR, $this->sortDirection === 'desc');
        }

        return $items->values();
    }

    /**
     * Get a field value from a Module object.
     */
    protected function getModuleFieldValue(Module $module, string $field): mixed
    {
        return $module->{$field} ?? null;
    }

    /**
     * Get paginated results.
     */
    public function paginate(int $perPage = 15, ?int $page = null): LengthAwarePaginator
    {
        $items = $this->applyFiltersAndSorting();
        $page = $page ?? (int) request('page', 1);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Get all items without pagination.
     */
    public function get(): Collection
    {
        return $this->applyFiltersAndSorting();
    }

    /**
     * Get the count of items.
     */
    public function count(): int
    {
        return $this->applyFiltersAndSorting()->count();
    }
}
