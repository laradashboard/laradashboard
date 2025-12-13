<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum UserFilterHook: string
{
    case USER_CREATED_BEFORE = 'filter.user.created_before';
    case USER_CREATED_AFTER = 'filter.user.created_after';

    case USER_UPDATED_BEFORE = 'filter.user.updated_before';
    case USER_UPDATED_AFTER = 'filter.user.updated_after';

    case USER_PROFILE_UPDATED_BEFORE = 'filter.user.profile_updated_before';
    case USER_PROFILE_UPDATED_AFTER = 'filter.user.profile_updated_after';

    case USER_DELETED_BEFORE = 'filter.user.deleted_before';
    case USER_DELETED_AFTER = 'filter.user.deleted_after';

    case USER_BULK_DELETED_BEFORE = 'filter.user.bulk_deleted_before';
    case USER_BULK_DELETED_AFTER = 'filter.user.bulk_deleted_after';

    // Validation hooks.
    case USER_STORE_VALIDATION_RULES = 'filter.user.store.validation.rules';
    case USER_UPDATE_VALIDATION_RULES = 'filter.user.update.validation.rules';

    // UI Hooks.
    case PROFILE_AFTER_BREADCRUMBS = 'filter.profile.after_breadcrumbs';
    case PROFILE_AFTER_FORM = 'filter.profile.after_form';
    case USER_AFTER_BREADCRUMBS = 'filter.users.after_breadcrumbs';
    case USER_AFTER_TABLE = 'filter.users.after_table';

    // User Show Page Hooks.
    case USER_SHOW_AFTER_BREADCRUMBS = 'filter.user.show.after_breadcrumbs';
    case USER_SHOW_AFTER_MAIN_CONTENT = 'filter.user.show.after_main_content';
    case USER_SHOW_AFTER_SIDEBAR = 'filter.user.show.after_sidebar';
    case USER_SHOW_AFTER_CONTENT = 'filter.user.show.after_content';

    // User Form Field Hooks.
    case USER_FORM_AFTER_AVATAR = 'filter.user.form.after_avatar';
    case USER_FORM_AFTER_SOCIAL_LINKS = 'filter.user.form.after_social_links';
    case USER_FORM_AFTER_FIRST_NAME = 'filter.user.form.after_first_name';
    case USER_FORM_AFTER_LAST_NAME = 'filter.user.form.after_last_name';
    case USER_FORM_AFTER_USERNAME = 'filter.user.form.after_username';
    case USER_FORM_AFTER_EMAIL = 'filter.user.form.after_email';
    case USER_FORM_AFTER_PASSWORD = 'filter.user.form.after_password';
    case USER_FORM_AFTER_CONFIRM_PASSWORD = 'filter.user.form.after_confirm_password';
    case USER_FORM_AFTER_ROLES = 'filter.user.form.after_roles';
    case USER_FORM_AFTER_DISPLAY_NAME = 'filter.user.form.after_display_name';
    case USER_FORM_AFTER_BIO = 'filter.user.form.after_bio';
    case USER_FORM_AFTER_TIMEZONE = 'filter.user.form.after_timezone';
    case USER_FORM_AFTER_LOCALE = 'filter.user.form.after_locale';
}
