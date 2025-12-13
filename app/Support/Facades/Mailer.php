<?php

declare(strict_types=1);

namespace App\Support\Facades;

use App\Models\EmailConnection;
use App\Services\Emails\Mailer as MailerService;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\PendingMail;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Facade;

/**
 * Mailer Facade
 *
 * Provides a clean interface for sending emails through configured email connections.
 *
 * @method static static connection(string|int $identifier) Use a specific connection by name or ID
 * @method static static useDefault() Use Laravel's default mail configuration (bypass email connections)
 * @method static EmailConnection|null getActiveConnection() Get the currently resolved connection
 * @method static PendingMail to(mixed $users) Begin fluent message construction with recipients
 * @method static PendingMail cc(mixed $users) Begin fluent message construction with CC recipients
 * @method static PendingMail bcc(mixed $users) Begin fluent message construction with BCC recipients
 * @method static SentMessage|null raw(string $text, \Closure $callback) Send a raw text email
 * @method static SentMessage|null send(string|array|Mailable $view, array $data = [], \Closure|null $callback = null) Send an email
 * @method static SentMessage|null sendMailable(Mailable $mailable) Send a Mailable instance
 * @method static SentMessage|null html(string $html, \Closure $callback) Send HTML content directly
 * @method static mixed queue(Mailable $mailable, string|null $queue = null) Queue a mailable
 * @method static mixed later(\DateTimeInterface|\DateInterval|int $delay, Mailable $mailable, string|null $queue = null) Queue a mailable with delay
 * @method static MailerContract getMailer() Get the underlying Laravel Mailer instance
 * @method static bool hasActiveConnection() Check if any email connection is configured and active
 * @method static array|null getConnectionInfo() Get information about the currently configured connection
 * @method static MailerService fresh() Create a fresh instance
 *
 * @see \App\Services\Emails\Mailer
 */
class Mailer extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return MailerService::class;
    }
}
