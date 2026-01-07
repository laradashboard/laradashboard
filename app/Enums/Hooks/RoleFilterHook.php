<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Role Filter Hooks
 *
 * Provides filter hooks for role management that modules can use to modify behavior.
 */
enum RoleFilterHook: string
{
    case ROLE_CREATED_BEFORE = 'filter.role.created_before';
    case ROLE_CREATED_AFTER = 'filter.role.created_after';

    case ROLE_UPDATED_BEFORE = 'filter.role.updated_before';
    case ROLE_UPDATED_AFTER = 'filter.role.updated_after';

    case ROLE_DELETED_BEFORE = 'filter.role.deleted_before';
    case ROLE_DELETED_AFTER = 'filter.role.deleted_after';

    case ROLE_BULK_DELETED_BEFORE = 'filter.role.bulk_deleted_before';
    case ROLE_BULK_DELETED_AFTER = 'filter.role.bulk_deleted_after';

    // Validation hooks
    case ROLE_STORE_VALIDATION_RULES = 'filter.role.store.validation.rules';
    case ROLE_UPDATE_VALIDATION_RULES = 'filter.role.update.validation.rules';

    // Role Index Page Hooks
    case ROLES_AFTER_BREADCRUMBS = 'filter.roles.after_breadcrumbs';
    case ROLES_AFTER_TABLE = 'filter.roles.after_table';
    case ROLES_BEFORE_TABLE = 'filter.roles.before_table';

    // Role Create/Edit Form Hooks
    case ROLE_CREATE_BEFORE_FORM = 'filter.role.create_before_form';
    case ROLE_EDIT_BEFORE_FORM = 'filter.role.edit_before_form';
    case ROLE_CREATE_AFTER_FORM = 'filter.role.create_after_form';
    case ROLE_EDIT_AFTER_FORM = 'filter.role.edit_after_form';
    case ROLE_FORM_AFTER_NAME = 'filter.role.form.after_name';
    case ROLE_FORM_BEFORE_PERMISSION_GROUPS = 'filter.role.form.before_permission_groups';
    case ROLE_FORM_AFTER_PERMISSIONS = 'filter.role.form.after_permissions';

    // Role Show Page Hooks
    case ROLE_SHOW_AFTER_BREADCRUMBS = 'filter.role.show.after_breadcrumbs';
    case ROLE_SHOW_AFTER_MAIN_CONTENT = 'filter.role.show.after_main_content';
    case ROLE_SHOW_AFTER_SIDEBAR = 'filter.role.show.after_sidebar';
    case ROLE_SHOW_AFTER_CONTENT = 'filter.role.show.after_content';
}
