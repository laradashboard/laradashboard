<?php

declare(strict_types=1);

namespace App\Services\Builder;

use Illuminate\Support\Facades\Log;

/**
 * Block Migrator Service
 *
 * Handles version migrations for LaraBuilder blocks.
 * When block prop structures change, migration files can be created to
 * transform old props to new formats without re-saving all content.
 *
 * Migration files live in: resources/js/lara-builder/blocks/{type}/migrations/
 * File naming: v{from}_to_v{to}.php (e.g., v1_0_0_to_v1_1_0.php)
 *
 * Each migration file returns a closure that transforms props:
 * return function(array $props): array { ... };
 *
 * @example
 * $migrator = app(BlockMigrator::class);
 *
 * // Migrate a single block
 * $migratedBlock = $migrator->migrateBlock($block);
 *
 * // Migrate all blocks in content
 * $migratedBlocks = $migrator->migrateBlocks($blocks);
 */
class BlockMigrator
{
    /**
     * Current block versions (loaded from block.json files)
     */
    protected array $currentVersions = [];

    /**
     * Cached migration paths
     */
    protected array $migrationCache = [];

    /**
     * Get the current version for a block type
     */
    public function getCurrentVersion(string $blockType): string
    {
        if (isset($this->currentVersions[$blockType])) {
            return $this->currentVersions[$blockType];
        }

        $blockJsonPath = resource_path("js/lara-builder/blocks/{$blockType}/block.json");

        if (file_exists($blockJsonPath)) {
            $blockConfig = json_decode(file_get_contents($blockJsonPath), true);
            $this->currentVersions[$blockType] = $blockConfig['version'] ?? '1.0.0';
        } else {
            $this->currentVersions[$blockType] = '1.0.0';
        }

        return $this->currentVersions[$blockType];
    }

    /**
     * Check if a block needs migration
     */
    public function needsMigration(array $block): bool
    {
        $blockType = $block['type'] ?? null;
        if (! $blockType) {
            return false;
        }

        $storedVersion = $block['version'] ?? '1.0.0';
        $currentVersion = $this->getCurrentVersion($blockType);

        return version_compare($storedVersion, $currentVersion, '<');
    }

    /**
     * Migrate a single block to the current version
     */
    public function migrateBlock(array $block): array
    {
        $blockType = $block['type'] ?? null;
        if (! $blockType) {
            return $block;
        }

        $storedVersion = $block['version'] ?? '1.0.0';
        $currentVersion = $this->getCurrentVersion($blockType);

        // No migration needed
        if (version_compare($storedVersion, $currentVersion, '>=')) {
            return $block;
        }

        // Get migration path
        $migrations = $this->getMigrationPath($blockType, $storedVersion, $currentVersion);

        // Apply migrations in order
        $props = $block['props'] ?? [];

        foreach ($migrations as $migration) {
            try {
                $props = $migration($props);
            } catch (\Throwable $e) {
                Log::warning('Block migration failed', [
                    'block_type' => $blockType,
                    'error' => $e->getMessage(),
                ]);
                // Continue with current props if migration fails
            }
        }

        return [
            ...$block,
            'version' => $currentVersion,
            'props' => $props,
        ];
    }

    /**
     * Migrate all blocks in an array (recursively handles nested blocks)
     */
    public function migrateBlocks(array $blocks): array
    {
        return array_map(function (array $block) {
            // Migrate the block itself
            $block = $this->migrateBlock($block);

            // Handle nested blocks (e.g., in columns)
            if (isset($block['props']['children']) && is_array($block['props']['children'])) {
                $block['props']['children'] = array_map(
                    fn ($column) => is_array($column) ? $this->migrateBlocks($column) : $column,
                    $block['props']['children']
                );
            }

            return $block;
        }, $blocks);
    }

