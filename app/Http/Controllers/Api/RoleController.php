<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ActionType;
use App\Http\Requests\Role\BulkDeleteRoleRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RolesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RoleController extends ApiController
{
    public function __construct(private readonly RolesService $rolesService)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @tags Roles
     */
    public function index(Request $request): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['role.view']);

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
     * Store a newly created resource in storage.
     *
     * @tags Roles
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
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
     * Display the specified resource.
     *
     * @tags Roles
     */
    public function show(int $id): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['role.view']);

        $role = Role::with('permissions')->find($id);
        if (! $role) {
            return $this->errorResponse('Role not found', 404);
        }

        return $this->resourceResponse(
            new RoleResource($role),
            'Role retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @tags Roles
     */
    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
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
     * Remove the specified resource from storage.
     *
     * @tags Roles
     */
    public function destroy(int $id): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['role.delete']);

        $role = Role::find($id);
        if (! $role) {
            return $this->errorResponse('Role not found', 404);
        }

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
     * Bulk delete roles.
     *
     * @tags Roles
     */
    public function bulkDelete(BulkDeleteRoleRequest $request): JsonResponse
    {
        $roleIds = $request->input('ids');

        // Check if any roles have users assigned
        $rolesWithUsers = Role::whereIn('id', $roleIds)->whereHas('users')->count();
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
