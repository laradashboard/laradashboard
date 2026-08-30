<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BackupService;
use App\Services\CoreUpgradeService;
use Illuminate\Console\Command;

class CoreUpgradeCommand extends Command
{
    protected $signature = 'core:upgrade
                            {version : Target core version to install}
                            {--no-backup : Skip creating a backup before upgrading}
                            {--force : Skip confirmation prompts}
                            {--json : Output result as JSON}';

    protected $description = 'Upgrade the LaraDashboard core to a specific version from the marketplace';

    public function __construct(
        protected CoreUpgradeService $upgradeService,
        protected BackupService $backupService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $version = (string) $this->argument('version');
        $isDemoMode = (bool) config('app.demo_mode', false);
        $createBackup = ! $this->option('no-backup') && ! $isDemoMode;

        if (! $this->option('force') && ! $this->confirm("Upgrade core to version {$version}?", true)) {
            $this->warn('Upgrade cancelled.');

            return Command::FAILURE;
        }

        $backupFile = null;

        if ($createBackup) {
            $this->info('Creating backup before upgrade...');
            $backupFile = $this->backupService->createBackup();

            if ($backupFile === null) {
                $this->error('Failed to create backup. Upgrade aborted.');

                return Command::FAILURE;
            }

            $this->line("Backup created: {$backupFile}");
        } elseif ($isDemoMode) {
            $this->comment('Demo mode enabled — skipping backup creation.');
        }

        $this->info("Upgrading to version {$version}...");

        $result = $this->upgradeService->performUpgrade($version, $backupFile);

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } elseif ($result['success']) {
            $this->info($result['message']);
        } else {
            $this->error($result['message']);
        }

        return $result['success'] ? Command::SUCCESS : Command::FAILURE;
    }
}
