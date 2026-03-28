<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CoreUpgradeService;
use Illuminate\Console\Command;

class CoreCheckUpdatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'core:check-updates
                            {--silent : Suppress output (for scheduled runs)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for available core updates from the LaraDashboard marketplace';

    public function __construct(protected CoreUpgradeService $upgradeService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $silent = $this->option('silent');

        if (! config('laradashboard.updates.enabled', true)) {
            if (! $silent) {
                $this->warn('Update checking is disabled.');
            }

            return Command::SUCCESS;
        }

        if (! $silent) {
            $this->info('Checking for core updates...');
        }

        $result = $this->upgradeService->checkForUpdates();

        if ($result === null) {
            if (! $silent) {
                $this->error('Failed to check for core updates.');
            }

            return Command::FAILURE;
        }

        if (! ($result['has_update'] ?? false)) {
            if (! $silent) {
                $this->info('Core is up to date (v' . ($result['current_version'] ?? 'unknown') . ').');
            }

            return Command::SUCCESS;
        }

        if (! $silent) {
            $this->info('Core update available: v' . ($result['latest_version'] ?? 'unknown'));

            if (isset($result['latest_update']['is_critical']) && $result['latest_update']['is_critical']) {
                $this->warn('This is a CRITICAL update.');
            }
        }

        return Command::SUCCESS;
    }
}
