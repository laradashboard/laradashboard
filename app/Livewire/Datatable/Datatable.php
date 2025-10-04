<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Concerns\Datatable\HasDatatableActionItems;
use App\Concerns\Datatable\HasDatatableDelete;
use App\Concerns\Datatable\HasDatatableGenerator;
use App\Concerns\Hookable;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Str;

abstract class Datatable extends Component
{
    use WithPagination;
    use HasDatatableActionItems;
    use HasDatatableDelete;
    use HasDatatableGenerator;
    use Hookable;

    public string $model = '';
    public string $search = '';
    public string $searchbarPlaceholder = '';
    public string $newResourceLinkPermission = '';
    public string $newResourceLinkRouteName = '';
    public string $newResourceLinkLabel = '';
    public string $sort = 'created_at';
    public string $direction = 'desc';
    public int $page = 1;
    public int|string $perPage = 10;
    public array $perPageOptions = [];
    public int $paginateOnEachSlide = 0;
    public array $filters = [];
    public $customFilters = null;
    public array $permissions = [];
    public array $selectedItems = [];
    public array $disabledRoutes = [];
    public bool $enableCheckbox = true;
    public bool $enablePagination = true;
    public string $noResultsMessage = '';
    public string $customNoResultsMessage = '';
    public array $headers = [];

    public const QUERY_STRING_DEFAULTS = [
        'search' => ['except' => ''],
        'sort' => ['except' => 'created_at'],
        'direction' => ['except' => 'asc'],
        'page' => ['except' => 1],
        'perPage' => ['except' => 10],
    ];

