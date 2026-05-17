<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\DailyErrorDigest;
use App\Models\Setting;
use App\Models\User;
use App\Services\ErrorLogNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotifyDailyErrorsCommand extends Command
{
    private const WINDOW_HOURS = 24;

    protected $signature = 'errors:notify-daily
        {--log= : Path to a specific log file (defaults to storage/logs/laravel.log)}
        {--dry-run : Parse and dedupe but do not send mail or persist notified_at}
        {--force : Run even when error_notifications_enabled is off}';

    protected $description = 'Send a daily digest of new errors from laravel.log, deduped so each unique error is reported once.';

    public function handle(ErrorLogNotificationService $service): int
    {
        if (! $this->option('force') && (string) config('settings.error_notifications_enabled') !== '1') {
            $this->info('Error notifications are disabled.');

            return self::SUCCESS;
        }

        $recipient = $this->resolveRecipient();
        if ($recipient === null) {
            $this->warn('No recipient email could be resolved. Set "error_notifications_email" or "contact_email" in Settings.');

            return self::SUCCESS;
        }

        $newErrors = $service->collectUnnotifiedErrors(
            $this->option('log') ?: null,
            self::WINDOW_HOURS,
        );

        if ($newErrors->isEmpty()) {
            $this->info('No new errors to report.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d new error(s).', $newErrors->count()));

        if ($this->option('dry-run')) {
            $this->line(' (dry-run) skipping send and notified_at update');
            $this->table(['Level', 'Message', 'Occurrences'], $newErrors->map(fn ($e) => [
                $e->level,
                \Illuminate\Support\Str::limit($e->message, 80),
                $e->occurrences,
            ])->all());

            return self::SUCCESS;
        }

        try {
            Mail::to($recipient)->send(new DailyErrorDigest($newErrors, self::WINDOW_HOURS));
        } catch (Throwable $e) {
            $this->error('Failed to send error digest: '.$e->getMessage());

            return self::FAILURE;
        }

        $service->markNotified($newErrors);

        $this->info(sprintf('Digest sent to %s.', $recipient));

        return self::SUCCESS;
    }

    private function resolveRecipient(): ?string
    {
        $configured = trim((string) config('settings.'.Setting::ERROR_NOTIFICATIONS_EMAIL));
        if ($configured !== '') {
            return $configured;
        }

        $contact = trim((string) config('settings.'.Setting::CONTACT_EMAIL));
        if ($contact !== '') {
            return $contact;
        }

        $admin = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Superadmin', 'Admin']))
            ->orderBy('id')
            ->first();

        return $admin?->email;
    }
}
