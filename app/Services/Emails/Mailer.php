<?php

declare(strict_types=1);

namespace App\Services\Emails;

use App\Models\EmailConnection;
use App\Services\EmailConnectionService;
use App\Services\EmailProviderRegistry;
use Closure;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\PendingMail;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Mailer - Unified email sending service that leverages the Email Connections system.
 *
 * This service provides a clean interface for sending emails through admin-configured
 * email connections, with automatic fallback to Laravel's default mail configuration.
 *
 * Usage:
 *   // Via dependency injection
 *   $mailer->to('user@example.com')->send($mailable);
 *
 *   // Via facade
 *   Mailer::to('user@example.com')->send($mailable);
 *
 *   // Raw email
 *   Mailer::raw('Hello!', fn($m) => $m->to('user@example.com')->subject('Hi'));
 *
 *   // Using specific connection
 *   Mailer::connection('marketing')->to('user@example.com')->send($mailable);
 */
class Mailer
{
    /**
     * The mailer name used for dynamic connections.
     */
    protected const DYNAMIC_MAILER_NAME = 'dynamic_connection';

    /**
     * Currently active connection for this mailer instance.
     */
    protected ?EmailConnection $activeConnection = null;

    /**
     * Whether to use the default connection automatically.
     */
    protected bool $useDefaultConnection = true;

    /**
     * Force a specific connection by ID or name.
     */
    protected ?string $forcedConnectionIdentifier = null;

    public function __construct(
        protected EmailConnectionService $connectionService
    ) {
    }

    /**
     * Use a specific connection by name or ID.
     *
     * @param  string|int  $identifier  Connection name or ID
     */
    public function connection(string|int $identifier): static
    {
        $clone = clone $this;
        $clone->forcedConnectionIdentifier = (string) $identifier;
        $clone->useDefaultConnection = false;

        return $clone;
    }

    /**
     * Use Laravel's default mail configuration (bypass email connections).
     */
    public function useDefault(): static
    {
        $clone = clone $this;
        $clone->useDefaultConnection = false;
        $clone->forcedConnectionIdentifier = null;
        $clone->activeConnection = null;

        return $clone;
    }

    /**
     * Get the currently resolved connection (or null for default Laravel config).
     */
    public function getActiveConnection(): ?EmailConnection
    {
        return $this->resolveConnection();
    }

    /**
     * Begin the fluent message construction - specify recipients.
     *
     * @param  mixed  $users  Email address(es) or user objects with email property
     */
    public function to(mixed $users): PendingMail
    {
        return $this->getMailer()->to($users);
    }

    /**
     * Begin the fluent message construction - specify CC recipients.
     */
    public function cc(mixed $users): PendingMail
    {
        return $this->getMailer()->cc($users);
    }

    /**
     * Begin the fluent message construction - specify BCC recipients.
     */
    public function bcc(mixed $users): PendingMail
    {
        return $this->getMailer()->bcc($users);
    }

    /**
     * Send a raw text email.
     *
     * @param  string  $text  The raw text content
     * @param  Closure  $callback  Callback to configure the message
     */
    public function raw(string $text, Closure $callback): ?SentMessage
    {
        $connection = $this->resolveConnection();
        $callback = $this->wrapCallbackWithConnectionDefaults($callback, $connection);

        return $this->getMailer()->raw($text, $callback);
    }

    /**
     * Send an email using a view/template.
     *
     * @param  string|array  $view  The view name or array of views
     * @param  array  $data  Data to pass to the view
     * @param  Closure|null  $callback  Callback to configure the message
     */
    public function send(string|array|Mailable $view, array $data = [], ?Closure $callback = null): ?SentMessage
    {
        if ($view instanceof Mailable) {
            return $this->sendMailable($view);
        }

        $connection = $this->resolveConnection();
        if ($callback) {
            $callback = $this->wrapCallbackWithConnectionDefaults($callback, $connection);
        }

        return $this->getMailer()->send($view, $data, $callback);
    }

    /**
     * Send a Mailable instance.
     */
    public function sendMailable(Mailable $mailable): ?SentMessage
    {
        $connection = $this->resolveConnection();

        // Apply connection defaults to mailable if configured
        if ($connection) {
            $this->applyConnectionDefaultsToMailable($mailable, $connection);
        }

        return $this->getMailer()->send($mailable);
    }

    /**
     * Queue a mailable for sending.
     */
    public function queue(Mailable $mailable, ?string $queue = null): mixed
    {
        $connection = $this->resolveConnection();

        if ($connection) {
            $this->applyConnectionDefaultsToMailable($mailable, $connection);
        }

        return $this->getMailer()->queue($mailable, $queue);
    }

    /**
     * Queue a mailable for sending after a delay.
     */
    public function later(\DateTimeInterface|\DateInterval|int $delay, Mailable $mailable, ?string $queue = null): mixed
    {
        $connection = $this->resolveConnection();

        if ($connection) {
            $this->applyConnectionDefaultsToMailable($mailable, $connection);
        }

        return $this->getMailer()->later($delay, $mailable, $queue);
    }

    /**
     * Send HTML content directly.
     *
     * @param  string  $html  HTML content
     * @param  Closure  $callback  Callback to configure the message
     */
    public function html(string $html, Closure $callback): ?SentMessage
    {
        $connection = $this->resolveConnection();
        $callback = $this->wrapCallbackWithConnectionDefaults($callback, $connection);

        return $this->getMailer()->send([], [], function ($message) use ($html, $callback) {
            $message->html($html);
            $callback($message);
        });
    }

