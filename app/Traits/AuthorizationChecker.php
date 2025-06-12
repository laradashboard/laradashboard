<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

trait AuthorizationChecker
{
    /**
     * Check if the user is authorized to perform the action.
     *
     * @param  Authenticatable|User  $user
     * @param  array|string  $permissions
     * @param  bool  $ownPermissionCheck
     */
    public function checkAuthorization($user, $permissions, $ownPermissionCheck = false): bool
    {
        if (empty($user) || ! $user->can($permissions)) {
            abort(403, 'Sorry !! You are unauthorized to perform this action.');
        }

        if ($ownPermissionCheck && $this->getUserId($user) !== Auth::id()) {
            abort(403, 'Sorry !! You are unauthorized to perform this action.');
        }

        return true;
    }

    /**
     * Get the user ID safely from any user object.
     *
     * @param  Authenticatable|User  $user
     * @return int|string|null
     */
    protected function getUserId($user)
    {
        // For User model, access id directly
        if ($user instanceof User) {
            return $user->id;
        }

        // For other Authenticatable implementations, use getAuthIdentifier()
        if ($user instanceof Authenticatable) {
            return $user->getAuthIdentifier();
        }

        return null;
    }

    /**
     * Prevent modification of the super admin in demo mode.
     *
     * @param  User  $user
     * @param  string|array  $additionalPermission
     */
    public function preventSuperAdminModification(User|Authenticatable|null $user = null, $additionalPermission = 'user.edit'): void
    {
        if ($user && ! $this->canBeModified($user, $additionalPermission)) {
            abort(403, 'Superadmin cannot be modified in demo mode.');
        }
    }

    public function canBeModified(User $user, $additionalPermission = 'user.edit'): bool
    {
        $isSuperAdmin = $user->email === 'superadmin@example.com' || $user->username === 'Superadmin';
        if (config('app.demo_mode') && $isSuperAdmin) {
            return false;
        }

        return auth()->user()->can($additionalPermission);
    }

    public function preventSuperAdminRoleModification(Role $role, string $action = 'modified')
    {
        if (config('app.demo_mode') && $role->name == 'Superadmin') {
            abort(403, "The Superadmin role can not be {$action}.");
        }
    }
}
