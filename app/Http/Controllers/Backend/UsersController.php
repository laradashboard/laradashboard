<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\PageGenerator\Traits\HasPageGenerator;
use App\Pages\User\UserCreate;
use App\Pages\User\UserList;
use Illuminate\Http\Response;
use App\Services\RolesService;
use App\Services\UserService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    use HasPageGenerator;

    public function __construct(
        private readonly UserService $userService,
        private readonly RolesService $rolesService,
        private readonly \App\Services\LanguageService $languageService,
        private readonly \App\Services\TimezoneService $timezoneService,
    ) {
    }

    public function index(UserList $page): Response|Renderable
    {
        // Method 1:
        // $this->authorize('viewAny', User::class);
        // $page = app(UserList::class, ['request' => request(), 'rolesService' => $this->rolesService]);
        // return $this->renderPage($page);

        // Method 2:
        // return app(UserList::class, [request()]);

        // Method 3:
        return $page;

        // $filters = [
        //     'search' => request('search'),
        //     'role' => request('role'),
        //     'sort_field' => null,
        //     'sort_direction' => null,
        // ];

        // return view('backend.pages.users.index', [
        //     'users' => $this->userService->getUsers($filters),
        //     'roles' => $this->rolesService->getRolesDropdown(),
        //     'breadcrumbs' => [
        //         'title' => __('Users'),
        //     ],
        // ]);
    }

    public function create(UserCreate $page): Renderable
    {
        return $page;
        $this->authorize('create', User::class);

        ld_do_action('user_create_page_before');

        return view('backend.pages.users.create', [
            'roles' => $this->rolesService->getRolesDropdown(),
            'locales' => $this->languageService->getLanguages(),
            'timezones' => $this->timezoneService->getTimezones(),
            'breadcrumbs' => [
                'title' => __('New User'),
                'items' => [
                    [
                        'label' => __('Users'),
                        'url' => route('admin.users.index'),
                    ],
                ],
            ],
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = $this->userService->createUserWithMetadata($request->validated(), $request);

        $this->storeActionLog(ActionType::CREATED, ['user' => $user]);

        session()->flash('success', __('User has been created.'));

        ld_do_action('user_store_after', $user);

        return redirect()->route('admin.users.index');
    }

    public function edit(int $id): Renderable
    {
        $user = User::with('avatar')->findOrFail($id);
        $this->authorize('update', $user);

        ld_do_action('user_edit_page_before');

        $user = ld_apply_filters('user_edit_page_before_with_user', $user);

        return view('backend.pages.users.edit', [
            'user' => $user,
            'roles' => $this->rolesService->getRolesDropdown(),
            'breadcrumbs' => [
                'title' => __('Edit User'),
                'items' => [
                    [
                        'label' => __('Users'),
                        'url' => route('admin.users.index'),
                    ],
                ],
            ],
        ]);
    }

    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $user = $this->userService->updateUserWithMetadata($user, $request->validated(), $request);

        ld_do_action('user_update_after', $user);

        $this->storeActionLog(ActionType::UPDATED, ['user' => $user]);

        session()->flash('success', __('User has been updated.'));

        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = $this->userService->getUserById($id);

        // Check if user is trying to delete themselves
        if (Auth::id() === $user->id) {
            session()->flash('error', __('You cannot delete your own account.'));
            return back();
        }

        $this->authorize('delete', $user);

        $user = ld_apply_filters('user_delete_before', $user);
        $user->delete();
        $user = ld_apply_filters('user_delete_after', $user);
        session()->flash('success', __('User has been deleted.'));

        $this->storeActionLog(ActionType::DELETED, ['user' => $user]);

        ld_do_action('user_delete_after', $user);

        return back();
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->authorize('bulkDelete', User::class);

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->route('admin.users.index')
                ->with('error', __('No users selected for deletion'));
        }

        if (in_array(Auth::id(), $ids)) {
            // Remove current user from the deletion list.
            $ids = array_filter($ids, fn ($id) => $id != Auth::id());
            session()->flash('error', __('You cannot delete your own account. Other selected users will be processed.'));

            // If no users left to delete after filtering out current user.
            if (empty($ids)) {
                return redirect()->route('admin.users.index')
                    ->with('error', __('No users were deleted.'));
            }
        }

        $users = User::whereIn('id', $ids)->get();
        $deletedCount = 0;

        foreach ($users as $user) {
            if ($user->hasRole('superadmin')) {
                continue;
            }

            $user = ld_apply_filters('user_delete_before', $user);
            $user->delete();
            ld_apply_filters('user_delete_after', $user);

            $this->storeActionLog(ActionType::DELETED, ['user' => $user]);
            ld_do_action('user_delete_after', $user);

            $deletedCount++;
        }

        if ($deletedCount > 0) {
            session()->flash('success', __(':count users deleted successfully', ['count' => $deletedCount]));
        } else {
            session()->flash('error', __('No users were deleted. Selected users may include protected accounts.'));
        }

        return redirect()->route('admin.users.index');
    }
}
