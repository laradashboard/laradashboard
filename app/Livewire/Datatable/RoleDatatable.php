<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Enums\Hooks\RoleActionHook;
use App\Enums\Hooks\RoleFilterHook;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\QueryBuilder;

class RoleDatatable extends Datatable
{
    public string $model = Role::class;

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by role name...');
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
                'id' => 'users',
                'title' => __('Users'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'users_count',
            ],
            [
                'id' => 'permissions',
                'title' => __('Permissions'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'permissions_count',
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
            ->withCount('permissions')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                });
            });

        return $this->sortQuery($query);
    }

    public function sortQuery(QueryBuilder $query): QueryBuilder|Builder
    {
        if (! $this->sort) {
            return $query;
        }

        if (! empty($this->sort) && $this->sort === 'permissions_count') {
            return $query->withCount('permissions')->orderBy('permissions_count', $this->direction);
        } elseif (! empty($this->sort) && $this->sort === 'users_count') {
            return $query->withCount('users')->orderBy('users_count', $this->direction);
        }

        return parent::sortQuery($query);
    }

    public function renderNameColumn(Role $role): string
    {
        return "
            <a href=\"" . route('admin.roles.show', $role) . "\" class='text-primary hover:underline'>" . $role->name . "</a>
            <p class='text-sm text-gray-500'>" . $role->permissions_count . " " . __('permissions') . "</p>
        ";
    }

    public function renderUsersColumn(Role $role): string
    {
        $url = route('admin.users.index', ['role' => $role->name]);
        return '<a title="' . __('View Users') . '" href="' . $url . '" class="text-primary hover:underline">' . $role->users_count . '</a>';
    }

    public function renderPermissionsColumn(Role $role): View
    {
        return view('backend.pages.roles.partials.permissions', compact('role'));
    }

    protected function handleBulkDelete(array $ids): int
    {
        $roles = Role::whereIn('id', $ids)->get();
        $deletedCount = 0;
        foreach ($roles as $role) {
            if ($role->name === Role::SUPERADMIN) {
                continue;
            }

            $this->authorize('delete', $role);

            $this->authorize('delete', $role);

            $this->addHooks(
                $role,
                RoleActionHook::ROLE_DELETED_BEFORE,
                RoleFilterHook::ROLE_DELETED_BEFORE
            );

            $role->delete();

            $this->addHooks(
                $role,
                RoleActionHook::ROLE_DELETED_AFTER,
                RoleFilterHook::ROLE_DELETED_AFTER
            );

            $deletedCount++;
        }

        return $deletedCount;
    }

    public function handleRowDelete(Model|Role $role): bool
    {
        if ($role->name === Role::SUPERADMIN) {
            throw new \Exception(__('You cannot delete a :role role.', ['role' => Role::SUPERADMIN]));
        }

        $this->authorize('delete', $role);

        $this->addHooks(
            $role,
            RoleActionHook::ROLE_DELETED_BEFORE,
            RoleFilterHook::ROLE_DELETED_BEFORE
        );

        $deleted = $role->delete();

        $this->addHooks(
            $role,
            RoleActionHook::ROLE_DELETED_AFTER,
            RoleFilterHook::ROLE_DELETED_AFTER
        );

        return $deleted;
    }
}
