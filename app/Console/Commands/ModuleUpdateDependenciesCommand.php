<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\Modules\ModuleComposerInterface;
use Illuminate\Console\Command;

/**
 * Update composer dependencies for module(s).
 *
 * This command enables modules to maintain their own independent dependencies
 * by running composer update within each module's directory.
 */
class ModuleUpdateDependenciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:update-deps
                            {module? : The module name (optional, updates all if omitted)}
                            {--timeout=300 : Timeout in seconds per module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update composer dependencies for module(s)';

    public function __construct(
        protected ModuleComposerInterface $composerService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moduleName = $this->argument('module');

        if ($moduleName !== null) {
            return $this->updateForModule($moduleName);
        }

        return $this->updateForAllModules();
    }

    /**
     * Update dependencies for a specific module.
     */
    private function updateForModule(string $moduleName): int
    {
        if (! $this->composerService->hasComposerFile($moduleName)) {
            $this->error("Module '{$moduleName}' not found or has no composer.json file.");
            $this->showAvailableModules();

            return self::FAILURE;
        }

        $modulePath = $this->composerService->getModulePath($moduleName);
        $this->info("Updating dependencies for module: {$moduleName}");
        $this->line("Path: {$modulePath}");
        $this->line('');

        $result = $this->composerService->update($moduleName);

        $this->line($result['output']);

        if ($result['success']) {
            $this->newLine();
            $this->info("Dependencies updated successfully for {$moduleName}.");

            return self::SUCCESS;
        }

        $this->newLine();
        $this->error("Failed to update dependencies for {$moduleName}.");

        return self::FAILURE;
    }

    /**
     * Update dependencies for all modules.
     */
    private function updateForAllModules(): int
    {
        $modules = $this->composerService->getModulesWithComposer();

        if (empty($modules)) {
            $this->warn('No modules with composer.json found.');

            return self::SUCCESS;
        }

        $this->info('Updating dependencies for all modules...');
        $this->line('');

        $hasFailures = false;
        $results = [];

        foreach ($modules as $module) {
            $this->comment("Processing: {$module}");

            $result = $this->composerService->update($module);
            $results[$module] = $result;

            if ($result['success']) {
                $this->info("  [OK] {$module}");
            } else {
                $this->error("  [FAILED] {$module}");
                $hasFailures = true;
            }
        }

        $this->newLine();
        $this->displaySummary($results);

        return $hasFailures ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Display a summary of the update results.
     *
     * @param  array<string, array{success: bool, output: string, exit_code: int}>  $results
     */
    private function displaySummary(array $results): void
    {
        $successful = array_filter($results, fn ($r) => $r['success']);
        $failed = array_filter($results, fn ($r) => ! $r['success']);

        $this->info('Summary:');
        $this->line("  Successful: " . count($successful));
        $this->line("  Failed: " . count($failed));

        if (! empty($failed)) {
            $this->newLine();
            $this->warn('Failed modules:');
            foreach ($failed as $module => $result) {
                $this->line("  - {$module} (exit code: {$result['exit_code']})");
            }
        }
    }

    /**
     * Show available modules with composer.json.
     */
    private function showAvailableModules(): void
    {
        $modules = $this->composerService->getModulesWithComposer();

        if (empty($modules)) {
            $this->line('');
            $this->warn('No modules with composer.json found.');

            return;
        }

        $this->line('');
        $this->line('Available modules with composer.json:');
        foreach ($modules as $module) {
            $this->line("  - {$module}");
        }
    }
}
