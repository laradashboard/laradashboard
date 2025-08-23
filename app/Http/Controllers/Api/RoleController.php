<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ActionType;
use App\Http\Requests\Role\BulkDeleteRoleRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RolesService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends ApiController
{
    public function __construct(private readonly RolesService $rolesService)
    {
    }

    /**
     * Roles list.
     *
     * @tags Roles
     */
    #[QueryParameter('per_page', description: 'Number of roles per page.', type: 'int', default: 15, example: 20)]
    #[QueryParameter('search', description: 'Search term for filtering roles by name.', type: 'string', example: 'admin')]
    #[QueryParameter('sort', description: 'Sort roles by field (prefix with - for descending).', type: 'string', example: '-created_at')]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $search = $request->input('search');
        $perPage = (int) ($request->input('per_page') ?? config('settings.default_pagination', 10));

        $roles = $this->rolesService->getPaginatedRolesWithUserCount($search, $perPage);

        return $this->resourceResponse(
            RoleResource::collection($roles)->additional([
                'meta' => [
                    'pagination' => [
                        'current_page' => $roles->currentPage(),
                        'last_page' => $roles->lastPage(),
                        'per_page' => $roles->perPage(),
                        'total' => $roles->total(),
                    ],
                ],
            ]),
            'Roles retrieved successfully'
        );
    }

    /**
     * Create Role.
     *
     * @tags Roles
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $role = $this->rolesService->create($request->validated());

        $this->storeActionLog(
            ActionType::CREATED,
            ['role' => $role->toArray()],
        );

        return $this->resourceResponse(
            new RoleResource($role->load('permissions')),
            'Role created successfully',
            201
        );
    }

    /**
     * Show Role.
     *
     * @tags Roles
     */
    public function show(int $id): JsonResponse
    {
        $role = Role::with('permissions')->find($id);
        if (! $role) {
            return $this->errorResponse('Role not found', 404);
        }

        $this->authorize('view', $role);

        return $this->resourceResponse(
            new RoleResource($role),
            'Role retrieved successfully'
        );
    }

    /**
     * Update Role.
     *
     * @tags Roles
     */
    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        if (! $role) {
            return $this->errorResponse('Role not found', 404);
        }
        $this->authorize('update', $role);

        $updatedRole = $this->rolesService->update($role, $request->validated());

        $this->storeActionLog(
            ActionType::UPDATED,
            ['role' => $updatedRole->toArray()],
        );

        return $this->resourceResponse(
            new RoleResource($updatedRole->load('permissions')),
            'Role updated successfully'
        );
    }

    /**
     * Delete Role.
     *
     * @tags Roles
     */
    public function destroy(int $id): JsonResponse
    {
        $role = Role::find($id);
        if (! $role) {
            return $this->errorResponse('Role not found', 404);
        }

        $this->authorize('delete', $role);

        if ($role->users()->count() > 0) {
            return $this->errorResponse('Cannot delete role with assigned users', 400);
        }

        $role->delete();

        $this->storeActionLog(
            ActionType::DELETED,
            ['role' => $role->toArray()],
        );

        return $this->successResponse(null, 'Role deleted successfully');
    }

    /**
     * Bulk Delete Roles.
     *
     * @tags Roles
     */
    public function bulkDelete(BulkDeleteRoleRequest $request): JsonResponse
    {
        $this->authorize('bulkDelete', Role::class);

        $roleIds = $request->input('ids');

        $rolesWithUsers = Role::whereIn('id', $roleIds)
            ->whereExists(function ($query) {
                $query->select('id')
                    ->from('model_has_roles')
                    ->whereColumn('model_has_roles.role_id', 'roles.id')
                    ->where('model_has_roles.model_type', config('permission.models.user', 'App\\Models\\User'));
            })
            ->count();

        if ($rolesWithUsers > 0) {
            return $this->errorResponse('Cannot delete roles with assigned users', 400);
        }

        $deletedCount = Role::whereIn('id', $roleIds)->delete();

        $this->storeActionLog(
            ActionType::BULK_DELETED,
            ['role_ids' => $roleIds],
            'Bulk deleted roles'
        );

        return $this->successResponse(
            ['deleted_count' => $deletedCount],
            $deletedCount . " roles deleted successfully"
        );
    }
}
