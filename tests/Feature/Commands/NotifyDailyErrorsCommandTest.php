<?php

declare(strict_types=1);

use App\Mail\DailyErrorDigest;
use App\Models\ErrorNotificationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();

    config(['settings.error_notifications_enabled' => '1']);
    config(['settings.error_notifications_email' => 'ops@example.test']);

    $this->logPath = storage_path('logs/test-errors-'.uniqid().'.log');
});

afterEach(function () {
    if (isset($this->logPath) && file_exists($this->logPath)) {
        unlink($this->logPath);
    }
});

function writeLog(string $path, string $contents): void
{
    file_put_contents($path, $contents);
}

it('sends a digest for new errors and dedupes by hash', function () {
    $now = now()->format('Y-m-d H:i:s');
    $log = <<<LOG
[$now] production.ERROR: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'name' at /app/Models/User.php:42
[$now] production.ERROR: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'name' at /app/Models/User.php:42
[$now] production.WARNING: This warning should be ignored
[$now] production.ERROR: Failed to authenticate on SMTP server at /vendor/symfony/mailer/Transport/Smtp/EsmtpTransport.php:269
LOG;
    writeLog($this->logPath, $log);

    $this->artisan('errors:notify-daily', ['--log' => $this->logPath])
        ->assertSuccessful();

    Mail::assertSent(
        DailyErrorDigest::class,
        fn ($mail) =>
        $mail->errors->count() === 2 &&
        $mail->hasTo('ops@example.test')
    );

    expect(ErrorNotificationLog::count())->toBe(2)
        ->and(ErrorNotificationLog::whereNotNull('notified_at')->count())->toBe(2);
});

it('does not re-notify a previously notified error', function () {
    $now = now()->format('Y-m-d H:i:s');
    $log = "[$now] production.ERROR: SQLSTATE[42S22]: Column not found at /app/Models/User.php:42\n";
    writeLog($this->logPath, $log);

    $this->artisan('errors:notify-daily', ['--log' => $this->logPath])->assertSuccessful();
    Mail::assertSentCount(1);

    $log .= "[$now] production.ERROR: SQLSTATE[42S22]: Column not found at /app/Models/User.php:42\n";
    writeLog($this->logPath, $log);

    $this->artisan('errors:notify-daily', ['--log' => $this->logPath])->assertSuccessful();
    Mail::assertSentCount(1);

    expect(ErrorNotificationLog::sole()->occurrences)->toBeGreaterThan(1);
});

it('skips sending when the setting is disabled', function () {
    config(['settings.error_notifications_enabled' => '0']);

    $now = now()->format('Y-m-d H:i:s');
    writeLog(
        $this->logPath,
        "[$now] production.ERROR: Boom at /app/X.php:1\n"
    );

    $this->artisan('errors:notify-daily', ['--log' => $this->logPath])->assertSuccessful();
    Mail::assertNothingSent();
});

it('ignores entries older than the 24-hour window', function () {
    $old = now()->subDays(3)->format('Y-m-d H:i:s');
    writeLog(
        $this->logPath,
        "[$old] production.ERROR: Ancient at /app/X.php:1\n"
    );

    $this->artisan('errors:notify-daily', ['--log' => $this->logPath])->assertSuccessful();
    Mail::assertNothingSent();
    expect(ErrorNotificationLog::count())->toBe(0);
});

it('falls back to contact_email when error_notifications_email is empty', function () {
    config(['settings.error_notifications_email' => '']);
    config(['settings.contact_email' => 'fallback@example.test']);

    $now = now()->format('Y-m-d H:i:s');
    writeLog(
        $this->logPath,
        "[$now] production.ERROR: Boom at /app/X.php:1\n"
    );

    $this->artisan('errors:notify-daily', ['--log' => $this->logPath])->assertSuccessful();
    Mail::assertSent(DailyErrorDigest::class, fn ($mail) => $mail->hasTo('fallback@example.test'));
});

it('dry-run is idempotent: a real run afterwards still sends the same errors', function () {
    $now = now()->format('Y-m-d H:i:s');
    writeLog(
        $this->logPath,
        "[$now] production.ERROR: Boom at /app/X.php:1\n"
    );

    $this->artisan('errors:notify-daily', ['--log' => $this->logPath, '--dry-run' => true])
        ->assertSuccessful();
    Mail::assertNothingSent();

    $this->artisan('errors:notify-daily', ['--log' => $this->logPath])
        ->assertSuccessful();
    Mail::assertSent(DailyErrorDigest::class, fn ($mail) => $mail->errors->count() === 1);
});

it('does not persist or send on dry-run', function () {
    $now = now()->format('Y-m-d H:i:s');
    writeLog(
        $this->logPath,
        "[$now] production.ERROR: Boom at /app/X.php:1\n"
    );

    $this->artisan('errors:notify-daily', ['--log' => $this->logPath, '--dry-run' => true])
        ->assertSuccessful();

    Mail::assertNothingSent();
    expect(ErrorNotificationLog::whereNotNull('notified_at')->count())->toBe(0);
});
