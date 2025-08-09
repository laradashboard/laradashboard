<?php

namespace App\Models;

use App\Concerns\QueryBuilderTrait;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use QueryBuilderTrait;

    /**
     * Get searchable columns for the model.
     */
    protected function getSearchableColumns(): array
    {
        return ['name', 'group_name'];
    }

    /**
     * Get columns that should be excluded from sorting.
     */
    protected function getExcludedSortColumns(): array
    {
        return ['role_count'];
    }
}
