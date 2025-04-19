<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\User;

trait AuthorizationChecker
{
    /**
     * Check if the user is authorized to perform the action.
     *
     * @param  Authenticatable  $user
     * @param  array|string  $permissions
     * @param  bool $ownPermissionCheck
     */
    public function checkAuthorization($user, $permissions, $ownPermissionCheck = false): void
    {
        if (is_null($user) || !$user->can($permissions)) {
            abort(403, 'Sorry !! You are unauthorized to perform this action.');
        }

        if ($ownPermissionCheck && $user->id !== auth()->user()->id) {
            abort(403, 'Sorry !! You are unauthorized to perform this action.');
        }
    }

    /**
     * Prevent modification of the super admin in demo mode.
     *
     * @param  User  $user
     */
    public function preventSuperAdminModification(User|Authenticatable $user = null, $additionalPermission = 'user.edit'): void
    {
        if ($user && !$this->canBeModified($user, $additionalPermission)) {
            abort(403, 'Super Admin cannot be modified in demo mode.');
        }
    }

    public function canBeModified(User $user, $additionalPermission = 'user.edit'): bool
    {
        $isSuperAdmin = $user->email === 'superadmin@example.com' || $user->username === 'superadmin';
        if (env('DEMO_MODE', false) && $isSuperAdmin) {
            return false;
        }

        return auth()->user()->can($additionalPermission);
    }
}
