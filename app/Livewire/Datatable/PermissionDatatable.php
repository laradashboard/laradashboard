<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Models\Permission;
use Spatie\QueryBuilder\QueryBuilder;

class PermissionDatatable extends Datatable
{
    public bool $enableCheckbox = false;
    public string $model = Permission::class;
    public array $disabledRoutes = ['edit', 'delete'];

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by permission name...');
    }

    protected function getPermissions(): array
    {
        return [
            'view' => 'role.view',
        ];
    }

    protected function getHeaders(): array
    {
        return [
            [
                'id' => 'name',
                'title' => __('Name'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'name',
            ],
            [
                'id' => 'group_name',
                'title' => __('Group'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'group_name',
            ],
            [
                'id' => 'roles',
                'title' => __('Roles'),
                'width' => null,
                'sortable' => false,
                'sortBy' => 'roles_count',
            ],
            [
                'id' => 'actions',
                'title' => __('Actions'),
                'width' => null,
                'sortable' => false,
                'is_action' => true,
            ],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        $query = QueryBuilder::for($this->model)
            ->withCount('users')
            ->withCount('roles');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            });
        }

        return $this->sortQuery($query);
    }

    public function renderNameColumn(Permission $permission): string
    {
        return "<a href=\"" . route('admin.permissions.show', $permission) . "\" class='text-primary hover:underline'>" . $permission->name . "</a>
            <p class='text-sm text-gray-500'>" . $permission->roles_count . " " . __('roles') . "</p>";
    }

    public function renderGroupNameColumn(Permission $permission): string
    {
        return (isset($permission->group_name) ? "<span class='badge'>" . ucfirst($permission->group_name) . "</span>" : '-');
    }

    public function renderRolesColumn(Permission $permission): string
    {
        if ($permission->roles_count === 0) {
            return "<span class='text-gray-400'>" . __('No roles assigned') . "</span>";
        }

        $roles = $permission->roles()->pluck('name', 'id')->toArray();
        $html = "<div class='flex items-center gap-1'>";
        foreach ($roles as $roleId => $roleName) {
            $html .= "<a href='" . route('admin.roles.edit', $roleId) . "' class='text-primary hover:underline badge'>" . $roleName . "</a>";
        }
        $html .= "</div>";

        return $html;
    }
}
