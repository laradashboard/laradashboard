<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Enums\Hooks\UserActionHook;
use App\Enums\Hooks\UserFilterHook;
use App\Models\Role;
use App\Services\RolesService;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;

class UserDatatable extends Datatable
{
    public string $role = '';
    public array $queryString = [
        ...parent::QUERY_STRING_DEFAULTS,
        'role' => [],
    ];
    public string $model = User::class;

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by name or email...');
    }

    public function updatingRole()
    {
        $this->resetPage();
    }

    public function getFilters(): array
    {
        return [
            [
                'id' => 'role',
                'label' => __('Role'),
                'filterLabel' => __('Filter by Role'),
                'icon' => 'lucide:sliders',
                'allLabel' => __('All Roles'),
                'options' => app(RolesService::class)->getRolesDropdown(),
                'selected' => $this->role,
            ],
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
                'sortBy' => 'first_name',
            ],
            [
                'id' => 'email',
                'title' => __('Email'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'email',
            ],
            [
                'id' => 'roles',
                'title' => __('Roles'),
                'width' => null,
                'sortable' => false,
            ],
            [
                'id' => 'created_at',
                'title' => __('Created At'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'created_at',
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
            ->with('roles')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->role, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->role);
                });
            });

        return $this->sortQuery($query);
    }

    public function renderNameColumn(User $user): Renderable
    {
        return view('backend.pages.users.partials.user-name', compact('user'));
    }

    public function renderRolesColumn(User $user): Renderable
    {
        return view('backend.pages.users.partials.user-roles', compact('user'));
    }

    public function getActionCellPermissions($item): array
    {
        return [
            ...parent::getActionCellPermissions($item),
            'user.login_as' => Auth::user()->canBeModified($item, $this->getPermissions()['login_as'] ?? ''),
        ];
    }

    public function renderAfterActionEdit($user): string|Renderable
    {
        return (! Auth::user()->can('user.login_as') || $user->id === Auth::id())
            ? '' :
            view('backend.pages.users.partials.action-button-login-as', compact('user'));
    }

    protected function handleBulkDelete(array $ids): int
    {
        $ids = array_filter($ids, fn ($id) => $id != Auth::id()); // Prevent self-deletion.
        $users = User::whereIn('id', $ids)->get();
        $deletedCount = 0;
        foreach ($users as $user) {
            if ($user->hasRole(Role::SUPERADMIN) || $user->id === Auth::id()) {
                continue;
            }

            $this->authorize('delete', $user);

            $user = $this->addHooks(
                $user,
                UserActionHook::USER_DELETED_BEFORE,
                UserFilterHook::USER_DELETED_BEFORE
            );

            $user->delete();

            $this->addHooks(
                $user,
                UserActionHook::USER_DELETED_AFTER,
                UserFilterHook::USER_DELETED_AFTER
            );

            $deletedCount++;
        }

        return $deletedCount;
    }

    public function handleRowDelete(Model|User $user): bool
    {
        // Prevent Superadmin deletion.
        // @phpstan-ignore-next-line
        if ($user->hasRole(Role::SUPERADMIN)) {
            throw new \Exception(__('You cannot delete a :role account.', ['role' => Role::SUPERADMIN]));
        }

        // Prevent own account deletion.
        if (Auth::id() === $user->id) {
            throw new \Exception(__('You cannot delete your own account.'));
        }

        $user = $this->addHooks(
            $user,
            UserActionHook::USER_DELETED_BEFORE,
            UserFilterHook::USER_DELETED_BEFORE
        );

        $this->authorize('delete', $user);

        $deleted = $user->delete();

        $this->addHooks(
            $user,
            UserActionHook::USER_DELETED_AFTER,
            UserFilterHook::USER_DELETED_AFTER
        );

        return $deleted;
    }
}
