<?php

declare(strict_types=1);

namespace App\Services\EmailProviders;

use App\Contracts\EmailProviderInterface;
use App\Models\EmailConnection;
use Illuminate\Support\Facades\Mail;

class PhpMailProvider implements EmailProviderInterface
{
    public function getKey(): string
    {
        return 'php_mail';
    }

    public function getName(): string
    {
        return __('PHP Mail');
    }

    public function getIcon(): string
    {
        return 'lucide:mail';
    }

    public function getDescription(): string
    {
        return __('Use PHP\'s built-in mail() function. Simple but may have deliverability issues.');
    }

    public function getFormFields(): array
    {
        return [
            [
                'name' => 'sendmail_path',
                'label' => __('Sendmail Path'),
                'type' => 'text',
                'required' => false,
                'default' => '/usr/sbin/sendmail -bs -i',
                'placeholder' => '/usr/sbin/sendmail -bs -i',
                'help' => __('The path to the sendmail binary. Leave default unless you know what you\'re doing.'),
            ],
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'settings.sendmail_path' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function getTransportConfig(EmailConnection $connection): array
    {
        $settings = $connection->settings ?? [];

        return [
            'transport' => 'sendmail',
            'path' => $settings['sendmail_path'] ?? '/usr/sbin/sendmail -bs -i',
        ];
    }

    public function testConnection(EmailConnection $connection, string $testEmail): array
    {
        try {
            $config = $this->getTransportConfig($connection);

            config([
                'mail.mailers.test_connection' => $config,
            ]);

            Mail::mailer('test_connection')->raw(
                __('This is a test email from :app to verify your PHP Mail connection is working correctly.', [
                    'app' => config('app.name'),
                ]),
                function ($message) use ($connection, $testEmail) {
                    $message->to($testEmail)
                        ->subject(__('Test Email - :name', ['name' => $connection->name]))
                        ->from($connection->from_email, $connection->from_name);
                }
            );

            return [
                'success' => true,
                'message' => __('Test email sent successfully to :email', ['email' => $testEmail]),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
