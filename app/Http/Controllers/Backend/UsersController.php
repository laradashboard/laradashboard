<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\UserService;
use App\Services\RolesService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly RolesService $rolesService
    ) {
    }

    public function index(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['user.view']);

        return view('backend.pages.users.index', [
            'users' => $this->userService->getUsers(),
            'roles' => $this->rolesService->getRolesDropdown(),
        ]);
    }

    public function create(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['user.create']);

        ld_do_action('user_create_page_before');

        return view('backend.pages.users.create', [
            'roles' => $this->rolesService->getRolesDropdown(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['user.create']);

        $user = new User();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        $user = ld_apply_filters('user_store_before_save', $user, $request);
        $user->save();
        $user = ld_apply_filters('user_store_after_save', $user, $request);

        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        $this->storeActionLog(ActionType::CREATED, ['user' => $user]);

        session()->flash('success', __('User has been created.'));

        ld_do_action('user_store_after', $user);

        return redirect()->route('admin.users.index');
    }

    public function edit(int $id): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['user.edit']);

        $user = User::findOrFail($id);

        // Prevent editing of super admin in demo mode
        $this->preventSuperAdminModification($user);

        ld_do_action('user_edit_page_before');

        $user = ld_apply_filters('user_edit_page_before_with_user', $user);

        return view('backend.pages.users.edit', [
            'user' => $user,
            'roles' => $this->rolesService->getRolesDropdown(),
        ]);
    }

    public function update(UserRequest $request, int $id): RedirectResponse
    {
        if (config('app.demo_mode') == true)  return back();
        
        $this->checkAuthorization(auth()->user(), ['user.edit']);

        $user = User::findOrFail($id);

        // Prevent editing of super admin in demo mode
        $this->preventSuperAdminModification($user);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->username;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user = ld_apply_filters('user_update_before_save', $user, $request);
        $user->save();
        $user = ld_apply_filters('user_update_after_save', $user, $request);
        ld_do_action('user_update_after', $user);

        $user->roles()->detach();
        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        $this->storeActionLog(ActionType::UPDATED, ['user' => $user]);

        session()->flash('success', 'User has been updated.');

        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['user.delete']);

        $user = $this->userService->getUserById($id);

        // Prevent deletion of super admin in demo mode
        $this->preventSuperAdminModification($user);

        $user = ld_apply_filters('user_delete_before', $user);
        $user->delete();
        $user = ld_apply_filters('user_delete_after', $user);
        session()->flash('success', 'User has been deleted.');

        $this->storeActionLog(ActionType::DELETED, ['user' => $user]);

        ld_do_action('user_delete_after', $user);

        return back();
    }
}
