<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Permission Filter Hooks
 *
 * Provides filter hooks for permissions that modules can use to modify behavior.
 * Filter hooks receive a value and must return it (modified or not).
 *
 * @example
 * // Add custom permission groups
 * Hook::addFilter(PermissionFilterHook::PERMISSION_GROUPS, function ($groups) {
 *     $groups[] = [
 *         'group_name' => 'crm',
 *         'permissions' => ['crm.view', 'crm.create', 'crm.edit', 'crm.delete'],
 *     ];
 *     return $groups;
 * });
 */
enum PermissionFilterHook: string
{
    // ==========================================================================
    // Data Filters
    // ==========================================================================

    /**
     * Filter permission data before creation.
     *
     * @param array $data The permission data
     * @return array Modified permission data
     */
    case PERMISSION_CREATED_BEFORE = 'filter.permission.created_before';

    /**
     * Filter permission after creation.
     *
     * @param \Spatie\Permission\Models\Permission $permission
     * @return \Spatie\Permission\Models\Permission
     */
    case PERMISSION_CREATED_AFTER = 'filter.permission.created_after';

    /**
     * Filter permission data before update.
     *
     * @param array $data The permission data
     * @param \Spatie\Permission\Models\Permission $permission
     * @return array Modified permission data
     */
    case PERMISSION_UPDATED_BEFORE = 'filter.permission.updated_before';

    /**
     * Filter permission after update.
     *
     * @param \Spatie\Permission\Models\Permission $permission
     * @return \Spatie\Permission\Models\Permission
     */
    case PERMISSION_UPDATED_AFTER = 'filter.permission.updated_after';

    /**
     * Filter permission before deletion (can prevent deletion by returning false).
     *
     * @param \Spatie\Permission\Models\Permission $permission
     * @return \Spatie\Permission\Models\Permission|false
     */
    case PERMISSION_DELETED_BEFORE = 'filter.permission.deleted_before';

    /**
     * Filter after permission deletion.
     *
     * @param int $permissionId
     * @return int
     */
    case PERMISSION_DELETED_AFTER = 'filter.permission.deleted_after';

    // ==========================================================================
    // Permission Groups
    // ==========================================================================

    /**
     * Filter the list of permission groups.
     * Use this to add custom permission groups for modules.
     *
     * @param array $groups Array of permission groups
     * @return array Modified permission groups
     */
    case PERMISSION_GROUPS = 'filter.permission.groups';

    /**
     * Filter permissions by group name.
     *
     * @param array $permissions Permissions in the group
     * @param string $groupName The group name
     * @return array Modified permissions
     */
    case PERMISSIONS_BY_GROUP = 'filter.permission.by_group';

    // ==========================================================================
    // Validation Hooks
    // ==========================================================================

    /**
     * Filter validation rules for storing permissions.
     *
     * @param array $rules The validation rules
     * @return array Modified validation rules
     */
    case PERMISSION_STORE_VALIDATION_RULES = 'filter.permission.store.validation.rules';

    /**
     * Filter validation rules for updating permissions.
     *
     * @param array $rules The validation rules
     * @return array Modified validation rules
     */
    case PERMISSION_UPDATE_VALIDATION_RULES = 'filter.permission.update.validation.rules';

    // ==========================================================================
    // UI Hooks - Index Page
    // ==========================================================================

    /**
     * Hook after the permissions page breadcrumbs.
     */
    case PERMISSIONS_AFTER_BREADCRUMBS = 'filter.permissions.after_breadcrumbs';

    /**
     * Hook after the permissions table.
     */
    case PERMISSIONS_AFTER_TABLE = 'filter.permissions.after_table';

    /**
     * Hook before the permissions table.
     */
    case PERMISSIONS_BEFORE_TABLE = 'filter.permissions.before_table';

    // ==========================================================================
    // UI Hooks - Show Page
    // ==========================================================================

    /**
     * Hook after the permission show page breadcrumbs.
     */
    case PERMISSION_SHOW_AFTER_BREADCRUMBS = 'filter.permission.show.after_breadcrumbs';

    /**
     * Hook after the permission show page main content.
     */
    case PERMISSION_SHOW_AFTER_MAIN_CONTENT = 'filter.permission.show.after_main_content';

    /**
     * Hook after the permission show page sidebar.
     */
    case PERMISSION_SHOW_AFTER_SIDEBAR = 'filter.permission.show.after_sidebar';

    /**
     * Hook at the end of permission show page.
     */
    case PERMISSION_SHOW_AFTER_CONTENT = 'filter.permission.show.after_content';

    // ==========================================================================
    // Permission Form Field Hooks
    // ==========================================================================

    /**
     * Hook after the permission name field.
     */
    case PERMISSION_FORM_AFTER_NAME = 'filter.permission.form.after_name';

    /**
     * Hook after the permission group field.
     */
    case PERMISSION_FORM_AFTER_GROUP = 'filter.permission.form.after_group';

    /**
     * Hook at the end of the permission form.
     */
    case PERMISSION_FORM_AFTER = 'filter.permission.form.after';
}
