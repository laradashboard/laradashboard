# Email Provider System

The Email Provider System is an extensible architecture that allows custom modules to define their own email providers (e.g., SendGrid, Mailgun, Amazon SES, Postmark, etc.) while maintaining a unified interface for sending emails across the application.

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    EmailProviderInterface                        │
│         (Contract that all providers must implement)             │
└─────────────────────────────────────────────────────────────────┘
                              ▲
                              │ implements
          ┌───────────────────┼───────────────────┐
          │                   │                   │
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│  PhpMailProvider │  │   SmtpProvider   │  │ CustomProvider  │
│    (Built-in)    │  │    (Built-in)    │  │   (Your Module) │
└─────────────────┘  └─────────────────┘  └─────────────────┘
          │                   │                   │
          └───────────────────┼───────────────────┘
                              │ registers with
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                   EmailProviderRegistry                          │
│         (Central registry for all email providers)               │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                   EmailConnectionService                         │
│        (Service for managing connection CRUD operations)         │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                         Mailer                                   │
│   (Unified email sending service - USE THIS FOR ALL EMAILS!)     │
│                                                                   │
│   Facade: App\Support\Facades\Mailer                             │
│   Service: App\Services\Emails\Mailer                            │
└─────────────────────────────────────────────────────────────────┘
```

## Core Components

### 1. EmailProviderInterface

Located at: `app/Contracts/EmailProviderInterface.php`

This is the contract that all email providers must implement:

```php
interface EmailProviderInterface
{
    // Unique identifier (e.g., 'sendgrid', 'mailgun', 'ses')
    public function getKey(): string;

    // Display name (e.g., 'SendGrid', 'Mailgun', 'Amazon SES')
    public function getName(): string;

    // Iconify icon name (e.g., 'simple-icons:sendgrid')
    public function getIcon(): string;

    // Short description of the provider
    public function getDescription(): string;

    // Form field definitions for the UI
    public function getFormFields(): array;

    // Laravel validation rules for settings
    public function getValidationRules(): array;

    // Laravel mail transport configuration
    public function getTransportConfig(EmailConnection $connection): array;

    // Test the connection and send a test email
    public function testConnection(EmailConnection $connection, string $testEmail): array;
}
```

### 2. EmailProviderRegistry

Located at: `app/Services/EmailProviderRegistry.php`

Central registry that manages all email providers. Providers register themselves here, and the system uses this registry to:
- List available providers in the UI
- Get provider instances for configuration
- Retrieve form fields and validation rules

### 3. EmailConnectionService

Located at: `app/Services/EmailConnectionService.php`

Service layer for managing email connections:
- CRUD operations for connections
- Default connection management
- Connection testing
- Priority-based connection ordering

### 4. EmailConnection Model

Located at: `app/Models/EmailConnection.php`

Eloquent model storing connection configurations:
- Encrypted credentials storage
- Provider-specific settings (JSON)
- Active/inactive status
- Default flag
- Test status tracking

---

## Creating a Custom Email Provider

### Step 1: Create the Provider Class

Create your provider in your module's services directory:

```php
<?php

declare(strict_types=1);

namespace Modules\YourModule\Services\EmailProviders;

use App\Contracts\EmailProviderInterface;
use App\Models\EmailConnection;
use Illuminate\Support\Facades\Mail;

class SendGridProvider implements EmailProviderInterface
{
    /**
     * Unique key for this provider.
     * Must be snake_case and unique across all providers.
     */
    public function getKey(): string
    {
        return 'sendgrid';
    }

    /**
     * Display name shown in the UI.
     */
    public function getName(): string
    {
        return __('SendGrid');
    }

    /**
     * Iconify icon name for the UI.
     * Browse icons at: https://icon-sets.iconify.design/
     */
    public function getIcon(): string
    {
        return 'simple-icons:sendgrid';
    }

    /**
     * Brief description of the provider.
     */
    public function getDescription(): string
    {
        return __('Send emails via SendGrid API with advanced analytics and deliverability.');
    }

