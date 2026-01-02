<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\Modules\ModuleComposerInterface;
use Illuminate\Console\Command;

/**
 * Run a composer command in a module's directory.
 *
 * This enables modules to maintain their own independent dependencies
 * without polluting the core composer.lock file.
 */
class ModuleComposerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:composer
                            {module : The module name (e.g., LaraDashboard, laradashboard)}
                            {composer_command : The composer command to run (e.g., install, update, require)}
                            {args?* : Additional arguments to pass to composer}
                            {--timeout=300 : Timeout in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a composer command in a module\'s directory';

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
        $command = $this->argument('composer_command');
        $args = $this->argument('args') ?? [];

        if (! $this->composerService->hasComposerFile($moduleName)) {
            $this->error("Module '{$moduleName}' not found or has no composer.json file.");
            $this->showAvailableModules();

            return self::FAILURE;
        }

        $modulePath = $this->composerService->getModulePath($moduleName);
        $this->info("Running 'composer {$command}' in {$modulePath}");
        $this->line('');

        $result = $this->composerService->run($moduleName, $command, $args);

        $this->line($result['output']);

        if ($result['success']) {
            $this->newLine();
            $this->info("Composer command completed successfully.");

            return self::SUCCESS;
        }

        $this->newLine();
        $this->error("Composer command failed with exit code: {$result['exit_code']}");

        return self::FAILURE;
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
