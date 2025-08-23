<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Permission;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    /**
     * Get all permissions organized by groups
     */
    public function getAllPermissions(): array
    {
        $permissions = [
            [
                'group_name' => 'dashboard',
                'permissions' => [
                    'dashboard.view',
                ],
            ],
            [
                'group_name' => 'blog',
                'permissions' => [
                    'blog.create',
                    'blog.view',
                    'blog.edit',
                    'blog.delete',
                    'blog.approve',
                ],
            ],
            [
                'group_name' => 'user',
                'permissions' => [
                    'user.create',
                    'user.view',
                    'user.edit',
                    'user.delete',
                    'user.approve',
                    'user.login_as',
                ],
            ],
            [
                'group_name' => 'role',
                'permissions' => [
                    'role.create',
                    'role.view',
                    'role.edit',
                    'role.delete',
                    'role.approve',
                ],
            ],
            [
                'group_name' => 'module',
                'permissions' => [
                    'module.create',
                    'module.view',
                    'module.edit',
                    'module.delete',
                ],
            ],
            [
                'group_name' => 'profile',
                'permissions' => [
                    'profile.view',
                    'profile.edit',
                    'profile.delete',
                    'profile.update',
                ],
            ],
            [
                'group_name' => 'monitoring',
                'permissions' => [
                    'pulse.view',
                    'actionlog.view',
                ],
            ],
            [
                'group_name' => 'settings',
                'permissions' => [
                    'settings.view',
                    'settings.edit',
                ],
            ],
            [
                'group_name' => 'translations',
                'permissions' => [
                    'translations.view',
                    'translations.edit',
                ],
            ],
            [
                'group_name' => 'post',
                'permissions' => [
                    'post.create',
                    'post.view',
                    'post.edit',
                    'post.delete',
                    'term.create',
                    'term.view',
                    'term.edit',
                    'term.delete',
                ],
            ],
            [
                'group_name' => 'media',
                'permissions' => [
                    'media.create',
                    'media.view',
                    'media.edit',
                    'media.delete',
                ],
            ],
            [
                'group_name' => 'ai_content',
                'permissions' => [
                    'ai_content.generate',
                ],
            ],
        ];

        return $permissions;
    }

    /**
     * Get a specific set of permissions by group name
     */
    public function getPermissionsByGroup(string $groupName): ?array
    {
        $permissions = $this->getAllPermissions();

        foreach ($permissions as $permissionGroup) {
            if ($permissionGroup['group_name'] === $groupName) {
                return $permissionGroup['permissions'];
            }
        }

        return null;
    }

    /**
     * Get all permission group names
     */
    public function getPermissionGroups(): array
    {
        $groups = [];
        foreach ($this->getAllPermissions() as $permission) {
            $groups[] = $permission['group_name'];
        }

        return $groups;
    }

    /**
     * Get all permission models from a database
     */
    public function getAllPermissionModels(): Collection
    {
        return Permission::all();
    }

    /**
     * Get permissions by group name from a database
     */
    public function getPermissionModelsByGroup(string $group_name): Collection
    {
        return Permission::select('name', 'id')
            ->where('group_name', $group_name)
            ->get();
    }

    /**
     * Get permission groups from database
     */
    public function getDatabasePermissionGroups(): Collection
    {
        $groups = Permission::select('group_name as name')
            ->groupBy('group_name')
            ->get();

        // Add the permissions to each group.
        foreach ($groups as $group) {
            $group->setAttribute('permissions', $this->getPermissionModelsByGroup($group->name));
        }

        return $groups;
    }

    /**
     * Create all permissions from the definitions
     *
     * @return array Created permissions
     */
    public function createPermissions(): array
    {
        $createdPermissions = [];
        $permissions = $this->getAllPermissions();

        foreach ($permissions as $permissionGroup) {
            $groupName = $permissionGroup['group_name'];

            foreach ($permissionGroup['permissions'] as $permissionName) {
                $permission = $this->findOrCreatePermission($permissionName, $groupName);
                $createdPermissions[] = $permission;
            }
        }

        return $createdPermissions;
    }

    /**
     * Find or create a permission
     */
    public function findOrCreatePermission(string $name, string $groupName): Permission
    {
        return Permission::firstOrCreate(
            ['name' => $name],
            [
                'name' => $name,
                'group_name' => $groupName,
                'guard_name' => 'web',
            ]
        );
    }

    /**
     * Get all permission objects by their names
     */
    public function getPermissionsByNames(array $permissionNames): array
    {
        return Permission::whereIn('name', $permissionNames)->get()->all();
    }

    /**
     * Get paginated permissions with role count
     */
    public function getPaginatedPermissionsWithRoleCount(?string $search, ?int $perPage): LengthAwarePaginator
    {
        // Check if we're sorting by role count
        $sort = request()->query('sort');
        $isRoleCountSort = ($sort === 'role_count' || $sort === '-role_count');

        // For role count sorting, we need to handle it separately
        if ($isRoleCountSort) {
            // Get all permissions matching the search criteria without any sorting
            $query = Permission::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('group_name', 'like', '%' . $search . '%');
                });
            }

            $allPermissions = $query->get();

            // Add role count to each permission
            foreach ($allPermissions as $permission) {
                $roles = $permission->roles()->get();
                $roleCount = $roles->count();
                $rolesList = $roles->pluck('name')->take(5)->implode(', ');

                if ($roleCount > 5) {
                    $rolesList .= ', ...';
                }

                // Use dynamic properties instead of undefined properties
                $permission->setAttribute('role_count', $roleCount);
                $permission->setAttribute('roles_list', $rolesList);
            }

            // Sort the collection by role_count
            $direction = $sort === 'role_count' ? 'asc' : 'desc';
            $sortedPermissions = $direction === 'asc'
                ? $allPermissions->sortBy('role_count')
                : $allPermissions->sortByDesc('role_count');

            // Manually paginate the collection
            $page = request()->get('page', 1);
            $offset = ($page - 1) * ($perPage ?? config('settings.default_pagination'));
            $perPageValue = $perPage ?? config('settings.default_pagination');

            $paginatedPermissions = new \Illuminate\Pagination\LengthAwarePaginator(
                $sortedPermissions->slice($offset, $perPageValue)->values(),
                $sortedPermissions->count(),
                $perPageValue,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return $paginatedPermissions;
        }

        // For normal sorting by database columns
        $filters = [
            'search' => $search,
            'sort_field' => 'name',
            'sort_direction' => 'asc',
        ];

        $query = Permission::applyFilters($filters);
        $permissions = $query->paginateData(['per_page' => $perPage ?? config('settings.default_pagination')]);

        // Add role count and roles information to each permission.
        foreach ($permissions->items() as $permission) {
            $roles = $permission->roles()->get();
            $roleCount = $roles->count();
            $rolesList = $roles->pluck('name')->take(5)->implode(', ');

            if ($roleCount > 5) {
                $rolesList .= ', ...';
            }

            // Use dynamic properties instead of undefined properties
            $permission->setAttribute('role_count', $roleCount);
            $permission->setAttribute('roles_list', $rolesList);
        }

        return $permissions;
    }

    /**
     * Get roles for permission
     */
    public function getRolesForPermission(SpatiePermission $permission): Collection
    {
        return $permission->roles()->get();
    }

    /**
     * Get permission by ID
     */
    public function getPermissionById(int $id): ?SpatiePermission
    {
        return SpatiePermission::find($id);
    }

    /**
     * Get all permissions with optional search and group filter
     */
    public function getAllPermissionsWithFilters(?string $search = null, ?string $groupName = null): Collection
    {
        $query = SpatiePermission::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($groupName) {
            $query->where('group_name', $groupName);
        }

        return $query->get();
    }
}
