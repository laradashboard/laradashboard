<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Auth Action Hooks
 *
 * Provides action hooks for authentication events that plugins/themes can hook into.
 */
enum AuthActionHook: string
{
    // Login Events
    case BEFORE_LOGIN_FORM_RENDER = 'action.auth.before_login_form_render';
    case AFTER_LOGIN_FORM_RENDER = 'action.auth.after_login_form_render';
    case BEFORE_LOGIN_ATTEMPT = 'action.auth.before_login_attempt';
    case AFTER_LOGIN_SUCCESS = 'action.auth.after_login_success';
    case AFTER_LOGIN_FAILED = 'action.auth.after_login_failed';

    // Registration Events
    case BEFORE_REGISTER_FORM_RENDER = 'action.auth.before_register_form_render';
    case AFTER_REGISTER_FORM_RENDER = 'action.auth.after_register_form_render';
    case BEFORE_REGISTRATION = 'action.auth.before_registration';
    case AFTER_REGISTRATION_SUCCESS = 'action.auth.after_registration_success';
    case AFTER_REGISTRATION_FAILED = 'action.auth.after_registration_failed';

    // Password Reset Events
    case BEFORE_PASSWORD_RESET_REQUEST = 'action.auth.before_password_reset_request';
    case AFTER_PASSWORD_RESET_EMAIL_SENT = 'action.auth.after_password_reset_email_sent';
    case AFTER_PASSWORD_RESET_SUCCESS = 'action.auth.after_password_reset_success';

    // Email Verification Events
    case AFTER_EMAIL_VERIFIED = 'action.auth.after_email_verified';
    case AFTER_VERIFICATION_EMAIL_SENT = 'action.auth.after_verification_email_sent';

    // Logout Events
    case BEFORE_LOGOUT = 'action.auth.before_logout';
    case AFTER_LOGOUT = 'action.auth.after_logout';
}
