<?php

namespace App\Models;

use App\Traits\QueryBuilderTrait;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use QueryBuilderTrait;

    /**
     * Get searchable columns for the model.
     *
     * @return array
     */
    protected function getSearchableColumns(): array
    {
        return ['name'];
    }
    
    /**
     * Get columns that should be excluded from sorting.
     *
     * @return array
     */
    protected function getExcludedSortColumns(): array
    {
        return ['user_count'];
    }
}
