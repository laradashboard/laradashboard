<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Contracts\Support\Renderable;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionService $permissionService
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('viewAny', Permission::class);

        $this->setBreadcrumbTitle(__('Permissions'))
            ->setBreadcrumbIcon('lucide:key');

        return $this->renderViewWithBreadcrumbs('backend.pages.permissions.index');
    }

    public function show(Permission $permission): Renderable
    {
        $this->authorize('view', $permission);

        $this->setBreadcrumbTitle(__('Permission Details'))
            ->setBreadcrumbIcon('lucide:key')
            ->addBreadcrumbItem(__('Permissions'), route('admin.permissions.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.permissions.show', [
            'permission' => $permission,
            'roles' => $this->permissionService->getRolesForPermission($permission),
        ]);
    }
}
