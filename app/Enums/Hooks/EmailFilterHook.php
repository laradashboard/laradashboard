<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Email Filter Hooks
 *
 * Provides filter hooks for emails that modules can use to modify behavior.
 * Filter hooks receive a value and must return it (modified or not).
 *
 * @example
 * // Add custom email variables
 * Hook::addFilter(EmailFilterHook::EMAIL_VARIABLES, function ($variables) {
 *     $variables['custom_var'] = 'Custom Value';
 *     return $variables;
 * });
 */
enum EmailFilterHook: string
{
    // ==========================================================================
    // Email Content Filters
    // ==========================================================================

    /**
     * Filter the email subject before sending.
     *
     * @param string $subject The email subject
     * @param array $variables Available variables
     * @return string Modified subject
     */
    case EMAIL_SUBJECT = 'filter.email.subject';

    /**
     * Filter the email content before sending.
     *
     * @param string $content The email content
     * @param array $variables Available variables
     * @return string Modified content
     */
    case EMAIL_CONTENT = 'filter.email.content';

    /**
     * Filter the email recipient before sending.
     *
     * @param string $recipient The recipient email
     * @return string Modified recipient
     */
    case EMAIL_RECIPIENT = 'filter.email.recipient';

    /**
     * Filter the from address.
     *
     * @param string $from The from email address
     * @return string Modified from address
     */
    case EMAIL_FROM_ADDRESS = 'filter.email.from_address';

    /**
     * Filter the from name.
     *
     * @param string $name The from name
     * @return string Modified from name
     */
    case EMAIL_FROM_NAME = 'filter.email.from_name';

    /**
     * Filter the reply-to address.
     *
     * @param string $replyTo The reply-to email
     * @return string Modified reply-to
     */
    case EMAIL_REPLY_TO = 'filter.email.reply_to';

    // ==========================================================================
    // Email Variables
    // ==========================================================================

    /**
     * Filter available email template variables.
     * Use this to add custom variables for email templates.
     *
     * @param array $variables The available variables
     * @return array Modified variables
     */
    case EMAIL_VARIABLES = 'filter.email.variables';

    /**
     * Filter the replacement data for email variables.
     *
     * @param array $data The replacement data
     * @return array Modified replacement data
     */
    case EMAIL_REPLACEMENT_DATA = 'filter.email.replacement_data';

    // ==========================================================================
    // Email Headers
    // ==========================================================================

    /**
     * Filter email headers before sending.
     *
     * @param array $headers The email headers
     * @return array Modified headers
     */
    case EMAIL_HEADERS = 'filter.email.headers';

    /**
     * Filter email attachments before sending.
     *
     * @param array $attachments The attachments
     * @return array Modified attachments
     */
    case EMAIL_ATTACHMENTS = 'filter.email.attachments';

    // ==========================================================================
    // Email Template Filters
    // ==========================================================================

    /**
     * Filter email template data before creation.
     *
     * @param array $data The template data
     * @return array Modified data
     */
    case EMAIL_TEMPLATE_DATA = 'filter.email.template.data';

    /**
     * Filter the list of available email templates.
     *
     * @param array $templates The templates
     * @return array Modified templates
     */
    case EMAIL_TEMPLATES_LIST = 'filter.email.templates.list';

    // ==========================================================================
    // Validation Hooks
    // ==========================================================================

    /**
     * Filter validation rules for storing email templates.
     *
     * @param array $rules The validation rules
     * @return array Modified rules
     */
    case EMAIL_TEMPLATE_STORE_VALIDATION_RULES = 'filter.email.template.store.validation.rules';

    /**
     * Filter validation rules for updating email templates.
     *
     * @param array $rules The validation rules
     * @return array Modified rules
     */
    case EMAIL_TEMPLATE_UPDATE_VALIDATION_RULES = 'filter.email.template.update.validation.rules';

    // ==========================================================================
    // UI Hooks - Index Page
    // ==========================================================================

    /**
     * Hook after the email templates page breadcrumbs.
     */
    case EMAIL_TEMPLATES_AFTER_BREADCRUMBS = 'filter.email.templates.after_breadcrumbs';

    /**
     * Hook before the email templates table.
     */
    case EMAIL_TEMPLATES_BEFORE_TABLE = 'filter.email.templates.before_table';

    /**
     * Hook after the email templates table.
     */
    case EMAIL_TEMPLATES_AFTER_TABLE = 'filter.email.templates.after_table';

    // ==========================================================================
    // UI Hooks - Show Page
    // ==========================================================================

    /**
     * Hook after the email template show page breadcrumbs.
     */
    case EMAIL_TEMPLATE_SHOW_AFTER_BREADCRUMBS = 'filter.email.template.show.after_breadcrumbs';

    /**
     * Hook after the email template show page content.
     */
    case EMAIL_TEMPLATE_SHOW_AFTER_CONTENT = 'filter.email.template.show.after_content';

    // ==========================================================================
    // UI Hooks - Email Composer
    // ==========================================================================

    /**
     * Hook for adding custom sections to the email composer.
     */
    case EMAIL_COMPOSER_SECTIONS = 'filter.email.composer.sections';

    /**
     * Hook for adding custom blocks to the email builder.
     */
    case EMAIL_BUILDER_BLOCKS = 'filter.email.builder.blocks';
}
