<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

trait QueryBuilderTrait
{
    /**
     * Apply filters to the query builder.
     */
    public function scopeApplyFilters(Builder $query, array $filters = []): Builder
    {
        // Apply search filter if provided
        if (isset($filters['search']) && ! empty($filters['search'])) {
            $searchColumns = $this->getSearchableColumns();
            $query->where(column: function ($q) use ($searchColumns, $filters) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'LIKE', '%'.$filters['search'].'%');
                }
            });
        }

        // Apply status filter if provided.
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        // Apply role filter if provided.
        if (isset($filters['role']) && ! empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        // Apply date range filter if provided.
        if (isset($filters['date_from']) && ! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && ! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Apply sorting from request.
        $sort = request()->query('sort');
        if ($sort) {
            $direction = 'asc';
            $field = $sort;

            // Check if it's a descending sort (prefixed with -).
            if (strpos($sort, '-') === 0) {
                $direction = 'desc';
                $field = substr($sort, 1);
            }

            // Check if the field is in the excluded sort columns.
            $excludedSortColumns = method_exists($this, 'getExcludedSortColumns')
                ? $this->getExcludedSortColumns()
                : [];

            if (! in_array($field, $excludedSortColumns)) {
                // Handle special sorting cases.
                $methodName = 'sortBy'.ucfirst($field);
                if (method_exists($this, $methodName)) {
                    $this->$methodName($query, $direction);
                } else {
                    // Special case for post_count (legacy support).
                    if ($field === 'post_count') {
                        $query->withCount('posts')->orderBy('posts_count', $direction);
                    } else {
                        $query->orderBy($field, $direction);
                    }
                }
            }
        } else {
            // Default sorting.
            $sortField = $filters['sort_field'] ?? 'created_at';
            $sortDirection = $filters['sort_direction'] ?? 'desc';
            $query->orderBy($sortField, $sortDirection);
        }

        return $query;
    }

    /**
     * Apply pagination to the query.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function scopePaginateData(Builder $query, array $options = []): LengthAwarePaginator
    {
        $perPage = $options['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Get searchable columns for the model.
     * Override this method in your model to define searchable columns.
     */
    protected function getSearchableColumns(): array
    {
        // Default searchable columns, override in model
        return ['name', 'title', 'description'];
    }
}
