<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\EmailConnection;

interface EmailProviderInterface
{
    /**
     * Get the unique key for this provider (e.g., 'php_mail', 'smtp').
     */
    public function getKey(): string;

    /**
     * Get the display name for this provider.
     */
    public function getName(): string;

    /**
     * Get the icon for this provider (Iconify format).
     */
    public function getIcon(): string;

    /**
     * Get a short description of this provider.
     */
    public function getDescription(): string;

    /**
     * Get the form field definitions for provider-specific settings.
     *
     * @return array<int, array{
     *     name: string,
     *     label: string,
     *     type: string,
     *     required?: bool,
     *     default?: mixed,
     *     placeholder?: string,
     *     help?: string,
     *     options?: array,
     *     is_credential?: bool,
     * }>
     */
    public function getFormFields(): array;

    /**
     * Get the validation rules for provider-specific settings.
     *
     * @return array Validation rules for Laravel validator
     */
    public function getValidationRules(): array;

    /**
     * Get the Laravel mail transport configuration for this connection.
     *
     * @param  EmailConnection  $connection  The connection instance
     * @return array The transport configuration array
     */
    public function getTransportConfig(EmailConnection $connection): array;

    /**
     * Test the connection and return the result.
     *
     * @param  EmailConnection  $connection  The connection to test
     * @param  string  $testEmail  The email address to send test to
     * @return array{success: bool, message: string}
     */
    public function testConnection(EmailConnection $connection, string $testEmail): array;
}
