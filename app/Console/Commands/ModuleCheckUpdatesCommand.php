<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Modules\ModuleUpdateService;
use Illuminate\Console\Command;

class ModuleCheckUpdatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:check-updates
                            {--force : Force a fresh check, bypassing cache}
                            {--silent : Suppress output (for scheduled runs)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for available module updates from the LaraDashboard marketplace';

    public function __construct(protected ModuleUpdateService $updateService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $silent = $this->option('silent');
        $force = $this->option('force');

        if (! config('laradashboard.updates.enabled', true)) {
            if (! $silent) {
                $this->warn('Module update checking is disabled.');
            }

            return Command::SUCCESS;
        }

        if (! $silent) {
            $this->info('Checking for module updates...');
        }

        $result = $this->updateService->checkForUpdates($force);

        if (! $result['success']) {
            if (! $silent) {
                $this->error('Failed to check for updates: ' . ($result['error'] ?? 'Unknown error'));
            }

            return Command::FAILURE;
        }

        $updates = $result['updates'] ?? [];

        if (empty($updates)) {
            if (! $silent) {
                $this->info('All modules are up to date.');
            }

            return Command::SUCCESS;
        }

        if (! $silent) {
            $this->info(count($updates) . ' update(s) available:');
            $this->newLine();

            $tableData = [];
            foreach ($updates as $module => $info) {
                $tableData[] = [
                    $module,
                    $info['current'] ?? 'N/A',
                    $info['latest'] ?? 'N/A',
                    $info['changelog'] ?? '-',
                ];
            }

            $this->table(
                ['Module', 'Current Version', 'Latest Version', 'Changelog'],
                $tableData
            );
        }

        return Command::SUCCESS;
    }
}