    /**
     * Define form fields for the provider configuration UI.
     *
     * Supported field types: text, password, number, select, checkbox
     *
     * Field properties:
     * - name: Field identifier (used in settings/credentials arrays)
     * - label: Display label
     * - type: Field type
     * - required: Whether field is required
     * - default: Default value
     * - placeholder: Input placeholder
     * - help: Help text shown below the field
     * - options: For select fields, array of {value, label} objects
     * - is_credential: If true, stored encrypted in 'credentials' column
     */
    public function getFormFields(): array
    {
        return [
            [
                'name' => 'api_key',
                'label' => __('API Key'),
                'type' => 'password',
                'required' => true,
                'placeholder' => 'SG.xxxxxxxxxxxxxxxxxxxx',
                'help' => __('Your SendGrid API key with Mail Send permissions.'),
                'is_credential' => true, // Stored encrypted!
            ],
            [
                'name' => 'endpoint',
                'label' => __('API Endpoint'),
                'type' => 'select',
                'required' => false,
                'default' => 'global',
                'options' => [
                    ['value' => 'global', 'label' => __('Global (api.sendgrid.com)')],
                    ['value' => 'eu', 'label' => __('EU (api.eu.sendgrid.com)')],
                ],
                'help' => __('Select EU endpoint for GDPR compliance.'),
            ],
            [
                'name' => 'sandbox_mode',
                'label' => __('Sandbox Mode'),
                'type' => 'checkbox',
                'required' => false,
                'default' => false,
                'help' => __('Enable sandbox mode for testing (emails won\'t be delivered).'),
            ],
        ];
    }