    public array $queryString = self::QUERY_STRING_DEFAULTS;

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy(string $field = '')
    {
        if ($this->sort === $field) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->direction = 'asc';
        }
    }

    public function sortQuery(QueryBuilder $query): QueryBuilder|Builder
    {
        if ($this->sort) {
            return $query->orderBy($this->sort, $this->direction);
        }

        return $query;
    }

    public function placeholder(): Renderable
    {
        return view('components.datatable.skeleton');
    }

    public function mount(): void
    {
        if (empty($this->getModelClass())) {
            throw new \Exception('Model class is not defined in the datatable component.');
        }

        $this->searchbarPlaceholder = $this->getSearchbarPlaceholder();
        $this->filters = $this->getFilters();
        $this->setActionLabels();
        $this->newResourceLinkPermission = $this->getNewResourceLinkPermission();
        $this->newResourceLinkRouteName = $this->getNewResourceLinkRouteName();
        $this->newResourceLinkLabel = $this->getNewResourceLinkLabel();
        $this->perPageOptions = $this->getPerPageOptions();
        $this->headers = $this->getHeaders();
        $this->noResultsMessage = $this->getNoResultsMessage();
        $this->customNoResultsMessage = $this->getCustomNoResultsMessage();
        $this->perPage = request()->per_page ?? config('settings.default_pagination', 10);
        $this->paginateOnEachSlide = 0;
    }

    public function renderBeforeSearchbar(): string|Renderable
    {
        return '';
    }

    public function renderAfterSearchbar(): string|Renderable
    {
        return '';
    }

    protected function getNoResultsMessage(): string
    {
        return __('No :items found.', ['items' => $this->getModelNamePlural()]);
    }

    protected function getCustomNoResultsMessage(): string
    {
        return $this->customNoResultsMessage ?? '';
    }

    protected function getModelClass(): string
    {
        return $this->model;
    }

    protected function getPerPageOptions(): array
    {
        return [10, 20, 50, 100, __('All')];
    }

    protected function getNewResourceLinkPermission(): string
    {
        return $this->getPermissions()['create'] ?? '';
    }

    protected function getNewResourceLinkRouteName(): string
    {
        return $this->getRoutes()['create'] ?? '';
    }

    protected function getNewResourceLinkLabel(): string
    {
        return __('New :model', ['model' => $this->getModelNameSingular()]);
    }

    protected function getFilters(): array
    {
        return [];
    }

    protected function getSnakeCaseModel(): string
    {
        return Str::snake(class_basename($this->getModelClass()));
    }

    public function getModelNameSingular(): string
    {
        $class = class_basename($this->getModelClass());

        // Insert spaces before capital letters (except the first)
        return trim(preg_replace('/(?<!^)([A-Z])/', ' $1', $class));
    }

    protected function getModelNamePlural(): string
    {
        return str($this->getModelNameSingular())->plural()->toString();
    }

    protected function getPermissions(): array
    {
        $snakeCaseModel = $this->getSnakeCaseModel();

        return [
            'create' => $snakeCaseModel . '.create',
            'view' => $snakeCaseModel . '.view',
            'edit' => $snakeCaseModel . '.edit',
            'delete' => $snakeCaseModel . '.delete',
        ];
    }

    protected function getRouteParameters(): array
    {
        return [];
    }

    protected function getItemRouteParameters($item): array
    {
        // For standard Laravel resource routes, the parameter name is the singular model name.
        $modelName = strtolower(class_basename($this->getModelClass()));
        $baseParams = $this->getRouteParameters();

        // If we have custom route parameters (like postType, taxonomy), use id
        // Otherwise use the model name for standard resource routes.
        if (! empty($baseParams)) {
            return array_merge($baseParams, ['id' => $item->id]);
        }

        return [$modelName => $item->id];
    }

    protected function getRouteUrl(string $routeName, array $parameters = []): string
    {
        if (empty($parameters)) {
            return route($routeName);
        }

        return route($routeName, $parameters);
    }

    public function getCreateRouteUrl(): string
    {
        $routes = $this->getRoutes();
        if (! isset($routes['create'])) {
            return '';
        }

        return $this->getRouteUrl($routes['create'], $this->getRouteParameters());
    }

    public function getViewRouteUrl($item): string
    {
        $routes = $this->getRoutes();
        if (! isset($routes['view'])) {
            return '';
        }

        return $this->getRouteUrl($routes['view'], $this->getItemRouteParameters($item));
    }

    public function getEditRouteUrl($item): string
    {
        $routes = $this->getRoutes();
        if (! isset($routes['edit'])) {
            return '';
        }

        return $this->getRouteUrl($routes['edit'], $this->getItemRouteParameters($item));
    }

    public function getDeleteRouteUrl($item): string
    {
        $routes = $this->getRoutes();
        if (! isset($routes['delete'])) {
            return '';
        }

        return $this->getRouteUrl($routes['delete'], $this->getItemRouteParameters($item));
    }

    public function getRoutes(): array
    {
        $routes = [
            'create' => 'admin.' . Str::lower($this->getModelNamePlural()) . '.create',
            'view' => 'admin.' . Str::lower($this->getModelNamePlural()) . '.show',
            'edit' => 'admin.' . Str::lower($this->getModelNamePlural()) . '.edit',
            'delete' => 'admin.' . Str::lower($this->getModelNamePlural()) . '.destroy',
        ];

        // Remove routes if any of them doesn't exist.
        foreach ($routes as $key => $route) {
            if (! Route::has($route)) {
                unset($routes[$key]);
            }
        }

        // Exclude the disabled routes.
        if (! empty($this->disabledRoutes)) {
            foreach ($this->disabledRoutes as $disabledRoute) {
                unset($routes[$disabledRoute]);
            }
        }

        return $routes;
    }

    protected function getSettingsPaginatorUi(): string
    {
        return config('settings.default_pagination_ui', 'default');
    }

    protected function getPaginatedData($query)
    {
        $paginationUi = $this->getSettingsPaginatorUi();
        $perPage = $this->perPage == __('All') ? 999999 : (int) $this->perPage;

        switch ($paginationUi) {
            case 'cursor':
                return $query->cursorPaginate($perPage);
            case 'simple':
                return $query->simplePaginate($perPage)->onEachSide($this->paginateOnEachSlide);
            case 'default':
            default:
                return $query->paginate($perPage)->onEachSide($this->paginateOnEachSlide);
        }
    }

    protected function getData(): CursorPaginator|LengthAwarePaginator|Paginator
    {
        return$this->getPaginatedData($this->buildQuery());
    }

    protected function buildQuery(): QueryBuilder
    {
        $query = QueryBuilder::for($this->getModelClass());

        if ($this->search) {
            $query->where(function ($q) {
                foreach ($this->headers as $header) {
                    if (isset($header['searchable']) && $header['searchable'] === true) {
                        $q->orWhere($header['sortBy'] ?? $header['id'], 'like', '%' . $this->search . '%');
                    }
                }
            });
        }

        foreach ($this->filters as $filter) {
            if (! empty($filter['selected'])) {
                $query->where($filter['id'], $filter['selected']);
            }
        }

        // Auto-include relationships if specified.
        if (! empty($this->relationships)) {
            $query->with($this->relationships);
        }

        // Apply sorting
        if ($this->sort) {
            $query->orderBy($this->sort, $this->direction);
        }

        return $query;
    }

    public function render(): Renderable
    {
        $this->headers = $this->getHeaders();

        return view('backend.livewire.datatable.datatable', [
            'headers' => $this->headers,
            'data' => $this->getData(),
            'perPage' => $this->perPage,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }

    public function renderIdCell($item): string
    {
        return array_key_exists('id', $item->getAttributes()) ? (string) $item->id : '';
    }

    public function renderCreatedAtColumn($item): string
    {
        if (! array_key_exists('created_at', $item->getAttributes()) || ! $item->created_at) {
            return '';
        }

        $short = $item->created_at->format('d M Y');
        $full = $item->created_at->format('Y-m-d H:i:s');

        return '<span class="text-sm" title="' . e($full) . '">' . e($short) . '</span>';
    }

    public function renderUpdatedAtColumn($item): string
    {
        if (! array_key_exists('updated_at', $item->getAttributes()) || ! $item->updated_at) {
            return '';
        }

        $short = $item->updated_at->format('d M Y');
        $full = $item->updated_at->format('Y-m-d H:i:s');
        return '<span class="text-sm" title="' . e($full) . '">' . e($short) . '</span>';
    }
}
