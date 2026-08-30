<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\UpgradeSnapshotService;
use Illuminate\Console\Command;

class CoreVerifyCommand extends Command
{
    protected $signature = 'core:verify
                            {--expected-version= : Expected core version after upgrade}
                            {--compare-with= : Path to a pre-upgrade snapshot JSON file}
                            {--json : Output verification report as JSON}';

    protected $description = 'Verify a core upgrade completed successfully and modules were preserved';

    public function __construct(protected UpgradeSnapshotService $snapshotService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $beforeSnapshot = null;
        $compareWith = $this->option('compare-with');

        if ($compareWith) {
            try {
                $beforeSnapshot = $this->snapshotService->load((string) $compareWith);
            } catch (\InvalidArgumentException $exception) {
                $this->error($exception->getMessage());

                return Command::FAILURE;
            }
        }

        $report = $this->snapshotService->verify(
            $beforeSnapshot,
            $this->option('expected-version') ? (string) $this->option('expected-version') : null,
        );

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            foreach ($report['checks'] as $check) {
                $label = $check['passed'] ? '<info>PASS</info>' : '<error>FAIL</error>';
                $this->line("{$label} {$check['name']}: {$check['message']}");
            }

            $this->newLine();
            $this->line('Core version: '.$report['snapshot']['core_version']);
            $this->line('Modules: '.count($report['snapshot']['modules']));
        }

        if ($report['passed']) {
            if (! $this->option('json')) {
                $this->info('Verification passed.');
            }

            return Command::SUCCESS;
        }

        if (! $this->option('json')) {
            $this->error('Verification failed.');
        }

        return Command::FAILURE;
    }
}
