<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\UpgradeSnapshotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CoreSnapshotCommand extends Command
{
    protected $signature = 'core:snapshot
                            {--output= : Output path for the snapshot JSON file}
                            {--json : Print snapshot to stdout as JSON}';

    protected $description = 'Capture installation state before or after a core upgrade';

    public function __construct(protected UpgradeSnapshotService $snapshotService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $snapshot = $this->snapshotService->capture();

        if ($this->option('json') && ! $this->option('output')) {
            $this->line(json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return Command::SUCCESS;
        }

        $output = $this->option('output') ?? $this->defaultOutputPath($snapshot['core_version']);

        $this->snapshotService->save($output, $snapshot);

        $this->info("Snapshot saved to {$output}");
        $this->line('Core version: '.$snapshot['core_version']);
        $this->line('Modules captured: '.count($snapshot['modules']));

        return Command::SUCCESS;
    }

    protected function defaultOutputPath(string $version): string
    {
        $directory = config('release.snapshot_path', storage_path('app/upgrade-snapshots'));

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_His');

        return $directory."/snapshot-{$version}-{$timestamp}.json";
    }
}
