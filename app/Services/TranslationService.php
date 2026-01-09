<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;

class TranslationService
{
    /**
     * Common translation groups
     */
    protected array $groups = [
        'json' => 'General',
        'auth' => 'Authentication',
        'pagination' => 'Pagination',
        'passwords' => 'Passwords',
        'validation' => 'Validation',
    ];

    /**
     * Get the core lang path.
     */
    protected function getCoreLangPath(): string
    {
        return resource_path('lang');
    }

    /**
     * Get the user lang path.
     */
    protected function getUserLangPath(): string
    {
        return resource_path('user-lang');
    }

    /**
     * Get all available translation groups.
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Get translations from file.
     */
    public function getTranslations(string $lang, string $group = 'json'): array
    {
        if ($group === 'json') {
            return $this->getJsonTranslations($lang);
        }

        return $this->getGroupTranslations($lang, $group);
    }

    /**
     * Get JSON translations (merged: core + user overrides).
     */
    public function getJsonTranslations(string $lang): array
    {
        // Get core translations
        $coreTranslations = $this->getCoreJsonTranslations($lang);

        // Get user translations (overrides)
        $userTranslations = $this->getUserJsonTranslations($lang);

        // Merge: user translations override core translations
        return array_merge($coreTranslations, $userTranslations);
    }

    /**
     * Get core JSON translations.
     */
    public function getCoreJsonTranslations(string $lang): array
    {
        $path = $this->getCoreLangPath()."/{$lang}.json";

        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);