    /**
     * Get the migration path from one version to another
     *
     * @return array<callable> Array of migration functions to apply in order
     */
    protected function getMigrationPath(string $blockType, string $fromVersion, string $toVersion): array
    {
        $cacheKey = "{$blockType}:{$fromVersion}:{$toVersion}";

        if (isset($this->migrationCache[$cacheKey])) {
            return $this->migrationCache[$cacheKey];
        }

        $migrationsDir = resource_path("js/lara-builder/blocks/{$blockType}/migrations");
        $migrations = [];

        if (! is_dir($migrationsDir)) {
            $this->migrationCache[$cacheKey] = [];

            return [];
        }

        // Get all migration files
        $files = glob($migrationsDir . '/v*.php');
        $availableMigrations = [];

        foreach ($files as $file) {
            // Parse filename: v1_0_0_to_v1_1_0.php
            $filename = basename($file, '.php');
            if (preg_match('/^v(\d+_\d+_\d+)_to_v(\d+_\d+_\d+)$/', $filename, $matches)) {
                $from = str_replace('_', '.', $matches[1]);
                $to = str_replace('_', '.', $matches[2]);
                $availableMigrations[] = [
                    'from' => $from,
                    'to' => $to,
                    'file' => $file,
                ];
            }
        }

        // Sort migrations by 'from' version
        usort($availableMigrations, fn ($a, $b) => version_compare($a['from'], $b['from']));

        // Find migration path using graph traversal
        $currentFrom = $fromVersion;
        $visited = [];

        while (version_compare($currentFrom, $toVersion, '<')) {
            $found = false;

            foreach ($availableMigrations as $migration) {
                if (version_compare($migration['from'], $currentFrom, '==') &&
                    ! in_array($migration['file'], $visited, true)) {

                    $callback = require $migration['file'];
                    if (is_callable($callback)) {
                        $migrations[] = $callback;
                        $visited[] = $migration['file'];
                        $currentFrom = $migration['to'];
                        $found = true;
                        break;
                    }
                }
            }

            if (! $found) {
                // No migration path found, break to avoid infinite loop
                break;
            }
        }

        $this->migrationCache[$cacheKey] = $migrations;

        return $migrations;
    }

    /**
     * Create a migration file template
     */
    public function createMigrationTemplate(string $blockType, string $fromVersion, string $toVersion): string
    {
        $migrationsDir = resource_path("js/lara-builder/blocks/{$blockType}/migrations");

        if (! is_dir($migrationsDir)) {
            mkdir($migrationsDir, 0755, true);
        }

        $fromVersionFile = str_replace('.', '_', $fromVersion);
        $toVersionFile = str_replace('.', '_', $toVersion);
        $filename = "v{$fromVersionFile}_to_v{$toVersionFile}.php";
        $filepath = "{$migrationsDir}/{$filename}";

        $template = <<<PHP
<?php

/**
 * Block Migration: {$blockType} v{$fromVersion} to v{$toVersion}
 *
 * This migration transforms block props from v{$fromVersion} to v{$toVersion}.
 *
 * Changes in this version:
 * - TODO: Document changes here
 */

return function (array \$props): array {
    // Transform props here
    // Example: Rename a property
    // if (isset(\$props['oldName'])) {
    //     \$props['newName'] = \$props['oldName'];
    //     unset(\$props['oldName']);
    // }

    // Example: Add new property with default
    // \$props['newProperty'] ??= 'default value';

    return \$props;
};

PHP;

        file_put_contents($filepath, $template);

        return $filepath;
    }

    /**
     * Get all blocks that need migration from stored content
     */
    public function getBlocksNeedingMigration(array $blocks): array
    {
        $needsMigration = [];

        foreach ($blocks as $block) {
            if ($this->needsMigration($block)) {
                $needsMigration[] = [
                    'type' => $block['type'],
                    'id' => $block['id'] ?? null,
                    'stored_version' => $block['version'] ?? '1.0.0',
                    'current_version' => $this->getCurrentVersion($block['type']),
                ];
            }

            // Check nested blocks
            if (isset($block['props']['children']) && is_array($block['props']['children'])) {
                foreach ($block['props']['children'] as $column) {
                    if (is_array($column)) {
                        $nestedNeedsMigration = $this->getBlocksNeedingMigration($column);
                        $needsMigration = array_merge($needsMigration, $nestedNeedsMigration);
                    }
                }
            }
        }

        return $needsMigration;
    }
}
