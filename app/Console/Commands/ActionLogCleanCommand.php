<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ActionLog;
use Illuminate\Console\Command;

class ActionLogCleanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'actionlog:clean
                            {--force : Skip confirmation prompt}
                            {--days= : Only delete logs older than specified days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean all action logs from the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days');
        $force = $this->option('force');

        $query = ActionLog::query();

        if ($days) {
            $query->where('created_at', '<', now()->subDays((int) $days));
            $count = $query->count();
            $message = "This will delete {$count} action log(s) older than {$days} days.";
        } else {
            $count = $query->count();
            $message = "This will delete ALL {$count} action log(s).";
        }

        if ($count === 0) {
            $this->info('No action logs to delete.');

            return self::SUCCESS;
        }

        $this->warn($message);

        if (! $force && ! $this->confirm('Are you sure you want to proceed?')) {
            $this->info('Operation cancelled.');

            return self::SUCCESS;
        }

        $deleted = $query->delete();

        $this->info("Successfully deleted {$deleted} action log(s).");

        return self::SUCCESS;
    }
}
