<?php

declare(strict_types=1);

namespace App\PageGenerator;

use App\PageGenerator\Contracts\ListPageContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

abstract class AbstractListPage extends AbstractPage implements ListPageContract
{
    protected Request $request;
    protected int $perPage = 10;
    protected bool $showCheckboxes = true;
    protected bool $showSearch = true;
    protected bool $showFilters = true;
    protected bool $showBulkActions = true;
    protected bool $showPagination = true;
    protected bool $showActions = true;
    protected string $emptyMessage = 'No records found';
    protected array $searchableColumns = [];
    protected array $sortableColumns = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct();
    }

    protected function buildContent(array $data): string
    {
        return view('page-generator.list', $data)->render();
    }

    protected function getDefaultView(): string
    {
        return 'page-generator.list';
    }

    protected function prepareViewData(): array
    {
        $data = parent::prepareViewData();

        return array_merge($data, [
            'columns' => $this->getColumns(),
            'data' => $this->getData(),
            'filters' => $this->getFilters(),
            'sortableColumns' => $this->getSortableColumns(),
            'searchableColumns' => $this->getSearchableColumns(),
            'bulkActions' => $this->getBulkActions(),
            'actions' => $this->getActions(),
            'perPage' => $this->getPerPage(),
            'showCheckboxes' => $this->showCheckboxes(),
            'showSearch' => $this->showSearch(),
            'showFilters' => $this->showFilters(),
            'showBulkActions' => $this->showBulkActions(),
            'showPagination' => $this->showPagination(),
            'showActions' => $this->showActions(),
            'emptyMessage' => $this->getEmptyMessage(),
            'searchPlaceholder' => $this->getSearchPlaceholder(),
            'createRoute' => $this->getCreateRoute(),
            'createButtonText' => $this->getCreateButtonText(),
            'showCreateButton' => $this->showCreateButton(),
        ]);
    }

    public function getFilters(): array
    {
        return [];
    }

    public function getSortableColumns(): array
    {
        return $this->sortableColumns;
    }

    public function getSearchableColumns(): array
    {
        return $this->searchableColumns;
    }

    public function getBulkActions(): array
    {
        return [];
    }

    public function getActions(): array
    {
        return [];
    }

    public function getPerPage(): int
    {
        return $this->request->input('per_page', $this->perPage);
    }

    public function showCheckboxes(): bool
    {
        return $this->showCheckboxes && count($this->getBulkActions()) > 0;
    }

    public function showSearch(): bool
    {
        return $this->showSearch && count($this->getSearchableColumns()) > 0;
    }

    public function showFilters(): bool
    {
        return $this->showFilters && count($this->getFilters()) > 0;
    }

    public function showBulkActions(): bool
    {
        return $this->showBulkActions && count($this->getBulkActions()) > 0;
    }

    public function showPagination(): bool
    {
        $data = $this->getData();

        return $this->showPagination && $data instanceof LengthAwarePaginator && $data->hasPages();
    }

    public function showActions(): bool
    {
        return $this->showActions && count($this->getActions()) > 0;
    }

    public function getEmptyMessage(): string
    {
        return __($this->emptyMessage);
    }

    protected function getSearchPlaceholder(): string
    {
        return __('Search...');
    }

    protected function getCreateRoute(): ?string
    {
        return null;
    }

    protected function getCreateButtonText(): string
    {
        return __('Create New');
    }

    protected function showCreateButton(): bool
    {
        return $this->getCreateRoute() !== null;
    }

    protected function applySorting($query)
    {
        $sortField = $this->request->input('sort_field');
        $sortDirection = $this->request->input('sort_direction', 'asc');

        if ($sortField && in_array($sortField, $this->sortableColumns)) {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query;
    }

    protected function applySearch($query)
    {
        $search = $this->request->input('search');

        if ($search && count($this->searchableColumns) > 0) {
            $query->where(function ($q) use ($search) {
                foreach ($this->searchableColumns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        return $query;
    }

    protected function applyFilters($query)
    {
        foreach ($this->getFilters() as $filter) {
            $value = $this->request->input($filter['name']);

            if ($value !== null && $value !== '') {
                if (isset($filter['callback'])) {
                    $query = $filter['callback']($query, $value);
                } else {
                    $query->where($filter['column'] ?? $filter['name'], $value);
                }
            }
        }

        return $query;
    }

    abstract public function getColumns(): array;

    abstract public function getData(): LengthAwarePaginator|Collection;
}
