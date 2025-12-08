<?php

declare(strict_types=1);

namespace App\Console\Commands\Builder;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\info;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

/**
 * Check LaraBuilder block versions and render.php status
 *
 * This command helps developers understand which blocks:
 * - Have version tracking enabled
 * - Have server-side rendering (render.php)
 * - May need migrations
 */
class CheckBlockVersions extends Command
{
    protected $signature = 'builder:check-versions
                            {--show-paths : Show file paths}';

    protected $description = 'Check LaraBuilder block versions and server-side rendering status';

    public function handle(): int
    {
        $blocksPath = resource_path('js/lara-builder/blocks');

        if (! File::isDirectory($blocksPath)) {
            warning('Blocks directory not found: ' . $blocksPath);

            return self::FAILURE;
        }

        $blocks = [];
        $directories = File::directories($blocksPath);

        foreach ($directories as $dir) {
            $blockType = basename($dir);
            $blockJsonPath = $dir . '/block.json';
            $renderPhpPath = $dir . '/render.php';
            $saveJsPath = $dir . '/save.js';
            $migrationsDir = $dir . '/migrations';

            $blockData = [
                'type' => $blockType,
                'version' => '-',
                'render.php' => '✗',
                'save.js' => '✗',
                'migrations' => '0',
            ];

            // Check block.json
            if (File::exists($blockJsonPath)) {
                $config = json_decode(File::get($blockJsonPath), true);
                $blockData['version'] = $config['version'] ?? '(none)';
            }

            // Check render.php
            if (File::exists($renderPhpPath)) {
                $blockData['render.php'] = '✓';
            }

            // Check save.js
            if (File::exists($saveJsPath)) {
                $blockData['save.js'] = '✓';

                // Check if save.js uses placeholder pattern
                $saveContent = File::get($saveJsPath);
                if (str_contains($saveContent, 'data-lara-block=')) {
                    $blockData['save.js'] = '→PHP';
                }
            }

            // Check migrations
            if (File::isDirectory($migrationsDir)) {
                $migrationFiles = File::files($migrationsDir);
                $blockData['migrations'] = (string) count($migrationFiles);
            }

            $blocks[] = $blockData;
        }

        // Sort by type
        usort($blocks, fn ($a, $b) => strcmp($a['type'], $b['type']));

        info('LaraBuilder Block Status');
        $this->newLine();

        // Summary
        $totalBlocks = count($blocks);
        $withVersion = count(array_filter($blocks, fn ($b) => $b['version'] !== '-' && $b['version'] !== '(none)'));
        $withRenderPhp = count(array_filter($blocks, fn ($b) => $b['render.php'] === '✓'));
        $withPlaceholder = count(array_filter($blocks, fn ($b) => $b['save.js'] === '→PHP'));

        info(sprintf(
            'Found %d blocks: %d versioned, %d with render.php, %d using placeholder pattern',
            $totalBlocks,
            $withVersion,
            $withRenderPhp,
            $withPlaceholder
        ));
        $this->newLine();

        // Table
        table(
            ['Block Type', 'Version', 'render.php', 'save.js', 'Migrations'],
            array_map(fn ($b) => array_values($b), $blocks)
        );

        $this->newLine();

        // Legend
        $this->line('<comment>Legend:</comment>');
        $this->line('  ✓     = File exists');
        $this->line('  ✗     = File missing');
        $this->line('  →PHP  = save.js outputs placeholder for server rendering');
        $this->line('  (none) = block.json exists but no version field');

        // Recommendations
        $this->newLine();
        $blocksWithoutVersion = array_filter($blocks, fn ($b) => $b['version'] === '(none)' || $b['version'] === '-');
        if (! empty($blocksWithoutVersion)) {
            warning('Blocks without version field:');
            foreach ($blocksWithoutVersion as $block) {
                $this->line("  - {$block['type']}");
            }
        }

        $blocksWithRenderButNoPlaceholder = array_filter(
            $blocks,
            fn ($b) => $b['render.php'] === '✓' && $b['save.js'] !== '→PHP'
        );
        if (! empty($blocksWithRenderButNoPlaceholder)) {
            warning('Blocks with render.php but save.js not using placeholder pattern:');
            foreach ($blocksWithRenderButNoPlaceholder as $block) {
                $this->line("  - {$block['type']} (consider updating save.js to use placeholder)");
            }
        }

        return self::SUCCESS;
    }
}
