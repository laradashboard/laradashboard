<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nwidart\Modules\Commands\Make\ModuleMakeCommand as NwidartModuleMakeCommand;

/**
 * Extends nwidart's module:make to automatically generate a CLAUDE.md
 * in every new module — providing Claude Code with module-specific
 * architecture guidance, CRUD recipes, and coding conventions.
 */
class ModuleMakeCommand extends NwidartModuleMakeCommand
{
    protected string $stubPath = 'stubs/laradashboard/module.claude.md.stub';

    public function handle(): int
    {
        $names = $this->argument('name');

        $exitCode = parent::handle();

        // Generate CLAUDE.md and .gitignore for each successfully created module
        foreach ($names as $name) {
            $this->generateClaudeMd($name);
            $this->generateGitignore($name);
        }

        return $exitCode;
    }

    protected function generateClaudeMd(string $moduleName): void
    {
        $studlyName = Str::studly($moduleName);
        $lowerName = Str::lower($moduleName);

        $modulePath = $this->resolveModulePath($studlyName, $lowerName);

        if (! $modulePath) {
            $this->warn("  Could not locate module path for '{$studlyName}' — CLAUDE.md not generated.");

            return;
        }

        $claudePath = "{$modulePath}/CLAUDE.md";

        if (File::exists($claudePath) && ! $this->option('force')) {
            $this->line("  Skipped: CLAUDE.md already exists in {$studlyName}");

            return;
        }

        $stubFile = base_path($this->stubPath);

        if (! File::exists($stubFile)) {
            $this->warn("  CLAUDE.md stub not found at {$this->stubPath} — skipping.");

            return;
        }

        $content = File::get($stubFile);
        $content = str_replace(
            ['$STUDLY_NAME$', '$LOWER_NAME$'],
            [$studlyName, $lowerName],
            $content
        );

        File::put($claudePath, $content);

        $this->components->info("CLAUDE.md generated for module [{$studlyName}]");
    }

    protected function generateGitignore(string $moduleName): void
    {
        $studlyName = Str::studly($moduleName);
        $lowerName = Str::lower($moduleName);

        $modulePath = $this->resolveModulePath($studlyName, $lowerName);

        if (! $modulePath) {
            return;
        }

        $gitignorePath = "{$modulePath}/.gitignore";

        if (File::exists($gitignorePath)) {
            // Ensure /vendor is present even if .gitignore already exists
            $existing = File::get($gitignorePath);
            if (! str_contains($existing, '/vendor')) {
                File::append($gitignorePath, "\n/vendor\n");
                $this->components->info(".gitignore updated with /vendor for module [{$studlyName}]");
            }

            return;
        }

        File::put($gitignorePath, "/vendor\n");

        $this->components->info(".gitignore generated for module [{$studlyName}]");
    }

    protected function resolveModulePath(string $studlyName, string $lowerName): ?string
    {
        $candidates = [
            base_path("modules/{$studlyName}"),
            base_path('modules/'.Str::kebab($studlyName)),
            base_path("modules/{$lowerName}"),
        ];

        foreach ($candidates as $path) {
            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
    }
}
