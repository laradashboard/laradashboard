<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Modules\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class UpgradeSnapshotService
{
    public function __construct(
        protected CoreUpgradeService $coreUpgradeService,
        protected ModuleService $moduleService,
    ) {
    }

    /**
     * Capture the current installation state for upgrade verification.
     *
     * @return array<string, mixed>
     */
    public function capture(): array
    {
        return [
            'captured_at' => now()->toIso8601String(),
            'core_version' => $this->coreUpgradeService->getCurrentVersion()['version'] ?? '0.0.0',
            'modules' => $this->captureModuleState(),
            'migration_count' => $this->getMigrationCount(),
            'demo_mode' => (bool) config('app.demo_mode', false),
        ];
    }

    /**
     * @return array<string, array{version: string, enabled: bool, folder: string}>
     */
    public function captureModuleState(): array
    {
        $modulesPath = config('modules.paths.modules', base_path('modules'));
        $statuses = $this->moduleService->getModuleStatuses();
        $modules = [];

        if (! File::isDirectory($modulesPath)) {
            return $modules;
        }

        foreach (File::directories($modulesPath) as $directory) {
            $folder = basename($directory);
            $moduleJsonPath = $directory.'/module.json';

            if (! File::exists($moduleJsonPath)) {
                continue;
            }

            $moduleData = json_decode(File::get($moduleJsonPath), true) ?? [];
            $name = $moduleData['name'] ?? $folder;
            $normalizedName = $this->moduleService->normalizeModuleName($name);

            $modules[$normalizedName] = [
                'name' => $name,
                'folder' => $folder,
                'version' => (string) ($moduleData['version'] ?? '0.0.0'),
                'enabled' => (bool) ($statuses[$normalizedName] ?? false),
            ];
        }

        ksort($modules);

        return $modules;
    }

    public function getMigrationCount(): int
    {
        if (! Schema::hasTable('migrations')) {
            return 0;
        }

        return (int) DB::table('migrations')->count();
    }

    /**
     * @param  array<string, mixed>|null  $snapshot
     */
    public function save(string $path, ?array $snapshot = null): string
    {
        $snapshot ??= $this->capture();
        $directory = dirname($path);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($path, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $path;
    }

    /**
     * @return array<string, mixed>
     */
    public function load(string $path): array
    {
        if (! File::exists($path)) {
            throw new \InvalidArgumentException("Snapshot file not found: {$path}");
        }

        $decoded = json_decode(File::get($path), true);

        if (! is_array($decoded)) {
            throw new \InvalidArgumentException("Invalid snapshot file: {$path}");
        }

        return $decoded;
    }

    /**
     * Run verification checks against the current installation.
     *
     * @return array{passed: bool, checks: array<int, array<string, mixed>>}
     */
    public function verify(?array $beforeSnapshot = null, ?string $expectedVersion = null): array
    {
        $checks = [];
        $afterSnapshot = $this->capture();

        $checks[] = $this->check('application_boots', function () {
            return app()->isBooted();
        });

        if ($expectedVersion !== null) {
            $checks[] = $this->check('core_version', function () use ($afterSnapshot, $expectedVersion) {
                return $afterSnapshot['core_version'] === $expectedVersion;
            }, [
                'expected' => $expectedVersion,
                'actual' => $afterSnapshot['core_version'],
            ]);
        }

        if ($beforeSnapshot !== null) {
            $checks[] = $this->check('modules_preserved', function () use ($beforeSnapshot, $afterSnapshot) {
                return ($beforeSnapshot['modules'] ?? []) === ($afterSnapshot['modules'] ?? []);
            }, [
                'before_count' => count($beforeSnapshot['modules'] ?? []),
                'after_count' => count($afterSnapshot['modules'] ?? []),
            ]);

            $checks[] = $this->check('migrations_not_regressed', function () use ($beforeSnapshot, $afterSnapshot) {
                return ($afterSnapshot['migration_count'] ?? 0) >= ($beforeSnapshot['migration_count'] ?? 0);
            }, [
                'before' => $beforeSnapshot['migration_count'] ?? 0,
                'after' => $afterSnapshot['migration_count'] ?? 0,
            ]);
        }

        foreach (config('release.verify.http_routes', []) as $route) {
            $checks[] = $this->check('http'.str_replace('/', '_', $route), function () use ($route) {
                return $this->verifyHttpRoute($route);
            }, ['route' => $route]);
        }

        $passed = collect($checks)->every(fn (array $check) => $check['passed'] === true);

        return [
            'passed' => $passed,
            'checks' => $checks,
            'snapshot' => $afterSnapshot,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function check(string $name, callable $callback, array $context = []): array
    {
        try {
            $passed = (bool) $callback();
        } catch (\Throwable $exception) {
            return array_merge([
                'name' => $name,
                'passed' => false,
                'message' => $exception->getMessage(),
            ], $context);
        }

        return array_merge([
            'name' => $name,
            'passed' => $passed,
            'message' => $passed ? 'OK' : 'Failed',
        ], $context);
    }

    protected function verifyHttpRoute(string $route): bool
    {
        if (app()->runningInConsole() || app()->environment('testing', 'local')) {
            try {
                $request = Request::create($route, 'GET');
                $response = app()->handle($request);
                $status = $response->getStatusCode();

                return $status >= 200 && $status < 400;
            } catch (\Throwable) {
                return false;
            }
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        $timeout = (int) config('release.verify.http_timeout', 15);

        try {
            $response = Http::timeout($timeout)->get($baseUrl.$route);

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }
}
