<?php

declare(strict_types=1);

namespace App\Services\EmailProviders;

use App\Contracts\EmailProviderInterface;
use App\Models\EmailConnection;
use Illuminate\Support\Facades\Mail;

class SmtpProvider implements EmailProviderInterface
{
    public function getKey(): string
    {
        return 'smtp';
    }

    public function getName(): string
    {
        return __('SMTP');
    }

    public function getIcon(): string
    {
        return 'lucide:server';
    }

    public function getDescription(): string
    {
        return __('Connect to any SMTP server. Recommended for better deliverability and tracking.');
    }

    public function getFormFields(): array
    {
        return [
            [
                'name' => 'host',
                'label' => __('SMTP Host'),
                'type' => 'text',
                'required' => true,
                'placeholder' => 'smtp.example.com',
                'help' => __('The hostname of your SMTP server.'),
            ],
            [
                'name' => 'port',
                'label' => __('SMTP Port'),
                'type' => 'number',
                'required' => true,
                'default' => 587,
                'placeholder' => '587',
                'help' => __('Common ports: 25, 465 (SSL), 587 (TLS).'),
            ],
            [
                'name' => 'encryption',
                'label' => __('Encryption'),
                'type' => 'select',
                'required' => false,
                'default' => 'tls',
                'options' => [
                    ['value' => '', 'label' => __('None')],
                    ['value' => 'tls', 'label' => 'TLS'],
                    ['value' => 'ssl', 'label' => 'SSL'],
                ],
                'help' => __('TLS is recommended for port 587. SSL for port 465.'),
            ],
            [
                'name' => 'username',
                'label' => __('Username'),
                'type' => 'text',
                'required' => false,
                'placeholder' => 'your-username',
                'help' => __('SMTP authentication username (usually your email address).'),
                'is_credential' => true,
            ],
            [
                'name' => 'password',
                'label' => __('Password'),
                'type' => 'password',
                'required' => false,
                'placeholder' => '********',
                'help' => __('SMTP authentication password or app-specific password.'),
                'is_credential' => true,
            ],
            [
                'name' => 'timeout',
                'label' => __('Timeout (seconds)'),
                'type' => 'number',
                'required' => false,
                'default' => 30,
                'placeholder' => '30',
                'help' => __('Connection timeout in seconds.'),
            ],
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'settings.host' => ['required', 'string', 'max:255'],
            'settings.port' => ['required', 'integer', 'min:1', 'max:65535'],
            'settings.encryption' => ['nullable', 'string', 'in:,tls,ssl'],
            'settings.timeout' => ['nullable', 'integer', 'min:1', 'max:300'],
            'credentials.username' => ['nullable', 'string', 'max:255'],
            'credentials.password' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function getTransportConfig(EmailConnection $connection): array
    {
        $settings = $connection->settings ?? [];
        $credentials = $connection->credentials ?? [];

        return [
            'transport' => 'smtp',
            'host' => $settings['host'] ?? '',
            'port' => (int) ($settings['port'] ?? 587),
            'encryption' => $settings['encryption'] ?? null,
            'username' => $credentials['username'] ?? null,
            'password' => $credentials['password'] ?? null,
            'timeout' => (int) ($settings['timeout'] ?? 30),
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
                __('This is a test email from :app to verify your SMTP connection is working correctly.', [
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
