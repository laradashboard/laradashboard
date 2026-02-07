<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Hook constants for the inbound email processing system.
 *
 * Modules can register handlers for these hooks to process incoming emails.
 */
enum InboundEmailHook: string
{
    /**
     * Called before any processing begins on an inbound email.
     * Handlers can return false to skip processing.
     */
    case BEFORE_PROCESS = 'inbound_email.before_process';

    /**
     * Main hook for processing an inbound email.
     * Handlers should attempt to match and handle the email.
     */
    case PROCESS = 'inbound_email.process';

    /**
     * Called after an email has been successfully processed.
     */
    case AFTER_PROCESS = 'inbound_email.after_process';

    /**
     * Called when email processing fails.
     */
    case PROCESS_FAILED = 'inbound_email.process_failed';

    /**
     * Called when no handler matched the email.
     */
    case UNMATCHED = 'inbound_email.unmatched';

    public function label(): string
    {
        return match ($this) {
            self::BEFORE_PROCESS => 'Before Process',
            self::PROCESS => 'Process',
            self::AFTER_PROCESS => 'After Process',
            self::PROCESS_FAILED => 'Process Failed',
            self::UNMATCHED => 'Unmatched',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::BEFORE_PROCESS => 'Called before any processing begins on an inbound email',
            self::PROCESS => 'Main hook for processing an inbound email',
            self::AFTER_PROCESS => 'Called after an email has been successfully processed',
            self::PROCESS_FAILED => 'Called when email processing fails',
            self::UNMATCHED => 'Called when no handler matched the email',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
