<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Contracts\Support\Renderable;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    public function __construct(
        private readonly PermissionService $permissionService
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('viewAny', Permission::class);

        $perPage = config('settings.default_pagination') ?? 10;
        $search = request()->input('search') !== '' ? request()->input('search') : null;

        return view('backend.pages.permissions.index', [
            'permissions' => $this->permissionService->getPaginatedPermissionsWithRoleCount($search, intval($perPage)),
            'breadcrumbs' => [
                'title' => __('Permissions'),
            ],
        ]);
    }

    public function show(int $id): Renderable
    {
        $permission = Permission::findById($id);
        $this->authorize('view', $permission);
        $roles = $this->permissionService->getRolesForPermission($permission);

        return view('backend.pages.permissions.show', [
            'permission' => $permission,
            'roles' => $roles,
            'breadcrumbs' => [
                'title' => __('Permission Details'),
                'items' => [
                    [
                        'label' => __('Permissions'),
                        'url' => route('admin.permissions.index'),
                    ],
                ],
            ],
        ]);
    }
}
