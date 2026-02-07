# Inbound Email Implementation

## Overview
IMAP-based inbound email processing for LaraDashboard core, with CRM module integration for ticket replies.

## Status: COMPLETE

## Architecture

### Core Components
- [x] `InboundEmailConnection` model - IMAP connection settings
- [x] `InboundEmail` model - Stored incoming emails
- [x] `InboundEmailHook` enum - Hook constants for extensibility
- [x] `InboundEmailHandlerInterface` - Contract for modules
- [x] `InboundEmailHandlerResult` - Result object for handlers
- [x] `ImapService` - Fetch emails via IMAP
- [x] `EmailParserService` - Parse reply content, strip quotes/signatures
- [x] `InboundEmailProcessor` - Process emails with handlers and hooks
- [x] `ProcessInboundEmailsCommand` - Artisan command (`email:process-inbound`)

### CRM Module
- [x] `TicketEmailHandler` - Handle ticket replies via email
- [x] Handler registered in `CrmServiceProvider`

### Database Tables
- [x] `inbound_email_connections` - IMAP connection configurations
- [x] `inbound_emails` - Received email records

## Usage

### 1. Configure IMAP Connection
Create an inbound email connection via the admin UI or database:

```php
use App\Models\InboundEmailConnection;

$connection = InboundEmailConnection::create([
    'name' => 'Support Inbox',
    'imap_host' => 'imap.example.com',
    'imap_port' => 993,
    'imap_encryption' => 'ssl',
    'imap_username' => 'support@example.com',
    'imap_password' => 'your-password',
    'imap_folder' => 'INBOX',
    'is_active' => true,
    'mark_as_read' => true,
    'delete_after_processing' => false,
    'fetch_limit' => 50,
    'polling_interval' => 5, // minutes
    'created_by' => auth()->id(),
]);
```

### 2. Process Inbound Emails

#### Via Artisan Command
```bash
# Process connections due for polling
php artisan email:process-inbound

# Process all active connections
php artisan email:process-inbound --all

# Process a specific connection
php artisan email:process-inbound --connection=1

# Test connection(s) without processing
php artisan email:process-inbound --test
```

#### Via Scheduler (Recommended)
Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('email:process-inbound')
        ->everyMinute()
        ->withoutOverlapping();
}
```

### 3. How Ticket Matching Works

The `TicketEmailHandler` matches incoming emails to tickets by:

1. **In-Reply-To header**: Matches against `email_message_id` in ticket replies
2. **References header**: Checks all referenced message IDs
3. **Subject line**: Extracts ticket numbers from patterns like:
   - `[Ticket #12345]`
   - `Ticket: 12345`
   - `Re: [#12345]`

When matched, a new `TicketReply` is created with the email content.

## Creating Custom Handlers

Modules can register custom handlers to process specific types of emails:

```php
namespace Modules\MyModule\Services;

use App\Contracts\InboundEmailHandlerInterface;
use App\Contracts\InboundEmailHandlerResult;
use App\Models\InboundEmail;

class MyEmailHandler implements InboundEmailHandlerInterface
{
    public function getHandlerType(): string
    {
        return 'mymodule.handler';
    }

    public function getName(): string
    {
        return 'My Custom Handler';
    }

    public function getPriority(): int
    {
        return 20; // Lower = higher priority
    }

    public function canHandle(InboundEmail $email): bool
    {
        // Return true if this handler can process the email
        return str_contains($email->subject ?? '', '[MyModule]');
    }

    public function handle(InboundEmail $email): InboundEmailHandlerResult
    {
        // Process the email
        // ...

        return InboundEmailHandlerResult::success(
            message: 'Email processed successfully',
            modelType: MyModel::class,
            modelId: $createdModel->id,
        );
    }
}
```

Register in your service provider:

```php
use App\Services\InboundEmailProcessor;

$this->app->booted(function () {
    if ($this->app->bound(InboundEmailProcessor::class)) {
        $processor = $this->app->make(InboundEmailProcessor::class);
        $processor->registerHandler($this->app->make(MyEmailHandler::class));
    }
});
```

## Hooks

Use hooks to extend the inbound email processing:

```php
use App\Enums\InboundEmailHook;
use App\Support\Facades\Hook;

// Before processing starts
Hook::addAction(InboundEmailHook::BEFORE_PROCESS, function ($email) {
    // Log or modify email before processing
});

// After successful processing
Hook::addAction(InboundEmailHook::AFTER_PROCESS, function ($email, $handler, $result) {
    // Send notifications, update stats, etc.
});

// When processing fails
Hook::addAction(InboundEmailHook::PROCESS_FAILED, function ($email, $errorMessage) {
    // Handle failures, send alerts
});

// When no handler matches the email
Hook::addAction(InboundEmailHook::UNMATCHED, function ($email) {
    // Create new ticket, forward to admin, etc.
});
```

## Files Created

### Core
- `app/Models/InboundEmailConnection.php`
- `app/Models/InboundEmail.php`
- `app/Enums/InboundEmailHook.php`
- `app/Contracts/InboundEmailHandlerInterface.php`
- `app/Contracts/InboundEmailHandlerResult.php`
- `app/Services/ImapService.php`
- `app/Services/EmailParserService.php`
- `app/Services/InboundEmailProcessor.php`
- `app/Console/Commands/ProcessInboundEmailsCommand.php`
- `database/migrations/2026_01_18_092821_create_inbound_email_connections_table.php`
- `database/migrations/2026_01_18_092822_create_inbound_emails_table.php`

### CRM Module
- `modules/Crm/app/Services/InboundEmail/TicketEmailHandler.php`

## Files Modified
- `app/Providers/AppServiceProvider.php` - Registered services as singletons
- `modules/Crm/app/Providers/CrmServiceProvider.php` - Registered TicketEmailHandler

## Requirements

- PHP IMAP extension must be installed and enabled
- Cron job for scheduler (recommended for automatic polling)

## Notes

- The `email_message_id` field in `TicketReply` is used for threading
- Quoted content and signatures are automatically stripped from replies
- Handlers are sorted by priority (lower number = higher priority)
- Failed emails can be reprocessed via `InboundEmailProcessor::reprocessEmail()`
