<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class CoreRollbackCommand extends Command
{
    protected $signature = 'core:rollback
                            {backup? : Backup filename in storage/app/core-backups}
                            {--latest : Restore the most recent backup}
                            {--force : Skip confirmation prompts}
                            {--json : Output result as JSON}';

    protected $description = 'Restore the application from a core upgrade backup';

    public function __construct(protected BackupService $backupService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $backupFile = $this->argument('backup');

        if ($this->option('latest') || $backupFile === null) {
            $backups = $this->backupService->getBackups();

            if ($backups === []) {
                $this->error('No backups found.');

                return Command::FAILURE;
            }

            usort($backups, fn (array $a, array $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
            $backupFile = $backups[0]['name'] ?? null;
        }

        if (! is_string($backupFile) || $backupFile === '') {
            $this->error('No backup file specified.');

            return Command::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Restore backup {$backupFile}?", false)) {
            $this->warn('Rollback cancelled.');

            return Command::FAILURE;
        }

        $restored = $this->backupService->restoreFromBackup($backupFile);

        $result = [
            'success' => $restored,
            'backup_file' => $backupFile,
            'message' => $restored
                ? "Successfully restored from backup {$backupFile}"
                : "Failed to restore from backup {$backupFile}",
        ];

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } elseif ($restored) {
            $this->info($result['message']);
        } else {
            $this->error($result['message']);
        }

        return $restored ? Command::SUCCESS : Command::FAILURE;
    }
}
