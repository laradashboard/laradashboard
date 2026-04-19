<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Filter hooks for the email template variable system.
 *
 * Modules use these hooks to register additional merge variables
 * (e.g. `{{contact.first_name}}`, `{{organization.name}}`) that can be
 * referenced inside email template bodies, subjects, and workflow
 * Send Email actions.
 */
enum EmailVariableFilterHook: string
{
    /**
     * Filters the full variable definition array.
     *
     * Receives:  array<string, array{label: string, sample_data: mixed, replacement?: mixed, description?: string, group?: string}>
     * Returns:   the same structure, optionally with extra keys.
     *
     * The string value matches the legacy `email_template_variables_data`
     * hook tag so existing `Hook::addFilter('email_template_variables_data', …)`
     * callers keep working without any change.
     */
    case VARIABLES = 'email_template_variables_data';
}
