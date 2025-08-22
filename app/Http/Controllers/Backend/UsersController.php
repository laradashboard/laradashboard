<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Enums\Hooks\UserHook;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\RolesService;
use App\Services\UserService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly RolesService $rolesService,
        private readonly \App\Services\LanguageService $languageService,
        private readonly \App\Services\TimezoneService $timezoneService,
    ) {
    }

    public function index(): Renderable
    {
        $this->checkAuthorization(Auth::user(), ['user.view']);

        $filters = [
            'search' => request('search'),
            'role' => request('role'),
            'sort_field' => null,
            'sort_direction' => null,
        ];

        return view('backend.pages.users.index', [
            'users' => $this->userService->getUsers($filters),
            'roles' => $this->rolesService->getRolesDropdown(),
            'breadcrumbs' => [
                'title' => __('Users'),
            ],
        ]);
    }

    public function create(): Renderable
    {
        $this->checkAuthorization(Auth::user(), ['user.create']);

        ld_do_action(UserHook::CREATE_PAGE_BEFORE);

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
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->avatar_id = $request->avatar_id;

        $user = ld_apply_filters(UserHook::STORE_BEFORE_SAVE, $user, $request);
        $user->save();
        /** @var User $user */
        $user = ld_apply_filters(UserHook::STORE_AFTER_SAVE, $user, $request);

        // Save user metadata for additional information
        $metaFields = ['display_name', 'bio', 'timezone', 'locale'];
        foreach ($metaFields as $field) {
            if ($request->has($field) && $request->input($field)) {
                $user->userMeta()->create([
                    'meta_key' => $field,
                    'meta_value' => $request->input($field),
                    'type' => 'string',
                ]);
            }
        }

        // Handle social links metadata
        $socialFields = ['social_facebook', 'social_x', 'social_youtube', 'social_linkedin', 'social_website'];
        foreach ($socialFields as $field) {
            if ($request->has($field) && $request->input($field)) {
                $user->userMeta()->create([
                    'meta_key' => $field,
                    'meta_value' => $request->input($field),
                    'type' => 'string',
                ]);
            }
        }

        if ($request->roles) {
            $roles = array_filter($request->roles);
            $user->assignRole($roles);
        }

        $this->storeActionLog(ActionType::CREATED, ['user' => $user]);

        session()->flash('success', __('User has been created.'));

        ld_do_action(UserHook::STORE_AFTER, $user);

        return redirect()->route('admin.users.index');
    }

    public function edit(int $id): Renderable
    {
        $this->checkAuthorization(Auth::user(), ['user.edit']);

        $user = User::with('avatar')->findOrFail($id);

        ld_do_action(UserHook::EDIT_PAGE_BEFORE);

        $user = ld_apply_filters(UserHook::EDIT_PAGE_BEFORE_WITH_USER, $user);

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

        // Prevent editing of super admin in demo mode
        $this->preventSuperAdminModification($user);

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->avatar_id = $request->avatar_id;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user = ld_apply_filters(UserHook::UPDATE_BEFORE_SAVE, $user, $request);
        $user->save();

        /** @var User $user */
        $user = ld_apply_filters(UserHook::UPDATE_AFTER_SAVE, $user, $request);

        // Update user metadata for additional information
        $metaFields = ['display_name', 'bio', 'timezone', 'locale'];
        foreach ($metaFields as $field) {
            if ($request->has($field)) {
                $user->userMeta()->updateOrCreate(
                    ['meta_key' => $field],
                    [
                        'meta_value' => $request->input($field) ?? '',
                        'type' => 'string',
                    ]
                );
            }
        }

        // Update social links metadata
        $socialFields = ['social_facebook', 'social_x', 'social_youtube', 'social_linkedin', 'social_website'];
        foreach ($socialFields as $field) {
            if ($request->has($field)) {
                $user->userMeta()->updateOrCreate(
                    ['meta_key' => $field],
                    [
                        'meta_value' => $request->input($field) ?? '',
                        'type' => 'string',
                    ]
                );
            }
        }

        ld_do_action(UserHook::UPDATE_AFTER, $user);

        $user->roles()->detach();
        if ($request->roles) {
            $roles = array_filter($request->roles);
            $user->assignRole($roles);
        }

        $this->storeActionLog(ActionType::UPDATED, ['user' => $user]);

        session()->flash('success', __('User has been updated.'));

        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->checkAuthorization(Auth::user(), ['user.delete']);
        $user = $this->userService->getUserById($id);

        // Prevent deletion of super admin in demo mode
        $this->preventSuperAdminModification($user);

        // Prevent users from deleting themselves.
        if (Auth::id() === $user->id) {
            session()->flash('error', __('You cannot delete your own account.'));
            return back();
        }

        $user = ld_apply_filters(UserHook::DELETE_BEFORE, $user);
        $user->delete();
        $user = ld_apply_filters(UserHook::DELETE_AFTER, $user);
        session()->flash('success', __('User has been deleted.'));

        $this->storeActionLog(ActionType::DELETED, ['user' => $user]);

        ld_do_action(UserHook::DELETE_AFTER, $user);

        return back();
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->checkAuthorization(Auth::user(), ['user.delete']);

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

            $user = ld_apply_filters(UserHook::DELETE_BEFORE, $user);
            $user->delete();
            ld_apply_filters(UserHook::DELETE_AFTER, $user);

            $this->storeActionLog(ActionType::DELETED, ['user' => $user]);
            ld_do_action(UserHook::DELETE_AFTER, $user);

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
