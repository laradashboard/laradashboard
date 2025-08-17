<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Concerns\HasDataTableFeatures;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

class RoleTable extends PowerGridComponent
{
    use HasDataTableFeatures, WithExport;

    public string $tableName = 'role-table';

    public function datasource(): Builder
    {
        $query = Role::query()
            ->withCount(['users', 'permissions'])
            ->select('roles.*');

        return $this->applyQueryFilters($query);
    }

    public function relationSearch(): array
    {
        return [
            'permissions' => ['name'],
            'users' => ['first_name', 'last_name', 'email'],
        ];
    }

    public function columns(): array
    {
        return $this->getExtensibleColumns();
    }

    public function filters(): array
    {
        return $this->getExtensibleFilters();
    }

    public function actions(): array
    {
        return $this->getExtensibleActions();
    }

    // Implementation of abstract methods from HasDataTableFeatures

    protected function getBaseColumns(): array
    {
        return [
            $this->formatIdColumn(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    return '<div class="flex flex-col">
                        <span class="font-medium text-gray-900 dark:text-white">' . ucfirst($value) . '</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">' . $row->guard_name . '</span>
                    </div>';
                }),

            Column::make('Users Count', 'users_count')
                ->sortable()
                ->format(function ($value) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">' . $value . ' users</span>';
                }),

            Column::make('Permissions Count', 'permissions_count')
                ->sortable()
                ->format(function ($value) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">' . $value . ' permissions</span>';
                }),

            $this->formatCreatedAtColumn(),
        ];
    }

    protected function getBaseActions(): array
    {
        $actions = [];

        if ($this->canPerformAction('edit')) {
            $actions[] = Button::make('edit', 'Edit')
                ->class('btn-sm btn-secondary inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500')
                ->icon('heroicon-o-pencil')
                ->route('admin.roles.edit', fn ($row) => $row->id);
        }

        if ($this->canPerformAction('view')) {
            $actions[] = Button::make('view', 'View Permissions')
                ->class('btn-sm btn-info inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-300 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500')
                ->icon('heroicon-o-eye')
                ->route('admin.roles.show', fn ($row) => $row->id);
        }

        if ($this->canPerformAction('delete')) {
            $actions[] = Button::make('delete', 'Delete')
                ->class('btn-sm btn-danger inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-300 rounded-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500')
                ->icon('heroicon-o-trash')
                ->confirm('Are you sure you want to delete this role? This action cannot be undone.')
                ->method('deleteRole')
                ->can(fn ($row) => $row->name !== 'superadmin');
        }

        return $actions;
    }

    protected function getBaseFilters(): array
    {
        return [
            Filter::inputText('search')
                ->placeholder('Search roles...'),

            Filter::select('guard_name')
                ->dataSource([
                    ['name' => 'Web', 'value' => 'web'],
                    ['name' => 'API', 'value' => 'api'],
                ])
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by guard'),

            Filter::datepicker('created_at')
                ->label('Created Date'),
        ];
    }

    protected function applyBaseQueryFilters($query)
    {
        // Apply search filter
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('guard_name', 'like', "%{$search}%");
            });
        }

        // Apply guard filter
        if ($guard = request('guard_name')) {
            $query->where('guard_name', $guard);
        }

        return $query;
    }

    protected function getHookPrefix(): string
    {
        return 'role';
    }

    protected function getRouteName(): string
    {
        return 'roles';
    }

    protected function getModelClass(): string
    {
        return Role::class;
    }

    protected function getPermissionPrefix(): string
    {
        return 'role';
    }

    /**
     * Custom delete method for roles
     */
    public function deleteRole(int $id): void
    {
        $role = Role::findOrFail($id);

        // Prevent deletion of superadmin role
        if ($role->name === 'superadmin') {
            $this->dispatchBrowserEvent('show-message', [
                'type' => 'error',
                'message' => __('The superadmin role cannot be deleted.')
            ]);
            return;
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            $this->dispatchBrowserEvent('show-message', [
                'type' => 'error',
                'message' => __('Cannot delete role that has users assigned to it.')
            ]);
            return;
        }

        // Apply hooks
        $role = ld_apply_filters('role_delete_before', $role);
        $role->delete();
        ld_do_action('role_delete_after', $role);

        $this->dispatchBrowserEvent('show-message', [
            'type' => 'success',
            'message' => __('Role deleted successfully.')
        ]);

        $this->fillData();
    }

    /**
     * Override bulk delete for role-specific logic
     */
    public function bulkDelete(): void
    {
        $this->validate([
            'checkboxValues' => 'required|array|min:1',
        ]);

        $roles = Role::whereIn('id', $this->checkboxValues)
            ->where('name', '!=', 'superadmin')
            ->whereDoesntHave('users')
            ->get();

        if ($roles->isEmpty()) {
            $this->dispatchBrowserEvent('show-message', [
                'type' => 'error',
                'message' => __('No valid roles selected for deletion.')
            ]);
            return;
        }

        $deletedCount = 0;

        foreach ($roles as $role) {
            $role = ld_apply_filters('role_delete_before', $role);
            $role->delete();
            ld_do_action('role_delete_after', $role);
            $deletedCount++;
        }

        $this->checkboxValues = [];

        $this->dispatchBrowserEvent('show-message', [
            'type' => 'success',
            'message' => __(':count roles deleted successfully', ['count' => $deletedCount])
        ]);

        $this->fillData();
    }
}