    /**
     * Validation rules for Laravel's validator.
     *
     * Use 'settings.field_name' for regular fields.
     * Use 'credentials.field_name' for credential fields.
     */
    public function getValidationRules(): array
    {
        return [
            'credentials.api_key' => ['required', 'string', 'min:20'],
            'settings.endpoint' => ['nullable', 'string', 'in:global,eu'],
            'settings.sandbox_mode' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Return Laravel mail transport configuration.
     *
     * This array is used to configure a dynamic mailer at runtime.
     * See Laravel docs for supported transport types.
     */
    public function getTransportConfig(EmailConnection $connection): array
    {
        $credentials = $connection->credentials ?? [];
        $settings = $connection->settings ?? [];

        $endpoint = match ($settings['endpoint'] ?? 'global') {
            'eu' => 'https://api.eu.sendgrid.com/v3/mail/send',
            default => 'https://api.sendgrid.com/v3/mail/send',
        };

        return [
            'transport' => 'sendgrid', // Or use 'smtp' for SMTP-based
            'api_key' => $credentials['api_key'] ?? '',
            'endpoint' => $endpoint,
        ];
    }

    /**
     * Test the connection by sending a test email.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(EmailConnection $connection, string $testEmail): array
    {
        try {
            $config = $this->getTransportConfig($connection);

            // Register a temporary mailer
            config(['mail.mailers.test_connection' => $config]);

            // Send test email
            Mail::mailer('test_connection')->raw(
                __('This is a test email from :app to verify your SendGrid connection.', [
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
```

### Step 2: Register the Provider

Register your provider in your module's service provider:

```php
<?php

namespace Modules\YourModule\Providers;

use App\Services\EmailProviderRegistry;
use Illuminate\Support\ServiceProvider;
use Modules\YourModule\Services\EmailProviders\SendGridProvider;
use Modules\YourModule\Services\EmailProviders\MailgunProvider;

class YourModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register email providers
        EmailProviderRegistry::registerProvider(SendGridProvider::class);
        EmailProviderRegistry::registerProvider(MailgunProvider::class);
    }
}
```

That's it! Your provider will now appear in the Email Connections UI.

---

## Using Form Field Types

### Text Field
```php
[
    'name' => 'host',
    'label' => __('SMTP Host'),
    'type' => 'text',
    'required' => true,
    'placeholder' => 'smtp.example.com',
    'help' => __('The hostname of your mail server.'),
]
```

### Password Field (for secrets)
```php
[
    'name' => 'api_key',
    'label' => __('API Key'),
    'type' => 'password',
    'required' => true,
    'is_credential' => true, // Important: encrypts the value
]
```

### Number Field
```php
[
    'name' => 'port',
    'label' => __('Port'),
    'type' => 'number',
    'required' => true,
    'default' => 587,
    'help' => __('Common ports: 25, 465 (SSL), 587 (TLS).'),
]
```

### Select Field
```php
[
    'name' => 'encryption',
    'label' => __('Encryption'),
    'type' => 'select',
    'default' => 'tls',
    'options' => [
        ['value' => '', 'label' => __('None')],
        ['value' => 'tls', 'label' => 'TLS'],
        ['value' => 'ssl', 'label' => 'SSL'],
    ],
]
```

### Checkbox Field
```php
[
    'name' => 'verify_peer',
    'label' => __('Verify SSL Certificate'),
    'type' => 'checkbox',
    'default' => true,
    'help' => __('Disable only for testing with self-signed certificates.'),
]
```

---

## Settings vs Credentials

The system separates regular settings from sensitive credentials:

| Storage | Column | Encryption | Access |
|---------|--------|------------|--------|
| Settings | `settings` (JSON) | No | `$connection->settings['field']` |
| Credentials | `credentials` (JSON) | Yes (AES-256) | `$connection->credentials['field']` |

Mark sensitive fields with `'is_credential' => true` in `getFormFields()` to ensure they're encrypted at rest.

---

## Complete Example: Mailgun Provider

```php
<?php

declare(strict_types=1);

namespace Modules\Email\Services\EmailProviders;

use App\Contracts\EmailProviderInterface;
use App\Models\EmailConnection;
use Illuminate\Support\Facades\Mail;

class MailgunProvider implements EmailProviderInterface
{
    public function getKey(): string
    {
        return 'mailgun';
    }

    public function getName(): string
    {
        return __('Mailgun');
    }

    public function getIcon(): string
    {
        return 'simple-icons:mailgun';
    }

    public function getDescription(): string
    {
        return __('Powerful transactional email API with detailed analytics.');
    }

    public function getFormFields(): array
    {
        return [
            [
                'name' => 'domain',
                'label' => __('Domain'),
                'type' => 'text',
                'required' => true,
                'placeholder' => 'mg.yourdomain.com',
                'help' => __('Your Mailgun sending domain.'),
            ],
            [
                'name' => 'secret',
                'label' => __('API Key'),
                'type' => 'password',
                'required' => true,
                'placeholder' => 'key-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                'help' => __('Your Mailgun private API key.'),
                'is_credential' => true,
            ],
            [
                'name' => 'region',
                'label' => __('Region'),
                'type' => 'select',
                'required' => true,
                'default' => 'us',
                'options' => [
                    ['value' => 'us', 'label' => __('US (api.mailgun.net)')],
                    ['value' => 'eu', 'label' => __('EU (api.eu.mailgun.net)')],
                ],
            ],
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'settings.domain' => ['required', 'string', 'max:255'],
            'settings.region' => ['required', 'string', 'in:us,eu'],
            'credentials.secret' => ['required', 'string', 'min:10'],
        ];
    }

    public function getTransportConfig(EmailConnection $connection): array
    {
        $settings = $connection->settings ?? [];
        $credentials = $connection->credentials ?? [];

        $endpoint = $settings['region'] === 'eu'
            ? 'api.eu.mailgun.net'
            : 'api.mailgun.net';

        return [
            'transport' => 'mailgun',
            'domain' => $settings['domain'] ?? '',
            'secret' => $credentials['secret'] ?? '',
            'endpoint' => $endpoint,
        ];
    }

    public function testConnection(EmailConnection $connection, string $testEmail): array
    {
        try {
            $config = $this->getTransportConfig($connection);

            config(['mail.mailers.test_connection' => $config]);

            Mail::mailer('test_connection')->raw(
                __('Test email from :app via Mailgun.', ['app' => config('app.name')]),
                function ($message) use ($connection, $testEmail) {
                    $message->to($testEmail)
                        ->subject(__('Test Email - :name', ['name' => $connection->name]))
                        ->from($connection->from_email, $connection->from_name);
                }
            );

            return [
                'success' => true,
                'message' => __('Test email sent to :email', ['email' => $testEmail]),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
```

---

## Sending Emails Using the Mailer Service

The `Mailer` service is the **recommended way** to send all emails in the application. It automatically uses the admin-configured default email connection with fallback to Laravel's default mail configuration.

### Using the Facade (Recommended)

```php
use App\Support\Facades\Mailer;

// Send a raw text email
Mailer::raw('Hello World!', function ($message) {
    $message->to('user@example.com')
        ->subject('Test Email');
});

// Send HTML email
Mailer::html('<h1>Hello!</h1><p>Welcome to our app.</p>', function ($message) {
    $message->to('user@example.com')
        ->subject('Welcome');
});

// Send a Mailable
Mailer::to('user@example.com')->send(new WelcomeMail($user));

// Queue a Mailable
Mailer::to('user@example.com')->queue(new NewsletterMail($content));

// Use a specific connection by name
Mailer::connection('marketing')->to('user@example.com')->send($mailable);

// Use a specific connection by ID
Mailer::connection(2)->raw('Hello!', fn($m) => $m->to('test@example.com'));

// Bypass email connections and use Laravel's default config
Mailer::useDefault()->to('user@example.com')->send($mailable);
```

### Using Dependency Injection

```php
use App\Services\Emails\Mailer;

class NotificationService
{
    public function __construct(
        protected Mailer $mailer
    ) {}

    public function sendWelcome(User $user): void
    {
        $this->mailer->to($user->email)->send(new WelcomeMail($user));
    }

    public function sendNewsletter(string $email, string $content): void
    {
        $this->mailer->html($content, function ($message) use ($email) {
            $message->to($email)
                ->subject('Weekly Newsletter');
        });
    }
}
```

### Checking Connection Status

```php
use App\Support\Facades\Mailer;

// Check if any email connection is configured
if (Mailer::hasActiveConnection()) {
    // A connection is available
}

// Get information about the active connection
$info = Mailer::getConnectionInfo();
// Returns: ['id' => 1, 'name' => 'Primary SMTP', 'provider' => 'smtp', ...]

// Get the actual connection model
$connection = Mailer::getActiveConnection();
```

### How It Works

1. **Auto-resolves connection**: Uses the default connection, or the highest priority active connection
2. **Configures transport**: Dynamically configures Laravel's mail system based on the connection's provider
3. **Applies from address**: Respects `force_from_email` and `force_from_name` settings
4. **Falls back gracefully**: Uses Laravel's default mail config if no connections are configured

### Migration Guide: Replacing `Mail::` with `Mailer::`

If you have existing code using Laravel's `Mail` facade:

```php
// Before
use Illuminate\Support\Facades\Mail;
Mail::to('user@example.com')->send(new WelcomeMail());

// After
use App\Support\Facades\Mailer;
Mailer::to('user@example.com')->send(new WelcomeMail());
```

The API is intentionally similar to Laravel's Mail facade for easy migration.

---

## Testing Your Provider

Create a feature test for your provider:

```php
<?php

use App\Services\EmailProviderRegistry;
use Modules\YourModule\Services\EmailProviders\SendGridProvider;
use App\Models\EmailConnection;

beforeEach(function () {
    // Register the provider
    EmailProviderRegistry::registerProvider(SendGridProvider::class);
});

it('registers the provider correctly', function () {
    expect(EmailProviderRegistry::hasProvider('sendgrid'))->toBeTrue();

    $provider = EmailProviderRegistry::getProvider('sendgrid');
    expect($provider)->toBeInstanceOf(SendGridProvider::class);
    expect($provider->getName())->toBe('SendGrid');
});

it('returns correct form fields', function () {
    $provider = EmailProviderRegistry::getProvider('sendgrid');
    $fields = $provider->getFormFields();

    expect($fields)->toBeArray();
    expect(collect($fields)->pluck('name'))->toContain('api_key');
});

it('validates settings correctly', function () {
    $provider = EmailProviderRegistry::getProvider('sendgrid');
    $rules = $provider->getValidationRules();

    expect($rules)->toHaveKey('credentials.api_key');
});

it('generates correct transport config', function () {
    $provider = EmailProviderRegistry::getProvider('sendgrid');

    $connection = EmailConnection::factory()->create([
        'provider_type' => 'sendgrid',
        'credentials' => ['api_key' => 'SG.test-key'],
        'settings' => ['endpoint' => 'eu'],
    ]);

    $config = $provider->getTransportConfig($connection);

    expect($config['transport'])->toBe('sendgrid');
    expect($config['api_key'])->toBe('SG.test-key');
    expect($config['endpoint'])->toContain('eu.sendgrid.com');
});
```

---

## Best Practices

1. **Always encrypt credentials**: Use `'is_credential' => true` for API keys, passwords, and secrets.

2. **Provide helpful descriptions**: Users should understand each field without external documentation.

3. **Use sensible defaults**: Pre-fill common values (ports, endpoints, timeouts).

4. **Handle errors gracefully**: Return meaningful error messages in `testConnection()`.

5. **Support internationalization**: Wrap all user-facing strings in `__()`.

6. **Use appropriate icons**: Browse [Iconify](https://icon-sets.iconify.design/) for provider logos.

7. **Validate thoroughly**: Add validation for all fields to prevent misconfigurations.

---

## Available Built-in Providers

| Provider | Key | Description |
|----------|-----|-------------|
| PHP Mail | `php_mail` | Uses PHP's native mail() function via sendmail |
| SMTP | `smtp` | Connect to any SMTP server |

---

## Database Schema

The `email_connections` table stores:

```sql
- uuid                  -- Unique identifier
- name                  -- Connection name
- from_email            -- Default sender email
- from_name             -- Default sender name
- force_from_email      -- Override sender email
- force_from_name       -- Override sender name
- provider_type         -- Provider key (e.g., 'smtp', 'sendgrid')
- settings              -- JSON: Non-sensitive configuration
- credentials           -- JSON (encrypted): Sensitive data
- is_active             -- Whether connection is active
- is_default            -- Whether this is the default connection
- priority              -- For failover ordering
- last_tested_at        -- Last test timestamp
- last_test_status      -- 'success' or 'failed'
- last_test_message     -- Last test result message
```
