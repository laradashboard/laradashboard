<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SetupStorage::class,
        Commands\CreatePlaceholderImages::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Schedule the demo database refresh command every 15 minutes in demo mode.
        $schedule->command('demo:refresh-database')->everyFifteenMinutes();

        // Check for module updates hourly (with silent output).
        // Uses caching to avoid hitting the API unnecessarily.
        $schedule->command('modules:check-updates --silent')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();

        // Check for core updates twice daily (with silent output).
        $schedule->command('core:check-updates --silent')
            ->twiceDaily(6, 18)
            ->withoutOverlapping()
            ->runInBackground();

        // Process inbound emails every 5 minutes.
        // This checks all active IMAP connections that are due for polling.
        // Each connection has its own polling_interval setting.
        $schedule->command('email:process-inbound')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/inbound-email.log'));

        // Process queued jobs (workflow actions, emails, etc.).
        // Runs every minute, processes up to 50 jobs per batch, stops after 55 seconds
        // to avoid overlapping with the next scheduled run.
        $schedule->command('queue:work --stop-when-empty --max-jobs=50 --max-time=55')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/queue-worker.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
