<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Services\PermissionService;
use App\Services\RolesService;
use App\Enums\Hooks\RoleActionHook;
use App\Enums\Hooks\RoleFilterHook;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly RolesService $rolesService,
        private readonly PermissionService $permissionService
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('viewAny', Role::class);

        $this->setBreadcrumbTitle(__('Roles'))
            ->setBreadcrumbIcon('lucide:shield')
            ->setBreadcrumbActionButton(
                route('admin.roles.create'),
                __('New Role'),
                'feather:plus',
                'role.create'
            );

        return $this->renderViewWithBreadcrumbs('backend.pages.roles.index');
    }

    public function create(): Renderable
    {
        $this->authorize('create', Role::class);

        $this->setBreadcrumbTitle(__('New Role'))
            ->setBreadcrumbIcon('lucide:shield')
            ->addBreadcrumbItem(__('Roles'), route('admin.roles.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.roles.create', [
            'roleService' => $this->rolesService,
            'all_permissions' => $this->permissionService->getAllPermissionModels(),
            'permission_groups' => $this->permissionService->getDatabasePermissionGroups(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $data = $this->addHooks(
            $request->validated(),
            RoleActionHook::ROLE_CREATED_BEFORE,
            RoleFilterHook::ROLE_CREATED_BEFORE
        );

        $role = $this->rolesService->createRole($data['name'] ?? $request->name, $data['permissions'] ?? $request->input('permissions', []));

        $role = $this->addHooks(
            $role,
            RoleActionHook::ROLE_CREATED_AFTER,
            RoleFilterHook::ROLE_CREATED_AFTER
        );

        session()->flash('success', __('Role has been created.'));

        return redirect()->route('admin.roles.index');
    }

    public function show(int $id): Renderable|RedirectResponse
    {
        $role = Role::with(['permissions', 'users'])->withCount(['permissions', 'users'])->findOrFail($id);

        $this->authorize('view', $role);

        $this->setBreadcrumbTitle($role->name)
            ->setBreadcrumbIcon('lucide:shield')
            ->addBreadcrumbItem(__('Roles'), route('admin.roles.index'))
            ->setBreadcrumbActionButton(
                route('admin.roles.edit', $role->id),
                __('Edit Role'),
                'feather:edit-2',
                'role.edit'
            );

        return $this->renderViewWithBreadcrumbs('backend.pages.roles.show', [
            'role' => $role,
            'permission_groups' => $this->permissionService->getDatabasePermissionGroups(),
        ]);
    }

    public function edit(int $id): Renderable|RedirectResponse
    {
        $role = $this->rolesService->findRoleById($id);
        if (! $role) {
            session()->flash('error', __('Role not found.'));

            return back();
        }

        $this->authorize('update', $role);

        $this->setBreadcrumbTitle(__('Edit Role'))
            ->setBreadcrumbIcon('lucide:shield')
            ->addBreadcrumbItem(__('Roles'), route('admin.roles.index'))
            ->setBreadcrumbActionButton(
                route('admin.roles.show', $role->id),
                __('View Role'),
                'feather:eye',
                'role.view',
                true
            );

        return $this->renderViewWithBreadcrumbs('backend.pages.roles.edit', [
            'role' => $role,
            'roleService' => $this->rolesService,
            'all_permissions' => $this->permissionService->getAllPermissionModels(),
            'permission_groups' => $this->permissionService->getDatabasePermissionGroups(),
        ]);
    }

    public function update(UpdateRoleRequest $request, int $id): RedirectResponse
    {
        $role = $this->rolesService->findRoleById($id);

        if (! $role) {
            session()->flash('error', __('Role not found.'));

            return back();
        }

        // Check if this is the Superadmin role in demo mode - return 403 directly
        if (config('app.demo_mode') && $role->name === 'Superadmin') {
            abort(403, 'Cannot modify Superadmin role in demo mode.');
        }

        $this->authorize('update', $role);

        $data = $this->addHooks(
            $request->validated(),
            RoleActionHook::ROLE_UPDATED_BEFORE,
            RoleFilterHook::ROLE_UPDATED_BEFORE
        );

        $role = $this->rolesService->updateRole($role, $data['name'] ?? $request->name, $data['permissions'] ?? $request->input('permissions', []));

        $role = $this->addHooks(
            $role,
            RoleActionHook::ROLE_UPDATED_AFTER,
            RoleFilterHook::ROLE_UPDATED_AFTER
        );

        session()->flash('success', __('Role has been updated.'));

        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $role = $this->rolesService->findRoleById($id);

        if (! $role) {
            session()->flash('error', __('Role not found.'));

            return back();
        }

        // Check if this is the Superadmin role in demo mode - return 403 directly
        if (config('app.demo_mode') && $role->name === Role::SUPERADMIN) {
            abort(403, 'Cannot delete Superadmin role in demo mode.');
        }

        $this->authorize('delete', $role);

        $role = $this->addHooks(
            $role,
            RoleActionHook::ROLE_DELETED_BEFORE,
            RoleFilterHook::ROLE_DELETED_BEFORE
        );

        $this->rolesService->deleteRole($role);

        $this->addHooks(
            $role,
            RoleActionHook::ROLE_DELETED_AFTER,
            RoleFilterHook::ROLE_DELETED_AFTER
        );

        session()->flash('success', __('Role has been deleted.'));

        return redirect()->route('admin.roles.index');
    }

    /**
     * Delete multiple roles at once
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->authorize('bulkDelete', Role::class);

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->route('admin.roles.index')
                ->with('error', __('No roles selected for deletion'));
        }

        $ids = $this->addHooks(
            $ids,
            RoleActionHook::ROLE_BULK_DELETED_BEFORE,
            RoleFilterHook::ROLE_BULK_DELETED_BEFORE
        );

        $deletedCount = 0;

        foreach ($ids as $id) {
            $role = $this->rolesService->findRoleById((int) $id);
            if (! $role) {
                continue;
            }
            // Skip Superadmin role.
            if ($role->name === Role::SUPERADMIN) {
                continue;
            }
            $this->rolesService->deleteRole($role);
            $deletedCount++;
        }

        $deletedCount = $this->addHooks(
            $deletedCount,
            RoleActionHook::ROLE_BULK_DELETED_AFTER,
            RoleFilterHook::ROLE_BULK_DELETED_AFTER
        );

        if ($deletedCount > 0) {
            session()->flash('success', __(':count roles deleted successfully', ['count' => $deletedCount]));
        } else {
            session()->flash('error', __('No roles were deleted. Selected roles may include protected roles.'));
        }

        return redirect()->route('admin.roles.index');
    }
}
