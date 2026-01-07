<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Permission Action Hooks
 *
 * Provides action hooks for permission events that modules can hook into.
 * Action hooks are fire-and-forget - they notify listeners but don't expect return values.
 *
 * @example
 * // Listen to permission creation
 * Hook::addAction(PermissionActionHook::PERMISSION_CREATED_AFTER, function ($permission) {
 *     // Log or perform side effects
 * });
 */
enum PermissionActionHook: string
{
    // ==========================================================================
    // Permission CRUD Events
    // ==========================================================================

    /**
     * Fired before a permission is created.
     *
     * @param array $data The permission data being created
     */
    case PERMISSION_CREATED_BEFORE = 'action.permission.created_before';

    /**
     * Fired after a permission is created.
     *
     * @param \Spatie\Permission\Models\Permission $permission The created permission
     */
    case PERMISSION_CREATED_AFTER = 'action.permission.created_after';

    /**
     * Fired before a permission is updated.
     *
     * @param \Spatie\Permission\Models\Permission $permission The permission being updated
     * @param array $data The new data
     */
    case PERMISSION_UPDATED_BEFORE = 'action.permission.updated_before';

    /**
     * Fired after a permission is updated.
     *
     * @param \Spatie\Permission\Models\Permission $permission The updated permission
     */
    case PERMISSION_UPDATED_AFTER = 'action.permission.updated_after';

    /**
     * Fired before a permission is deleted.
     *
     * @param \Spatie\Permission\Models\Permission $permission The permission being deleted
     */
    case PERMISSION_DELETED_BEFORE = 'action.permission.deleted_before';

    /**
     * Fired after a permission is deleted.
     *
     * @param int $permissionId The ID of the deleted permission
     */
    case PERMISSION_DELETED_AFTER = 'action.permission.deleted_after';

    // ==========================================================================
    // Bulk Operations
    // ==========================================================================

    /**
     * Fired before permissions are bulk deleted.
     *
     * @param array $permissionIds The IDs of permissions being deleted
     */
    case PERMISSION_BULK_DELETED_BEFORE = 'action.permission.bulk_deleted_before';

    /**
     * Fired after permissions are bulk deleted.
     *
     * @param array $permissionIds The IDs of deleted permissions
     */
    case PERMISSION_BULK_DELETED_AFTER = 'action.permission.bulk_deleted_after';

    // ==========================================================================
    // Permission Sync Events
    // ==========================================================================

    /**
     * Fired before permissions are synced/seeded.
     *
     * @param array $permissions The permissions being synced
     */
    case PERMISSIONS_SYNC_BEFORE = 'action.permission.sync_before';

    /**
     * Fired after permissions are synced/seeded.
     *
     * @param array $permissions The synced permissions
     */
    case PERMISSIONS_SYNC_AFTER = 'action.permission.sync_after';

    /**
     * Fired when a permission is assigned to a role.
     *
     * @param \Spatie\Permission\Models\Permission $permission
     * @param \Spatie\Permission\Models\Role $role
     */
    case PERMISSION_ASSIGNED_TO_ROLE = 'action.permission.assigned_to_role';

    /**
     * Fired when a permission is revoked from a role.
     *
     * @param \Spatie\Permission\Models\Permission $permission
     * @param \Spatie\Permission\Models\Role $role
     */
    case PERMISSION_REVOKED_FROM_ROLE = 'action.permission.revoked_from_role';
}
