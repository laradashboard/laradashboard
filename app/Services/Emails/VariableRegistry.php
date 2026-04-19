<?php

declare(strict_types=1);

namespace App\Services\Emails;

use App\Enums\Hooks\EmailVariableFilterHook;
use App\Support\Facades\Hook;

/**
 * Tidy sugar layer over the `EmailVariableFilterHook::VARIABLES` filter.
 *
 * Modules can call `VariableRegistry::register('organization.name', [...])`
 * during their service provider boot instead of hand-writing a
 * `Hook::addFilter` closure. A single listener walks the registered
 * entries and merges them into the variable array.
 *
 * The raw hook still works — this is purely a developer-experience
 * convenience, not a replacement.
 */
class VariableRegistry
{
    /**
     * @var array<string, array{
     *   label: string,
     *   sample_data: mixed,
     *   description?: string,
     *   group?: string,
     *   resolver?: callable,
     * }>
     */
    private static array $entries = [];

    private static bool $listenerAttached = false;

    public static function register(string $key, array $meta): void
    {
        self::$entries[$key] = array_merge([
            'label' => $key,
            'sample_data' => '',
        ], $meta);

        self::ensureListener();
    }

    /**
     * @return array<string, array>
     */
    public static function all(): array
    {
        return self::$entries;
    }

    /**
     * Clears the registry — used by tests between runs.
     */
    public static function reset(): void
    {
        self::$entries = [];
    }

    private static function ensureListener(): void
    {
        if (self::$listenerAttached) {
            return;
        }

        self::$listenerAttached = true;

        Hook::addFilter(EmailVariableFilterHook::VARIABLES, function (array $variables) {
            foreach (self::$entries as $key => $meta) {
                if (isset($variables[$key])) {
                    continue;
                }

                $resolver = $meta['resolver'] ?? null;
                $replacement = is_callable($resolver) ? (string) $resolver() : '';

                $variables[$key] = [
                    'label' => $meta['label'],
                    'sample_data' => $meta['sample_data'],
                    'replacement' => $replacement,
                    'description' => $meta['description'] ?? null,
                    'group' => $meta['group'] ?? null,
                ];
            }

            return $variables;
        });
    }
}
