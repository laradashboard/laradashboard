<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Concerns\HasDataTableFeatures;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

class UserTable extends PowerGridComponent
{
    use HasDataTableFeatures, WithExport;

    public string $tableName = 'user-table';

    public function datasource(): Builder
    {
        $query = User::query()
            ->with(['roles', 'avatar'])
            ->select('users.*');

        return $this->applyQueryFilters($query);
    }

    public function relationSearch(): array
    {
        return [
            'roles' => ['name'],
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

            Column::make('Avatar', 'avatar_id')
                ->bodyAttribute('class', 'w-16')
                ->format(function ($value, $row) {
                    $avatarUrl = $row->avatar_url ?? '/images/user/default.svg';
                    return '<img src="' . $avatarUrl . '" alt="' . $row->full_name . '" class="w-10 h-10 rounded-full">';
                }),

            Column::make('Name', 'first_name')
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    $name = $row->full_name;
                    $username = $row->username ? '@' . $row->username : '';
                    return '<div class="flex flex-col">
                        <span class="font-medium">' . $name . '</span>
                        <span class="text-xs text-gray-500">' . $username . '</span>
                    </div>';
                }),

            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),

            Column::make('Roles', 'roles')
                ->format(function ($value, $row) {
                    $roles = $row->roles->pluck('name')->map(function ($role) {
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">' . ucfirst($role) . '</span>';
                    })->join(' ');
                    return $roles;
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
                ->route('admin.users.edit', fn ($row) => $row->id);
        }

        if ($this->canPerformAction('login_as')) {
            $actions[] = Button::make('login-as', 'Login As')
                ->class('btn-sm btn-info inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-300 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->route('admin.users.login-as', fn ($row) => $row->id)
                ->can(fn ($row) => auth()->user()->can('user.login_as') && $row->id !== auth()->id());
        }

        if ($this->canPerformAction('delete')) {
            $actions[] = Button::make('delete', 'Delete')
                ->class('btn-sm btn-danger inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-300 rounded-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500')
                ->icon('heroicon-o-trash')
                ->confirm('Are you sure you want to delete this user?')
                ->method('deleteUser')
                ->can(fn ($row) => auth()->user()->canBeModified($row, 'user.delete'));
        }

        return $actions;
    }

    protected function getBaseFilters(): array
    {
        $roles = \App\Models\Role::pluck('name', 'name')->toArray();

        return [
            Filter::inputText('search')
                ->placeholder('Search users...'),

            Filter::select('role')
                ->dataSource($roles)
                ->optionLabel('name')
                ->optionValue('name')
                ->placeholder('Filter by role'),

            Filter::datepicker('created_at')
                ->label('Created Date'),
        ];
    }

    protected function applyBaseQueryFilters($query)
    {
        // Apply search filter
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if ($role = request('role')) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        return $query;
    }

    protected function getHookPrefix(): string
    {
        return 'user';
    }

    protected function getRouteName(): string
    {
        return 'users';
    }

    protected function getModelClass(): string
    {
        return User::class;
    }

    protected function getPermissionPrefix(): string
    {
        return 'user';
    }

    /**
     * Custom delete method for users
     */
    public function deleteUser(int $id): void
    {
        $user = User::findOrFail($id);

        // Check authorization
        if (!auth()->user()->canBeModified($user, 'user.delete')) {
            $this->dispatchBrowserEvent('show-message', [
                'type' => 'error',
                'message' => __('You are not authorized to delete this user.')
            ]);
            return;
        }

        // Prevent users from deleting themselves
        if (auth()->id() === $user->id) {
            $this->dispatchBrowserEvent('show-message', [
                'type' => 'error',
                'message' => __('You cannot delete your own account.')
            ]);
            return;
        }

        // Apply hooks
        $user = ld_apply_filters('user_delete_before', $user);
        $user->delete();
        ld_do_action('user_delete_after', $user);

        $this->dispatchBrowserEvent('show-message', [
            'type' => 'success',
            'message' => __('User deleted successfully.')
        ]);

        $this->fillData();
    }

    /**
     * Override bulk delete for user-specific logic
     */
    public function bulkDelete(): void
    {
        $this->validate([
            'checkboxValues' => 'required|array|min:1',
        ]);

        $currentUserId = auth()->id();
        $userIds = collect($this->checkboxValues)->reject(fn ($id) => $id == $currentUserId);

        if ($userIds->isEmpty()) {
            $this->dispatchBrowserEvent('show-message', [
                'type' => 'error',
                'message' => __('No valid users selected for deletion.')
            ]);
            return;
        }

        $users = User::whereIn('id', $userIds)->get();
        $deletedCount = 0;

        foreach ($users as $user) {
            if (!auth()->user()->canBeModified($user, 'user.delete')) {
                continue;
            }

            $user = ld_apply_filters('user_delete_before', $user);
            $user->delete();
            ld_do_action('user_delete_after', $user);
            $deletedCount++;
        }

        $this->checkboxValues = [];

        $this->dispatchBrowserEvent('show-message', [
            'type' => 'success',
            'message' => __(':count users deleted successfully', ['count' => $deletedCount])
        ]);

        $this->fillData();
    }
}