    /**
     * Get the underlying Laravel Mailer instance configured for the active connection.
     */
    public function getMailer(): MailerContract
    {
        $connection = $this->resolveConnection();

        if (! $connection) {
            // Use Laravel's default mailer
            return Mail::mailer();
        }

        // Configure and return dynamic mailer
        $this->configureMailerForConnection($connection);

        return Mail::mailer(self::DYNAMIC_MAILER_NAME);
    }

    /**
     * Resolve which connection to use based on configuration.
     */
    protected function resolveConnection(): ?EmailConnection
    {
        // Return cached connection if already resolved
        if ($this->activeConnection !== null) {
            return $this->activeConnection;
        }

        // If a specific connection is forced, find it
        if ($this->forcedConnectionIdentifier !== null) {
            $this->activeConnection = $this->findConnection($this->forcedConnectionIdentifier);

            if (! $this->activeConnection) {
                Log::warning('Mailer: Requested connection not found, falling back to default', [
                    'identifier' => $this->forcedConnectionIdentifier,
                ]);
            }

            return $this->activeConnection;
        }

        // Use the default/best connection if auto-connection is enabled
        if ($this->useDefaultConnection) {
            $this->activeConnection = $this->connectionService->getBestConnection();
        }

        return $this->activeConnection;
    }

    /**
     * Find a connection by name or ID.
     */
    protected function findConnection(string $identifier): ?EmailConnection
    {
        // Try by ID first
        if (is_numeric($identifier)) {
            $connection = EmailConnection::query()
                ->active()
                ->find((int) $identifier);

            if ($connection) {
                return $connection;
            }
        }

        // Try by name
        return EmailConnection::query()
            ->active()
            ->where('name', $identifier)
            ->first();
    }

    /**
     * Configure Laravel's mail system for the given connection.
     */
    protected function configureMailerForConnection(EmailConnection $connection): void
    {
        $provider = EmailProviderRegistry::getProvider($connection->provider_type);

        if (! $provider) {
            Log::error('Mailer: Unknown provider type', [
                'provider_type' => $connection->provider_type,
                'connection_id' => $connection->id,
            ]);

            throw new \RuntimeException("Unknown email provider type: {$connection->provider_type}");
        }

        $transportConfig = $provider->getTransportConfig($connection);

        // Register the dynamic mailer configuration
        Config::set('mail.mailers.' . self::DYNAMIC_MAILER_NAME, $transportConfig);

        // Set default from address if the connection has one
        if ($connection->from_email) {
            Config::set('mail.mailers.' . self::DYNAMIC_MAILER_NAME . '.from', [
                'address' => $connection->from_email,
                'name' => $connection->from_name,
            ]);
        }
    }

    /**
     * Wrap a callback to apply connection defaults (from address).
     */
    protected function wrapCallbackWithConnectionDefaults(Closure $callback, ?EmailConnection $connection): Closure
    {
        if (! $connection) {
            return $callback;
        }

        return function ($message) use ($callback, $connection) {
            // Apply the user's callback first
            $callback($message);

            // Override from address if force_from_email is enabled
            if ($connection->force_from_email && $connection->from_email) {
                $message->from($connection->from_email, $connection->from_name);
            }

            // If no from is set yet, use connection's default
            $currentFrom = $message->getFrom();
            if (empty($currentFrom) && $connection->from_email) {
                $message->from($connection->from_email, $connection->from_name);
            }
        };
    }

    /**
     * Apply connection defaults to a Mailable instance.
     */
    protected function applyConnectionDefaultsToMailable(Mailable $mailable, EmailConnection $connection): void
    {
        // Use reflection to check if 'from' is set
        $reflection = new \ReflectionClass($mailable);

        // If force_from is enabled, always override
        if ($connection->force_from_email && $connection->from_email) {
            $mailable->from($connection->from_email, $connection->from_name);

            return;
        }

        // Check if mailable has a from address set
        if ($reflection->hasProperty('from')) {
            $fromProperty = $reflection->getProperty('from');
            $fromProperty->setAccessible(true);
            $fromValue = $fromProperty->getValue($mailable);

            // If no from is set, use connection's default
            if (empty($fromValue) && $connection->from_email) {
                $mailable->from($connection->from_email, $connection->from_name);
            }
        }
    }

    /**
     * Check if any email connection is configured and active.
     */
    public function hasActiveConnection(): bool
    {
        return $this->connectionService->getBestConnection() !== null;
    }

    /**
     * Get information about the currently configured connection.
     *
     * @return array{name: string, provider: string, from_email: string}|null
     */
    public function getConnectionInfo(): ?array
    {
        $connection = $this->resolveConnection();

        if (! $connection) {
            return null;
        }

        return [
            'id' => $connection->id,
            'name' => $connection->name,
            'provider' => $connection->provider_type,
            'provider_label' => $connection->provider_label,
            'from_email' => $connection->from_email,
            'from_name' => $connection->from_name,
            'is_default' => $connection->is_default,
        ];
    }

    /**
     * Create a fresh instance (useful for testing or resetting state).
     */
    public function fresh(): self
    {
        return new self($this->connectionService);
    }
}
