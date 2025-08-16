<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesService
{
    public function __construct(private readonly PermissionService $permissionService)
    {
    }

    public function getAllRoles()
    {
        return Role::all();
    }

    public function getRolesDropdown(): array
    {
        return Role::pluck('name', 'id')->toArray();
    }

    public function getPaginatedRoles(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Role::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->paginate(config('settings.default_pagination', $perPage));
    }

    public static function getPermissionsByGroupName(string $group_name): Collection
    {
        return Permission::select('name', 'id')
            ->where('group_name', $group_name)
            ->get();
    }

    /**
     * Get permissions by group
     */
    public function getPermissionsByGroup(string $groupName): ?array
    {
        return $this->permissionService->getPermissionsByGroup($groupName);
    }

    public function roleHasPermissions(Role $role, $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $role->hasPermissionTo($permission->name)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a new role with permissions
     */
    public function createRole(string $name, array $permissions = []): \Spatie\Permission\Models\Role
    {
        /** @var \Spatie\Permission\Models\Role $role */
        $role = Role::create(['name' => $name, 'guard_name' => 'web']);

        if (! empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role;
    }

    public function findRoleById(int $id): ?Role
    {
        $role = Role::findById($id);

        return $role instanceof Role ? $role : null;
    }

    public function findRoleByName(string $name): ?Role
    {
        $role = Role::findByName($name);

        return $role instanceof Role ? $role : null;
    }

    public function updateRole(Role $role, string $name, array $permissions = []): Role
    {
        $role->name = $name;
        $role->save();

        if (! empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role;
    }

    public function deleteRole(Role $role): bool
    {
        return $role->delete();
    }

    /**
     * Count users in a specific role
     *
     * @param  Role|string  $role
     */
    public function countUsersInRole($role): int
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
            if (! $role) {
                return 0;
            }
        }

        return $role->users->count();
    }

    /**
     * Get roles with user counts
     */
    public function getPaginatedRolesWithUserCount(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        // Check if we're sorting by user count
        $sort = request()->query('sort');
        $isUserCountSort = ($sort === 'user_count' || $sort === '-user_count');

        // For user count sorting, we need to handle it separately
        if ($isUserCountSort) {
            // Get all roles matching the search criteria without any sorting
            $query = \App\Models\Role::query();

            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }

            $allRoles = $query->get();

            // Add user count to each role
            foreach ($allRoles as $role) {
                $userCount = $this->countUsersInRole($role);
                $role->setAttribute('user_count', $userCount);
            }

            // Sort the collection by user_count
            $direction = $sort === 'user_count' ? 'asc' : 'desc';
            $sortedRoles = $direction === 'asc'
                ? $allRoles->sortBy('user_count')
                : $allRoles->sortByDesc('user_count');

            // Manually paginate the collection
            $page = request()->get('page', 1);
            $offset = ($page - 1) * $perPage;

            $paginatedRoles = new \Illuminate\Pagination\LengthAwarePaginator(
                $sortedRoles->slice($offset, $perPage)->values(),
                $sortedRoles->count(),
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return $paginatedRoles;
        }

        // For normal sorting by database columns
        $filters = [
            'search' => $search,
            'sort_field' => 'name',
            'sort_direction' => 'asc',
        ];

        $query = \App\Models\Role::applyFilters($filters);
        $roles = $query->paginateData(['per_page' => $perPage]);

        // Add user count to each role
        foreach ($roles->items() as $role) {
            $userCount = $this->countUsersInRole($role);
            $role->setAttribute('user_count', $userCount);
        }

        return $roles;
    }

    /**
     * Create predefined roles with their permissions
     */
    public function createPredefinedRoles(): array
    {
        $roles = [];

        // 1. Superadmin role - has all permissions
        $allPermissionNames = [];
        foreach ($this->permissionService->getAllPermissions() as $group) {
            foreach ($group['permissions'] as $permission) {
                $allPermissionNames[] = $permission;
            }
        }

        $roles['superadmin'] = $this->createRole('Superadmin', $allPermissionNames);

        // 2. Admin role - has almost all permissions except some critical ones
        $adminPermissions = $allPermissionNames;
        $adminExcludedPermissions = [
            'user.delete', // Cannot delete users.
            'user.login_as', // Cannot login as other users.
        ];

        $adminPermissions = array_diff($adminPermissions, $adminExcludedPermissions);
        $roles['admin'] = $this->createRole('Admin', $adminPermissions);

        // 3. Editor role - can manage content but not users/settings
        $editorPermissions = [
            'dashboard.view',
            // Blog permissions
            'blog.create',
            'blog.view',
            'blog.edit',
            // Profile permissions
            'profile.view',
            'profile.edit',
            'profile.update',
            // Translations
            'translations.view',
        ];

        $roles['editor'] = $this->createRole('Editor', $editorPermissions);

        // 4. Subscriber role - basic user role
        $subscriberPermissions = [
            'dashboard.view',
            'profile.view',
            'profile.edit',
            'profile.update',
        ];

        $roles['subscriber'] = $this->createRole('Subscriber', $subscriberPermissions);

        $contactPermissions = [
            'dashboard.view',
            'profile.view',
            'profile.edit',
            'profile.update',
        ];

        $roles['contact'] = $this->createRole('Contact', $contactPermissions);

        return $roles;
    }

    /**
     * Get a specific predefined role's permissions
     */
    public function getPredefinedRolePermissions(string $roleName): array
    {
        $roleName = strtolower($roleName);

        switch ($roleName) {
            case 'superadmin':
                // All permissions
                $allPermissionNames = [];
                foreach ($this->permissionService->getAllPermissions() as $group) {
                    foreach ($group['permissions'] as $permission) {
                        $allPermissionNames[] = $permission;
                    }
                }

                return $allPermissionNames;

            case 'admin':
                // All except some critical permissions
                $adminExcludedPermissions = [
                    'user.delete',
                ];
                $allPermissionNames = [];
                foreach ($this->permissionService->getAllPermissions() as $group) {
                    foreach ($group['permissions'] as $permission) {
                        $allPermissionNames[] = $permission;
                    }
                }

                return array_diff($allPermissionNames, $adminExcludedPermissions);

            case 'editor':
                return [
                    'dashboard.view',
                    'blog.create',
                    'blog.view',
                    'blog.edit',
                    'profile.view',
                    'profile.edit',
                    'profile.update',
                    'translations.view',
                    // Add post and term related permissions for Editor
                    'post.create',
                    'post.view',
                    'post.edit',
                    'term.view',
                    'term.create',
                ];

            case 'subscriber':
            case 'contact ':
                return [
                    'dashboard.view',
                    'profile.view',
                    'profile.edit',
                    'profile.update',
                    'post.view',
                    'term.view',
                ];
            default:
                return [
                    'dashboard.view',
                    'profile.view',
                    'profile.edit',
                    'profile.update',
                    // Add basic content viewing for Subscriber
                    'post.view',
                    'term.view',
                ];
        }
    }

    /**
     * Create a new role (API wrapper)
     */
    public function create(array $data): Role
    {
        return $this->createRole($data['name'], $data['permissions'] ?? []);
    }

    /**
     * Update a role (API wrapper)
     */
    public function update(Role $role, array $data): Role
    {
        return $this->updateRole($role, $data['name'], $data['permissions'] ?? []);
    }
}
