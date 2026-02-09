<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\InboundEmail;

/**
 * Interface for modules to handle inbound emails.
 *
 * Modules implement this interface to process specific types of incoming emails,
 * such as CRM ticket replies, support requests, etc.
 */
interface InboundEmailHandlerInterface
{
    /**
     * Get the unique identifier for this handler.
     *
     * @return string e.g., 'crm.ticket', 'support.reply'
     */
    public function getHandlerType(): string;

    /**
     * Get the display name for this handler.
     */
    public function getName(): string;

    /**
     * Get the priority of this handler (lower = higher priority).
     *
     * When multiple handlers can match an email, the one with
     * the lowest priority number is used first.
     */
    public function getPriority(): int;

    /**
     * Determine if this handler can process the given email.
     *
     * This method should check if the email matches criteria
     * for this handler (e.g., checking In-Reply-To header against
     * known message IDs, checking subject patterns, etc.)
     *
     * @param  InboundEmail  $email  The inbound email to check
     * @return bool True if this handler can process the email
     */
    public function canHandle(InboundEmail $email): bool;

    /**
     * Process the inbound email.
     *
     * This method should create the appropriate records in the module
     * (e.g., create a ticket reply, update a conversation, etc.)
     *
     * @param  InboundEmail  $email  The inbound email to process
     * @return InboundEmailHandlerResult The result of processing
     */
    public function handle(InboundEmail $email): InboundEmailHandlerResult;
}
