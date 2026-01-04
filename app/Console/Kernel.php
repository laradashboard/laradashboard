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

        // Check for module updates twice daily (with silent output).
        // Uses caching to avoid hitting the API unnecessarily.
        $schedule->command('modules:check-updates --silent')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();
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
