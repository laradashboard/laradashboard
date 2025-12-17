<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\Hooks\UserActionHook;
use App\Enums\Hooks\UserFilterHook;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\Common\BulkDeleteRequest;
use App\Models\User;
use App\Services\LanguageService;
use App\Services\RolesService;
use App\Services\TimezoneService;
use App\Services\UserService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly RolesService $rolesService,
        private readonly LanguageService $languageService,
        private readonly TimezoneService $timezoneService,
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('viewAny', User::class);

        $this->setBreadcrumbTitle(__('Users'))
            ->setBreadcrumbIcon('lucide:users')
            ->setBreadcrumbActionButton(
                route('admin.users.create'),
                __('New User'),
                'feather:plus',
                'user.create'
            );

        return $this->renderViewWithBreadcrumbs('backend.pages.users.index');
    }

    public function create(): Renderable
    {
        $this->authorize('create', User::class);

        $this->setBreadcrumbTitle(__('New User'))
            ->setBreadcrumbIcon('lucide:users')
            ->addBreadcrumbItem(__('Users'), route('admin.users.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.users.create', [
            'roles' => $this->rolesService->getRolesDropdown(),
            'locales' => $this->languageService->getLanguages(),
            'timezones' => $this->timezoneService->getTimezones(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $this->addHooks(
            $request->validated(),
            UserActionHook::USER_CREATED_BEFORE,
            UserFilterHook::USER_CREATED_BEFORE
        );

        $user = $this->userService->createUserWithMetadata($data, $request);

        $user = $this->addHooks(
            $user,
            UserActionHook::USER_CREATED_AFTER,
            UserFilterHook::USER_CREATED_AFTER
        );

        session()->flash('success', __('User has been created.'));

        return redirect()->route('admin.users.index');
    }

    public function show(int $id): Renderable
    {
        $user = User::with(['avatar', 'roles', 'userMeta'])->findOrFail($id);

        $this->authorize('view', $user);

        $this->setBreadcrumbTitle($user->full_name)
            ->setBreadcrumbIcon('lucide:users')
            ->addBreadcrumbItem(__('Users'), route('admin.users.index'))
            ->setBreadcrumbActionButton(
                route('admin.users.edit', $user->id),
                __('Edit User'),
                'feather:edit-2',
                'user.edit',
            );

        return $this->renderViewWithBreadcrumbs('backend.pages.users.show', [
            'user' => $user,
        ]);
    }

    public function edit(int $id): Renderable
    {
        $user = User::with('avatar')->findOrFail($id);

        $this->authorize('update', $user);

        $this->setBreadcrumbTitle(__('Edit User'))
            ->setBreadcrumbIcon('lucide:users')
            ->addBreadcrumbItem(__('Users'), route('admin.users.index'))
            ->setBreadcrumbActionButton(
                route('admin.users.show', $user->id),
                __('View User'),
                'feather:eye',
                'user.view',
                true
            );

        return $this->renderViewWithBreadcrumbs('backend.pages.users.edit', [
            'user' => $user,
            'roles' => $this->rolesService->getRolesDropdown(),
        ]);
    }

    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $data = $this->addHooks(
            $request->validated(),
            UserActionHook::USER_UPDATED_BEFORE,
            UserFilterHook::USER_UPDATED_BEFORE
        );

        $user = $this->userService->updateUserWithMetadata($user, $data, $request);

        $user = $this->addHooks(
            $user,
            UserActionHook::USER_UPDATED_AFTER,
            UserFilterHook::USER_UPDATED_AFTER
        );

        session()->flash('success', __('User has been updated.'));

        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = $this->userService->getUserById($id);

        // Check if user is trying to delete themselves.
        if (Auth::id() === $user->id) {
            session()->flash('error', __('You cannot delete your own account.'));
            return back();
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

        session()->flash('success', __('User has been deleted.'));

        return back();
    }

    public function bulkDelete(BulkDeleteRequest $request): RedirectResponse
    {
        $this->authorize('bulkDelete', User::class);

        $ids = $request->validated('ids');

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

        $this->addHooks(
            $ids,
            UserActionHook::USER_BULK_DELETED_BEFORE,
            UserFilterHook::USER_BULK_DELETED_BEFORE
        );

        $deletedCount = $this->userService->bulkDeleteUsers($ids, Auth::id());

        $this->addHooks(
            $deletedCount,
            UserActionHook::USER_BULK_DELETED_AFTER,
            UserFilterHook::USER_BULK_DELETED_AFTER
        );

        if ($deletedCount > 0) {
            session()->flash('success', __(':count users deleted successfully', ['count' => $deletedCount]));
        } else {
            session()->flash('error', __('No users were deleted. Selected users may include protected accounts.'));
        }

        return redirect()->route('admin.users.index');
    }
}
