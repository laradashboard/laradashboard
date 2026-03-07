<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TranslationsSyncCommand extends Command
{
    protected $signature = 'translations:sync
        {--lang= : Sync a specific language (e.g. es, fr)}
        {--remove-stale : Remove keys not present in en.json}
        {--dry-run : Show changes without applying them}';

    protected $description = 'Sync all language JSON files against en.json as the source of truth';

    public function handle(): int
    {
        $langPath = resource_path('lang');
        $enFile = $langPath . '/en.json';

        if (! File::exists($enFile)) {
            $this->error('en.json not found at ' . $enFile);

            return self::FAILURE;
        }

        $enTranslations = json_decode(File::get($enFile), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Failed to parse en.json: ' . json_last_error_msg());

            return self::FAILURE;
        }

        ksort($enTranslations);

        $isDryRun = $this->option('dry-run');
        $removeStale = $this->option('remove-stale');
        $specificLang = $this->option('lang');

        // Sort en.json itself
        if (! $isDryRun) {
            File::put($enFile, json_encode($enTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
            $this->info('Sorted en.json (' . count($enTranslations) . ' keys)');
        }

        // Gather language files
        $langFiles = collect(File::glob($langPath . '/*.json'))
            ->filter(function ($file) use ($specificLang) {
                $code = pathinfo($file, PATHINFO_FILENAME);

                if ($code === 'en') {
                    return false;
                }

                if ($specificLang) {
                    return $code === $specificLang;
                }

                return true;
            })
            ->sort()
            ->values();

        if ($langFiles->isEmpty()) {
            $this->warn('No language files found to sync.');

            return self::SUCCESS;
        }

        $summaryRows = [];

        foreach ($langFiles as $file) {
            $code = pathinfo($file, PATHINFO_FILENAME);
            $translations = json_decode(File::get($file), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->warn("Skipping {$code}.json: " . json_last_error_msg());

                continue;
            }

            $existingKeys = array_keys($translations);
            $enKeys = array_keys($enTranslations);

            $missingKeys = array_diff($enKeys, $existingKeys);
            $staleKeys = array_diff($existingKeys, $enKeys);

            // Add missing keys with English value as placeholder
            foreach ($missingKeys as $key) {
                $translations[$key] = $enTranslations[$key];
            }

            // Remove stale keys if requested
            $removedCount = 0;
            if ($removeStale) {
                foreach ($staleKeys as $key) {
                    unset($translations[$key]);
                    $removedCount++;
                }
            }

            ksort($translations);

            if (! $isDryRun) {
                File::put($file, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
            }

            $summaryRows[] = [
                $code,
                count($existingKeys),
                count($missingKeys),
                count($staleKeys),
                $removeStale ? $removedCount : 'skipped',
                count($translations),
            ];

            if (count($missingKeys) > 0 || $removedCount > 0) {
                $action = $isDryRun ? 'Would sync' : 'Synced';
                $addedCount = count($missingKeys);
                $this->line("{$action} <info>{$code}.json</info>: +{$addedCount} added" .
                    ($removedCount > 0 ? ", -{$removedCount} removed" : ''));
            }
        }

        $this->newLine();
        $this->table(
            ['Lang', 'Before', 'Added', 'Stale', 'Removed', 'After'],
            $summaryRows
        );

        $this->newLine();
        if ($isDryRun) {
            $this->warn('Dry run complete. No files were modified.');
        } else {
            $this->info('Sync complete! All language files now have ' . count($enTranslations) . ' keys.');
        }

        return self::SUCCESS;
    }
}
