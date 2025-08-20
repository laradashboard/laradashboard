<?php

declare(strict_types=1);

namespace App\Pages\User;

use App\Models\User;
use App\PageGenerator\AbstractListPage;
use App\Services\RolesService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserList extends AbstractListPage
{
    protected array $searchableColumns = ['name', 'email'];
    protected array $sortableColumns = ['name', 'email', 'created_at'];

    public function __construct(
        Request $request,
        private readonly RolesService $rolesService
    ) {
        parent::__construct($request);
    }

    public function authorize(): bool
    {
        return Gate::allows('viewAny', User::class);
    }

    public function getTitle(): string
    {
        return __('Users');
    }

    public function getBreadcrumbs(): array
    {
        return [
            'title' => __('Users'),
        ];
    }

    public function getColumns(): array
    {
        return [
            [
                'name' => 'avatar',
                'label' => '',
                'type' => 'custom',
                'width' => '60px',
                'render' => function ($user, $value) {
                    return '<img src="' . $user->avatar_url . '" alt="' . $user->full_name . '" class="w-10 h-10 rounded-full">';
                },
            ],
            [
                'name' => 'name',
                'label' => __('Name'),
                'type' => 'custom',
                'width' => '25%',
                'render' => function ($user, $value) {
                    $editUrl = auth()->user()->canBeModified($user) ? route('admin.users.edit', $user->id) : '#';
                    return '
                        <a href="' . $editUrl . '" class="flex flex-col">
                            <span class="font-medium">' . e($user->full_name) . '</span>
                            <span class="text-xs text-gray-500 dark:text-gray-300">' . e($user->username) . '</span>
                        </a>
                    ';
                },
            ],
            [
                'name' => 'email',
                'label' => __('Email'),
                'type' => 'text',
                'width' => '25%',
            ],
            [
                'name' => 'roles',
                'label' => __('Roles'),
                'type' => 'custom',
                'width' => '30%',
                'render' => function ($user, $value) {
                    $badges = '';
                    foreach ($user->roles as $role) {
                        $badges .= '<span class="badge mr-1">' . e($role->name) . '</span>';
                    }
                    return $badges;
                },
            ],
            [
                'name' => 'created_at',
                'label' => __('Created'),
                'type' => 'date',
                'format' => 'Y-m-d',
                'width' => '15%',
            ],
        ];
    }

    public function getData(): LengthAwarePaginator|Collection
    {
        $query = User::with(['roles', 'avatar']);

        $query = $this->applySearch($query);
        $query = $this->applySorting($query);
        $query = $this->applyFilters($query);

        return $query->paginate($this->getPerPage());
    }

    public function getFilters(): array
    {
        return [
            [
                'name' => 'role',
                'label' => __('Role'),
                'type' => 'select',
                'options' => $this->rolesService->getRolesDropdown(),
                'callback' => function ($query, $value) {
                    return $query->whereHas('roles', function ($q) use ($value) {
                        $q->where('name', $value);
                    });
                },
            ],
            // [
            //     'name' => 'created_at',
            //     'label' => __('Created Date'),
            //     'type' => 'daterange',
            //     'callback' => function ($query, $value) {
            //         $from = $this->request->input('created_at_from');
            //         $to = $this->request->input('created_at_to');

            //         if ($from) {
            //             $query->whereDate('created_at', '>=', $from);
            //         }
            //         if ($to) {
            //             $query->whereDate('created_at', '<=', $to);
            //         }

            //         return $query;
            //     },
            // ],
        ];
    }

    public function getActions(): array
    {
        return [
            [
                'label' => __('Edit'),
                'icon' => 'pencil',
                'route' => fn ($user) => route('admin.users.edit', $user->id),
                'condition' => fn ($user) => auth()->user()->canBeModified($user),
            ],
            [
                'label' => __('Login as'),
                'icon' => 'box-arrow-in-right',
                'route' => fn ($user) => route('admin.users.login-as', $user->id),
                'condition' => fn ($user) => auth()->user()->can('user.login_as') && $user->id !== auth()->id(),
            ],
            [
                'type' => 'delete',
                'label' => __('Delete'),
                'icon' => 'trash',
                'class' => 'text-red-600 dark:text-red-400',
                'route' => fn ($user) => route('admin.users.destroy', $user->id),
                'condition' => fn ($user) => auth()->user()->canBeModified($user, 'user.delete'),
                'modalTitle' => __('Delete User'),
                'modalContent' => __('Are you sure you want to delete this user?'),
            ],
        ];
    }

    public function getBulkActions(): array
    {
        return [
            [
                'name' => 'bulk_delete',
                'label' => __('Delete Selected'),
                'icon' => 'lucide:trash',
                'class' => 'text-red-600 dark:text-red-500 hover:bg-red-50 dark:hover:bg-red-500 dark:hover:text-red-50',
                'route' => route('admin.users.bulk-delete'),
                'method' => 'DELETE',
                'confirm' => true,
                'confirmMessage' => __('Are you sure you want to delete the selected users?'),
            ],
        ];
    }

    protected function getCreateRoute(): ?string
    {
        return auth()->user()->can('user.edit') ? route('admin.users.create') : null;
    }

    protected function getCreateButtonText(): string
    {
        return __('New User');
    }

    protected function getSearchPlaceholder(): string
    {
        return __('Search by name or email');
    }
}
