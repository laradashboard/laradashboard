<?php

namespace App\Models;

use App\Traits\QueryBuilderTrait;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use QueryBuilderTrait;

    /**
     * Get searchable columns for the model.
     *
     * @return array
     */
    protected function getSearchableColumns(): array
    {
        return ['name', 'group_name'];
    }
    
    /**
     * Get columns that should be excluded from sorting.
     *
     * @return array
     */
    protected function getExcludedSortColumns(): array
    {
        return ['role_count'];
    }
}
