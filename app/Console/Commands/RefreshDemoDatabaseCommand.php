<?php

namespace App\Console\Commands;

use App\Services\DemoAppService;
use Illuminate\Console\Command;

class RefreshDemoDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:refresh-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh database with fresh migrations and seeds if demo mode is enabled';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private readonly DemoAppService $demoAppService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->demoAppService->isDemoAppEnabled()) {
            $this->info('Demo mode is not enabled. Skipping database refresh.');

            return 0;
        }

        $this->info('Demo mode is enabled. Refreshing database...');

        // Run migrations with --force to suppress confirmation prompts.
        $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);

        // Run module:seed with --all to seed all modules and --force to suppress confirmation prompts.
        $this->call('module:seed', [
            '--all' => true,
            '--force' => true,
        ]);

        $this->info('Database refreshed successfully!');

        return 0;
    }
}
