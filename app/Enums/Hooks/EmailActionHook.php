<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Email Action Hooks
 *
 * Provides action hooks for email events that modules can hook into.
 * Action hooks are fire-and-forget - they notify listeners but don't expect return values.
 *
 * @example
 * // Log email sent events
 * Hook::addAction(EmailActionHook::EMAIL_SENT_AFTER, function ($recipient, $subject, $content) {
 *     Log::info("Email sent to {$recipient}: {$subject}");
 * });
 */
enum EmailActionHook: string
{
    // ==========================================================================
    // Email Sending Events
    // ==========================================================================

    /**
     * Fired before an email is sent.
     *
     * @param string $recipient The recipient email address
     * @param string $subject The email subject
     * @param string $content The email content
     */
    case EMAIL_SENDING_BEFORE = 'action.email.sending_before';

    /**
     * Fired after an email is successfully sent.
     *
     * @param string $recipient The recipient email address
     * @param string $subject The email subject
     * @param string $content The email content
     */
    case EMAIL_SENT_AFTER = 'action.email.sent_after';

    /**
     * Fired when an email fails to send.
     *
     * @param string $recipient The recipient email address
     * @param string $subject The email subject
     * @param \Throwable $exception The exception that occurred
     */
    case EMAIL_SEND_FAILED = 'action.email.send_failed';

    // ==========================================================================
    // Email Template Events
    // ==========================================================================

    /**
     * Fired before an email template is created.
     *
     * @param array $data The template data
     */
    case EMAIL_TEMPLATE_CREATED_BEFORE = 'action.email.template.created_before';

    /**
     * Fired after an email template is created.
     *
     * @param mixed $template The created template
     */
    case EMAIL_TEMPLATE_CREATED_AFTER = 'action.email.template.created_after';

    /**
     * Fired before an email template is updated.
     *
     * @param mixed $template The template being updated
     * @param array $data The new data
     */
    case EMAIL_TEMPLATE_UPDATED_BEFORE = 'action.email.template.updated_before';

    /**
     * Fired after an email template is updated.
     *
     * @param mixed $template The updated template
     */
    case EMAIL_TEMPLATE_UPDATED_AFTER = 'action.email.template.updated_after';

    /**
     * Fired before an email template is deleted.
     *
     * @param mixed $template The template being deleted
     */
    case EMAIL_TEMPLATE_DELETED_BEFORE = 'action.email.template.deleted_before';

    /**
     * Fired after an email template is deleted.
     *
     * @param int $templateId The ID of the deleted template
     */
    case EMAIL_TEMPLATE_DELETED_AFTER = 'action.email.template.deleted_after';

    // ==========================================================================
    // Bulk Email Events
    // ==========================================================================

    /**
     * Fired before a bulk email campaign starts.
     *
     * @param array $recipients The list of recipients
     * @param string $subject The email subject
     */
    case BULK_EMAIL_STARTED = 'action.email.bulk.started';

    /**
     * Fired after a bulk email campaign completes.
     *
     * @param array $results The results of the bulk send
     */
    case BULK_EMAIL_COMPLETED = 'action.email.bulk.completed';

    /**
     * Fired when a single email in a bulk campaign is sent.
     *
     * @param string $recipient The recipient
     * @param int $index The index in the batch
     */
    case BULK_EMAIL_SINGLE_SENT = 'action.email.bulk.single_sent';
}
