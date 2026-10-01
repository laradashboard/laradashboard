<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Role;
use App\Models\User;

final class RolePermissionGuard
{
    /**
     * Permissions that may only exist on the superadmin role.
     *
     * @var list<string>
     */
    public const SUPERADMIN_ONLY_PERMISSIONS = [
        'user.delete',
        'user.login_as',
    ];

    public static function isSuperAdminOnlyPermission(string $permission): bool
    {
        return in_array($permission, self::SUPERADMIN_ONLY_PERMISSIONS, true);
    }

    /**
     * @param  list<string>  $permissions
     * @return list<string>
     */
    public static function filterGrantablePermissions(User $editor, Role $targetRole, array $permissions): array
    {
        if ($editor->isSuperAdmin()) {
            return $permissions;
        }

        if ($targetRole->isSuperAdminRole()) {
            abort(403, __('You are not allowed to modify the Superadmin role.'));
        }

        return array_values(array_filter(
            $permissions,
            fn (string $permission): bool => ! self::isSuperAdminOnlyPermission($permission)
        ));
    }
}
