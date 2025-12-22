<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Auth Filter Hooks
 *
 * Provides filter hooks for authentication that plugins/themes can use to modify behavior.
 */
enum AuthFilterHook: string
{
    // ==========================================================================
    // Login Filters
    // ==========================================================================
    case LOGIN_VALIDATION_RULES = 'filter.auth.login.validation_rules';
    case LOGIN_VALIDATION_MESSAGES = 'filter.auth.login.validation_messages';
    case LOGIN_CREDENTIALS = 'filter.auth.login.credentials';
    case LOGIN_REDIRECT_PATH = 'filter.auth.login.redirect_path';
    case LOGIN_FORM_FIELDS = 'filter.auth.login.form_fields';
    case LOGIN_PAGE_TITLE = 'filter.auth.login.page_title';
    case LOGIN_PAGE_DESCRIPTION = 'filter.auth.login.page_description';
    case LOGIN_VIEW = 'filter.auth.login.view';

    // ==========================================================================
    // Registration Filters
    // ==========================================================================
    case REGISTER_VALIDATION_RULES = 'filter.auth.register.validation_rules';
    case REGISTER_VALIDATION_MESSAGES = 'filter.auth.register.validation_messages';
    case REGISTER_USER_DATA = 'filter.auth.register.user_data';
    case REGISTER_REDIRECT_PATH = 'filter.auth.register.redirect_path';
    case REGISTER_FORM_FIELDS = 'filter.auth.register.form_fields';
    case REGISTER_DEFAULT_ROLE = 'filter.auth.register.default_role';
    case REGISTER_PAGE_TITLE = 'filter.auth.register.page_title';
    case REGISTER_PAGE_DESCRIPTION = 'filter.auth.register.page_description';
    case REGISTER_VIEW = 'filter.auth.register.view';

    // ==========================================================================
    // Password Reset Filters
    // ==========================================================================
    case PASSWORD_RESET_VALIDATION_RULES = 'filter.auth.password_reset.validation_rules';
    case PASSWORD_RESET_REDIRECT_PATH = 'filter.auth.password_reset.redirect_path';
    case PASSWORD_RESET_REQUEST_VIEW = 'filter.auth.password_reset.request_view';
    case PASSWORD_RESET_VIEW = 'filter.auth.password_reset.view';
    case PASSWORD_RESET_FORM_BEFORE = 'filter.auth.password_reset.form_before';
    case PASSWORD_RESET_FORM_AFTER = 'filter.auth.password_reset.form_after';

    // ==========================================================================
    // Logout Filters
    // ==========================================================================
    case LOGOUT_REDIRECT_PATH = 'filter.auth.logout.redirect_path';

    // ==========================================================================
    // General Auth Filters
    // ==========================================================================
    case AUTH_LAYOUT = 'filter.auth.layout';
    case AUTH_LOGO = 'filter.auth.logo';
    case AUTH_SIDEBAR_CONTENT = 'filter.auth.sidebar_content';
    case AUTH_FOOTER_CONTENT = 'filter.auth.footer_content';
    case AUTH_SOCIAL_LOGIN_PROVIDERS = 'filter.auth.social_login_providers';
    case AUTH_ENABLED_FEATURES = 'filter.auth.enabled_features';

    // ==========================================================================
    // UI Slot Filters
    // ==========================================================================
    case LOGIN_FORM_BEFORE = 'filter.auth.login.form_before';
    case LOGIN_FORM_AFTER = 'filter.auth.login.form_after';
    case LOGIN_FORM_FIELDS_BEFORE_EMAIL = 'filter.auth.login.fields_before_email';
    case LOGIN_FORM_FIELDS_AFTER_EMAIL = 'filter.auth.login.fields_after_email';
    case LOGIN_FORM_FIELDS_BEFORE_PASSWORD = 'filter.auth.login.fields_before_password';
    case LOGIN_FORM_FIELDS_AFTER_PASSWORD = 'filter.auth.login.fields_after_password';
    case LOGIN_FORM_FIELDS_BEFORE_SUBMIT = 'filter.auth.login.fields_before_submit';
    case LOGIN_FORM_FIELDS_AFTER_SUBMIT = 'filter.auth.login.fields_after_submit';

    case REGISTER_FORM_BEFORE = 'filter.auth.register.form_before';
    case REGISTER_FORM_AFTER = 'filter.auth.register.form_after';
    case REGISTER_FORM_FIELDS_BEFORE = 'filter.auth.register.fields_before';
    case REGISTER_FORM_FIELDS_AFTER = 'filter.auth.register.fields_after';
    case REGISTER_FORM_FIELDS_BEFORE_SUBMIT = 'filter.auth.register.fields_before_submit';
    case REGISTER_FORM_FIELDS_AFTER_SUBMIT = 'filter.auth.register.fields_after_submit';

    // ==========================================================================
    // Settings UI Hooks
    // ==========================================================================
    case SETTINGS_AUTH_TAB_BEFORE_SECTION_START = 'filter.settings.auth_tab_before_section_start';
    case SETTINGS_AUTH_TAB_BEFORE_SECTION_END = 'filter.settings.auth_tab_before_section_end';
    case SETTINGS_AUTH_TAB_AFTER_SECTION_END = 'filter.settings.auth_tab_after_section_end';
}
