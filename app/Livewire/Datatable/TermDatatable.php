<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Models\Term;
use Spatie\QueryBuilder\QueryBuilder;

class TermDatatable extends Datatable
{
    public string $taxonomy;
    public string $model = Term::class;
    public array $disabledRoutes = ['view'];

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by :taxonomy name...', ['taxonomy' => $this->taxonomy]);
    }

    public function getColumnVisibilityStorageKey(): string
    {
        return parent::getColumnVisibilityStorageKey() . '-' . $this->taxonomy;
    }

    protected function getNoResultsMessage(): string
    {
        return __('No :items found.', ['items' => ucfirst($this->taxonomy)]);
    }

    protected function getHeaders(): array
    {
        return [
            [
                'id' => 'name',
                'title' => __('Name'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'name',
            ],
            [
                'id' => 'parent',
                'title' => __('Parent'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'parent_id',
            ],
            [
                'id' => 'posts_count',
                'title' => __('Posts'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'posts_count',
            ],
            [
                'id' => 'actions',
                'title' => __('Actions'),
                'width' => null,
                'sortable' => false,
                'is_action' => true,
            ],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        $query = QueryBuilder::for($this->model)
            ->where('taxonomy', $this->taxonomy)
            ->with('parent')
            ->withCount('posts')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                });
            });

        return $this->sortQuery($query);
    }

    public function renderParentColumn($term): string
    {
        return $term->parent->name ?? '-';
    }

    protected function getRouteParameters(): array
    {
        return ['taxonomy' => $this->taxonomy];
    }

    protected function getItemRouteParameters($item): array
    {
        return [
            'taxonomy' => $this->taxonomy,
            'term' => $item->id,
        ];
    }

    public function renderNameColumn($term): string
    {
        return "<a class='text-primary hover:underline'  href=\"".$this->getEditRouteUrl($term)."\">{$term->name}</a>";
    }
}
