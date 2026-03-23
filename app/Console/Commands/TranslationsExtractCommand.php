<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TranslationsExtractCommand extends Command
{
    protected $signature = 'translations:extract
        {--dry-run : Preview without writing}';

    protected $description = 'Scan the codebase and extract missing translatable strings into en.json';

    /**
     * Directories to scan (relative to base_path).
     */
    protected array $scanDirs = [
        'resources/views',
        'app',
        'resources/js',
    ];

    /**
     * Directories to exclude from scanning.
     */
    protected array $excludeDirs = [
        'modules',
        'vendor',
        'node_modules',
    ];

    /**
     * Files to exclude from scanning.
     */
    protected array $excludeFiles = [
        'resources/js/lara-builder/i18n/index.js',
    ];

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

        $isDryRun = $this->option('dry-run');

        // Collect and scan files
        $files = $this->collectFiles();
        $this->info('Scanning ' . count($files) . ' files...');

        $extracted = [];
        foreach ($files as $file) {
            $strings = $this->extractStringsFromFile($file);
            foreach ($strings as $string) {
                if (! $this->shouldSkip($string)) {
                    $extracted[$string] = $string;
                }
            }
        }

        // Find strings missing from en.json
        $newKeys = array_diff_key($extracted, $enTranslations);

        if (empty($newKeys)) {
            $this->info('No new translatable strings found. en.json is up to date.');

            return self::SUCCESS;
        }

        $this->info('Found ' . count($newKeys) . ' new translatable strings.');

        if ($this->output->isVerbose()) {
            foreach (array_keys($newKeys) as $key) {
                $this->line("  + {$key}");
            }
        }

        if (! $isDryRun) {
            $merged = array_merge($enTranslations, $newKeys);
            ksort($merged);
            File::put($enFile, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
            $this->info('Added ' . count($newKeys) . ' new keys to en.json (total: ' . count($merged) . ' keys).');
        } else {
            $this->warn('Dry run complete. No files were modified.');
        }

        return self::SUCCESS;
    }

    /**
     * Collect all scannable files from the configured directories.
     */
    protected function collectFiles(): array
    {
        $basePath = base_path();
        $files = [];

        $excludeAbsolute = array_map(fn ($dir) => $basePath . '/' . $dir, $this->excludeDirs);
        $excludeFilesAbsolute = array_map(fn ($f) => $basePath . '/' . $f, $this->excludeFiles);

        foreach ($this->scanDirs as $dir) {
            $fullDir = $basePath . '/' . $dir;

            if (! File::isDirectory($fullDir)) {
                continue;
            }

            $allFiles = File::allFiles($fullDir);

            foreach ($allFiles as $file) {
                $path = $file->getRealPath();

                // Exclude directories
                $skip = false;
                foreach ($excludeAbsolute as $excludeDir) {
                    if (str_starts_with($path, $excludeDir . '/')) {
                        $skip = true;
                        break;
                    }
                }

                if ($skip) {
                    continue;
                }

                // Exclude specific files
                if (in_array($path, $excludeFilesAbsolute)) {
                    continue;
                }

                // Filter by extension
                $ext = $file->getExtension();
                if (in_array($ext, ['php', 'blade.php', 'js', 'jsx', 'ts', 'tsx', 'vue'])) {
                    $files[] = $path;
                } elseif (str_ends_with($path, '.blade.php')) {
                    $files[] = $path;
                }
            }
        }

        return $files;
    }

    /**
     * Extract translatable strings from a file.
     */
    protected function extractStringsFromFile(string $path): array
    {
        $content = File::get($path);
        $strings = [];

        $patterns = $this->getPatterns($path);

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                // Some patterns have multiple capture groups (e.g., _n with singular and plural)
                foreach ($matches as $groupIndex => $group) {
                    if ($groupIndex === 0) {
                        continue; // Skip full match
                    }
                    foreach ($group as $match) {
                        $unescaped = $this->unescapeString($match);
                        if ($unescaped !== '') {
                            $strings[] = $unescaped;
                        }
                    }
                }
            }
        }

        return $strings;
    }

    /**
     * Get regex patterns appropriate for the file type.
     */
    protected function getPatterns(string $path): array
    {
        $patterns = [
            // __('single quoted')
            "/__\(\s*'((?:[^'\\\\]|\\\\.)*?)'\s*[,)]/",
            // __("double quoted")
            '/__\(\s*"((?:[^"\\\\]|\\\\.)*?)"\s*[,)]/',
            // trans('single quoted') — negative lookbehind to avoid matching _trans, etc.
            "/(?<![_a-zA-Z])trans\(\s*'((?:[^'\\\\]|\\\\.)*?)'\s*[,)]/",
            // trans("double quoted")
            '/(?<![_a-zA-Z])trans\(\s*"((?:[^"\\\\]|\\\\.)*?)"\s*[,)]/',
        ];

        if (str_ends_with($path, '.blade.php')) {
            $patterns[] = "/@lang\(\s*'((?:[^'\\\\]|\\\\.)*?)'\s*[,)]/";
            $patterns[] = '/@lang\(\s*"((?:[^"\\\\]|\\\\.)*?)"\s*[,)]/';
        }

        $jsExtensions = ['js', 'jsx', 'ts', 'tsx', 'vue'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        if (in_array($ext, $jsExtensions)) {
            // _x('text', 'context')
            $patterns[] = "/_x\(\s*'((?:[^'\\\\]|\\\\.)*?)'\s*,/";
            $patterns[] = '/_x\(\s*"((?:[^"\\\\]|\\\\.)*?)"\s*,/';
            // _n('singular', 'plural', n) — capture both strings
            $patterns[] = "/_n\(\s*'((?:[^'\\\\]|\\\\.)*?)'\s*,\s*'((?:[^'\\\\]|\\\\.)*?)'\s*,/";
            $patterns[] = '/_n\(\s*"((?:[^"\\\\]|\\\\.)*?)"\s*,\s*"((?:[^"\\\\]|\\\\.)*?)"\s*,/';
            // esc_html__('text')
            $patterns[] = "/esc_html__\(\s*'((?:[^'\\\\]|\\\\.)*?)'\s*[,)]/";
            $patterns[] = '/esc_html__\(\s*"((?:[^"\\\\]|\\\\.)*?)"\s*[,)]/';
        }

        return $patterns;
    }

    /**
     * Determine whether a string should be skipped.
     */
    protected function shouldSkip(string $str): bool
    {
        // Empty or whitespace-only
        if (trim($str) === '') {
            return true;
        }

        // Single character
        if (mb_strlen($str) <= 1) {
            return true;
        }

        // Pure numeric strings (not translatable, and json_decode mangles numeric keys)
        if (ctype_digit($str)) {
            return true;
        }

        // Pure punctuation / symbols (no letters or digits)
        if (preg_match('/^[^\p{L}\p{N}]+$/u', $str)) {
            return true;
        }

        // PHP file-based translation keys like "auth.failed", "validation.required"
        // Pattern: lowercase dot-separated segments with no spaces
        if (preg_match('/^[a-z_]+(\.[a-z_]+)+$/', $str)) {
            return true;
        }

        return false;
    }

    /**
     * Unescape a matched string (handle \' and \").
     */
    protected function unescapeString(string $str): string
    {
        return str_replace(["\'", '\"'], ["'", '"'], $str);
    }
}