        return json_decode($content, true) ?: [];
    }

    /**
     * Get user JSON translations (overrides).
     */
    public function getUserJsonTranslations(string $lang): array
    {
        $path = $this->getUserLangPath()."/{$lang}.json";

        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);

        return json_decode($content, true) ?: [];
    }

    /**
     * Get group translations from PHP files (merged: core + user overrides).
     */
    public function getGroupTranslations(string $lang, string $group): array
    {
        // Get core translations
        $coreTranslations = $this->getCoreGroupTranslations($lang, $group);

        // Get user translations (overrides)
        $userTranslations = $this->getUserGroupTranslations($lang, $group);

        // Deep merge: user translations override core translations
        return $this->arrayMergeRecursiveDistinct($coreTranslations, $userTranslations);
    }

    /**
     * Get core group translations from PHP files.
     */
    public function getCoreGroupTranslations(string $lang, string $group): array
    {
        $path = $this->getCoreLangPath()."/{$lang}/{$group}.php";

        if (File::exists($path)) {
            return include $path;
        }

        // If file doesn't exist but English version does, use English as reference
        if ($lang !== 'en') {
            $enPath = $this->getCoreLangPath()."/en/{$group}.php";
            if (File::exists($enPath)) {
                return include $enPath;
            }
        }

        return $this->getDefaultTranslationsForGroup($group);
    }

    /**
     * Get user group translations from PHP files (overrides).
     */
    public function getUserGroupTranslations(string $lang, string $group): array
    {
        $path = $this->getUserLangPath()."/{$lang}/{$group}.php";

        if (! File::exists($path)) {
            return [];
        }

        return include $path;
    }

    /**
     * Recursively merge arrays, with second array values taking priority.
     */
    protected function arrayMergeRecursiveDistinct(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Get default translations structure for a specific group.
     */
    public function getDefaultTranslationsForGroup(string $group): array
    {
        switch ($group) {
            case 'auth':
                return [
                    'failed' => 'These credentials do not match our records.',
                    'password' => 'The provided password is incorrect.',
                    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
                ];
            case 'pagination':
                return [
                    'previous' => '&laquo; Previous',
                    'next' => 'Next &raquo;',
                ];
            case 'passwords':
                return [
                    'reset' => 'Your password has been reset!',
                    'sent' => 'We have emailed your password reset link.',
                    'throttled' => 'Please wait before retrying.',
                    'token' => 'This password reset token is invalid.',
                    'user' => "We can't find a user with that email address.",
                ];
            case 'validation':
                // Return just a few common validation messages as default
                return [
                    'accepted' => 'The :attribute must be accepted.',
                    'active_url' => 'The :attribute is not a valid URL.',
                    'after' => 'The :attribute must be a date after :date.',
                    'alpha' => 'The :attribute may only contain letters.',
                    'alpha_dash' => 'The :attribute may only contain letters, numbers, dashes and underscores.',
                    'required' => 'The :attribute field is required.',
                    'email' => 'The :attribute must be a valid email address.',
                ];
            default:
                return [];
        }
    }

    /**
     * Create a new language translation file.
     */
    public function createLanguageFile(string $lang, string $group): bool
    {
        // Check if language already exists
        if ($group === 'json' && File::exists(resource_path("lang/{$lang}.json"))) {
            return false;
        }

        if ($group !== 'json' && File::exists(resource_path("lang/{$lang}/{$group}.php"))) {
            return false;
        }

        // Create language file based on group
        if ($group === 'json') {
            // Copy from English or create new
            if (File::exists(resource_path('lang/en.json'))) {
                // Read English JSON file
                $englishContent = File::get(resource_path('lang/en.json'));
                $englishTranslations = json_decode($englishContent, true) ?: [];

                // Create a new array with the same keys but empty values
                $emptyTranslations = [];
                foreach ($englishTranslations as $key => $value) {
                    $emptyTranslations[$key] = '';
                }

                // Save the new JSON file with empty values
                File::put(
                    resource_path("lang/{$lang}.json"),
                    json_encode($emptyTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
            } else {
                // Create empty JSON file
                File::put(resource_path("lang/{$lang}.json"), '{}');
            }
        } else {
            // Create group file
            if (File::exists(resource_path("lang/en/{$group}.php"))) {
                // If English exists, copy structure but with empty values
                $enTranslations = include resource_path("lang/en/{$group}.php");
                $emptyTranslations = $this->createEmptyTranslations($enTranslations);
                $this->createGroupTranslationFile($lang, $group, $emptyTranslations);
            } else {
                // Create with default translations but with empty values
                $defaultTranslations = $this->getDefaultTranslationsForGroup($group);
                $emptyTranslations = $this->createEmptyTranslations($defaultTranslations);
                $this->createGroupTranslationFile($lang, $group, $emptyTranslations);
            }
        }

        return true;
    }

    /**
     * Recursively create array with same keys but empty values
     */
    public function createEmptyTranslations(array $translations): array
    {
        $result = [];

        foreach ($translations as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->createEmptyTranslations($value);
            } else {
                $result[$key] = '';
            }
        }

        return $result;
    }

    /**
     * Create a new group translation file.
     */
    public function createGroupTranslationFile(string $lang, string $group, array $translations): void
    {
        $path = resource_path("lang/{$lang}/{$group}.php");

        // Prepare file content
        $content = "<?php\n\nreturn ".$this->varExport($translations, true).";\n";

        // Create the directory if it doesn't exist
        $directory = dirname($path);
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Write the file
        File::put($path, $content);
    }

    /**
     * Save translations to file.
     */
    public function saveTranslations(string $lang, array $translations, string $group = 'json'): bool
    {
        if ($group === 'json') {
            return $this->saveJsonTranslations($lang, $translations);
        }

        return $this->saveGroupTranslations($lang, $group, $translations);
    }

    /**
     * Save JSON translations to user-lang folder (only differences from core).
     */
    public function saveJsonTranslations(string $lang, array $translations): bool
    {
        // Get core translations to compare
        $coreTranslations = $this->getCoreJsonTranslations($lang);

        // Find only the translations that differ from core
        $userOverrides = $this->findDifferentTranslations($translations, $coreTranslations);

        $userLangPath = $this->getUserLangPath();
        $path = "{$userLangPath}/{$lang}.json";

        // Create the user-lang directory if it doesn't exist
        if (! File::exists($userLangPath)) {
            File::makeDirectory($userLangPath, 0755, true);
        }

        // If no overrides, remove the user file if it exists
        if (empty($userOverrides)) {
            if (File::exists($path)) {
                File::delete($path);
            }

            return true;
        }

        // Sort translations alphabetically
        ksort($userOverrides);

        // Save with pretty print to user-lang folder
        return (bool) File::put(
            $path,
            json_encode($userOverrides, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Save group translations to user-lang PHP files (only differences from core).
     */
    public function saveGroupTranslations(string $lang, string $group, array $translations): bool
    {
        // Get core translations to compare
        $coreTranslations = $this->getCoreGroupTranslations($lang, $group);

        // Find only the translations that differ from core
        $userOverrides = $this->findDifferentTranslationsRecursive($translations, $coreTranslations);

        $userLangPath = $this->getUserLangPath();
        $path = "{$userLangPath}/{$lang}/{$group}.php";

        // If no overrides, remove the user file if it exists
        if (empty($userOverrides)) {
            if (File::exists($path)) {
                File::delete($path);
            }

            return true;
        }

        // Prepare file content
        $content = "<?php\n\nreturn ".$this->varExport($userOverrides, true).";\n";

        // Create the directory if it doesn't exist
        $directory = dirname($path);
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Write the file
        return (bool) File::put($path, $content);
    }

    /**
     * Find translations that differ from core (for flat arrays like JSON).
     */
    protected function findDifferentTranslations(array $submitted, array $core): array
    {
        $different = [];

        foreach ($submitted as $key => $value) {
            // Include if: key doesn't exist in core, or value is different from core
            if (! array_key_exists($key, $core) || $core[$key] !== $value) {
                $different[$key] = $value;
            }
        }

        return $different;
    }

    /**
     * Find translations that differ from core (for nested arrays like PHP files).
     */
    protected function findDifferentTranslationsRecursive(array $submitted, array $core): array
    {
        $different = [];

        foreach ($submitted as $key => $value) {
            if (! array_key_exists($key, $core)) {
                // Key doesn't exist in core - include it
                $different[$key] = $value;
            } elseif (is_array($value) && is_array($core[$key])) {
                // Both are arrays - recurse
                $nestedDiff = $this->findDifferentTranslationsRecursive($value, $core[$key]);
                if (! empty($nestedDiff)) {
                    $different[$key] = $nestedDiff;
                }
            } elseif ($value !== $core[$key]) {
                // Value is different - include it
                $different[$key] = $value;
            }
        }

        return $different;
    }

    /**
     * Get all available translation groups for a language.
     */
    public function getAvailableTranslationGroups(string $lang): array
    {
        $availableGroups = ['json'];

        // Check if language directory exists
        $langPath = resource_path("lang/{$lang}");
        if (File::exists($langPath)) {
            // Get all PHP files in the directory
            $files = File::files($langPath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    $availableGroups[] = $file->getFilenameWithoutExtension();
                }
            }
        }

        // Check English directory for additional groups
        if ($lang !== 'en') {
            $enPath = resource_path('lang/en');
            if (File::exists($enPath)) {
                $files = File::files($enPath);
                foreach ($files as $file) {
                    if ($file->getExtension() === 'php') {
                        $group = $file->getFilenameWithoutExtension();
                        if (! in_array($group, $availableGroups)) {
                            $availableGroups[] = $group;
                        }
                    }
                }
            }
        }

        return $availableGroups;
    }

    /**
     * Calculate statistics for translation progress
     */
    public function calculateTranslationStats(array $translations, array $enTranslations, string $group): array
    {
        $totalKeys = 0;
        $nonEmptyTranslations = 0;

        if ($group === 'json') {
            $totalKeys = count($enTranslations);

            foreach ($translations as $key => $value) {
                if (isset($enTranslations[$key]) && ! empty(trim((string) $value))) {
                    $nonEmptyTranslations++;
                }
            }
        } else {
            $totalKeys = $this->countTotalKeys($enTranslations);
            $nonEmptyTranslations = $this->countNonEmptyTranslations($translations, $enTranslations);
        }

        $missingTranslations = $totalKeys - $nonEmptyTranslations;
        $progressPercentage = $totalKeys > 0 ? ($nonEmptyTranslations / $totalKeys * 100) : 0;

        return [
            'totalKeys' => $totalKeys,
            'translated' => $nonEmptyTranslations,
            'missing' => $missingTranslations,
            'percentage' => $progressPercentage,
        ];
    }

    /**
     * Count non-empty translations recursively in nested arrays
     */
    public function countNonEmptyTranslations(array $translationArray, array $enArray): int
    {
        $count = 0;
        foreach ($enArray as $key => $value) {
            if (is_array($value)) {
                // Recurse into nested arrays
                if (isset($translationArray[$key]) && is_array($translationArray[$key])) {
                    $count += $this->countNonEmptyTranslations($translationArray[$key], $value);
                }
            } elseif (isset($translationArray[$key]) && ! empty(trim((string) $translationArray[$key]))) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Count total keys recursively in nested arrays
     */
    public function countTotalKeys(array $array): int
    {
        $count = 0;
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $count += $this->countTotalKeys($value);
            } else {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Count translations recursively, including nested arrays
     */
    public function countTranslationsRecursively(array $translations): int
    {
        $count = 0;

        foreach ($translations as $translation) {
            if (is_array($translation)) {
                $count += $this->countTranslationsRecursively($translation);
            } else {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Better formatting for nested arrays when exporting to PHP.
     */
    public function varExport($expression, bool $return = false): string
    {
        $export = var_export($expression, true);
        $patterns = [
            "/array \(/" => '[',
            "/^([ ]*)\)(,?)$/m" => '$1]$2',
            "/=>[ ]?\n[ ]+\[/" => '=> [',
            "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
        ];
        $export = preg_replace(array_keys($patterns), array_values($patterns), $export);

        if ($return) {
            return $export;
        }

        echo $export;

        return '';
    }

    /**
     * Prepare nested translation data for form inputs.
     */
    public function flattenTranslations(array $translations, string $prefix = ''): array
    {
        $result = [];

        foreach ($translations as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenTranslations($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Reconstruct nested array from flattened form inputs.
     */
    public function unflattenTranslations(array $translations): array
    {
        $result = [];

        foreach ($translations as $key => $value) {
            $parts = explode('.', $key);
            $current = &$result;

            foreach ($parts as $i => $part) {
                if ($i === count($parts) - 1) {
                    $current[$part] = $value;
                } else {
                    if (! isset($current[$part]) || ! is_array($current[$part])) {
                        $current[$part] = [];
                    }
                    $current = &$current[$part];
                }
            }

            unset($current);
        }

        return $result;
    }
}
