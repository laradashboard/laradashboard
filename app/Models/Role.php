<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\QueryBuilderTrait;
use App\Observers\RoleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Spatie\Permission\Models\Role as SpatieRole;

#[ObservedBy([RoleObserver::class])]
class Role extends SpatieRole
{
    use QueryBuilderTrait;

    public const SUPERADMIN = 'Superadmin';

    protected $fillable = [
        'name',
        'guard_name',
        'is_super_admin',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_super_admin' => 'boolean',
        ];
    }

    public function isSuperAdminRole(): bool
    {
        return (bool) $this->is_super_admin;
    }
    public const EDITOR = 'Editor';
    public const ADMIN = 'Admin';
    public const SUBSCRIBER = 'Subscriber';

    /**
     * Get searchable columns for the model.
     */
    protected function getSearchableColumns(): array
    {
        return ['name'];
    }

    /**
     * Get columns that should be excluded from sorting.
     */
    protected function getExcludedSortColumns(): array
    {
        return ['user_count'];
    }

    /**
     * Custom sort method for permissions_count
     */
    public static function sortByPermissionsCount($query, string $direction = 'asc'): void
    {
        $query->withCount('permissions')->orderBy('permissions_count', $direction);
    }
}
