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
     * Get JSON translations.
     */
    public function getJsonTranslations(string $lang): array
    {
        $path = resource_path("lang/{$lang}.json");

        if (! File::exists($path)) {
            if ($lang !== 'en') {
                // Create file with empty translations if it doesn't exist
                File::put($path, '{}');
            } else {
                // For English, create with default Laravel translations
                $defaultTranslations = [];
                File::put($path, json_encode($defaultTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        $content = File::get($path);

        return json_decode($content, true) ?: [];
    }

    /**
     * Get group translations from PHP files.
     */
    public function getGroupTranslations(string $lang, string $group): array
    {
        $path = resource_path("lang/{$lang}/{$group}.php");

        // If the directory doesn't exist, create it
        if (! File::exists(resource_path("lang/{$lang}"))) {
            File::makeDirectory(resource_path("lang/{$lang}"), 0755, true);
        }

        // If file doesn't exist but English version does, copy structure from English
        if (! File::exists($path) && File::exists(resource_path("lang/en/{$group}.php"))) {
            $enTranslations = include resource_path("lang/en/{$group}.php");

            // Create file with empty translations or copy English structure
            $this->createGroupTranslationFile($lang, $group, $enTranslations);

            // Check again after creating the file.
            if (File::exists($path)) {
                return include $path;
            }

            return [];
        }

        // If file exists, return its contents
        if (File::exists($path)) {
            return include $path;
        }

        // If no file exists, create a default structure based on group
        $defaultTranslations = $this->getDefaultTranslationsForGroup($group);
        $this->createGroupTranslationFile($lang, $group, $defaultTranslations);

        return $defaultTranslations;
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
     * Save JSON translations.
     */
    public function saveJsonTranslations(string $lang, array $translations): bool
    {
        $path = resource_path("lang/{$lang}.json");

        // Sort translations alphabetically
        ksort($translations);

        // Save with pretty print
        return (bool) File::put(
            $path,
            json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Save group translations to PHP files.
     */
    public function saveGroupTranslations(string $lang, string $group, array $translations): bool
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
        return (bool) File::put($path, $content);
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
