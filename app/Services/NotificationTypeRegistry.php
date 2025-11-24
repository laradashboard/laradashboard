<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Facades\Hook;

class NotificationTypeRegistry
{
    /**
     * Registered types
     *
     * @var string[]
     */
    protected static array $types = [];

    /**
     * Optional metadata for types, like label and icon.
     *
     * @var array<string, array>
     */
    protected static array $meta = [];

    /**
     * Register a notification type.
     */
    public static function register(string $type, array $meta = []): void
    {
        if (! in_array($type, static::$types, true)) {
            static::$types[] = $type;
        }
        if (! empty($meta)) {
            static::$meta[$type] = $meta;
        }
    }

    /**
     * Register multiple types at once.
     *
     * @param string[] $types
     */
    public static function registerMany(array $types): void
    {
        foreach ($types as $type) {
            if (is_array($type)) {
                // Allow [{ type: 'x', meta: ['label' => 'X']}, 'y']
                if (isset($type['type'])) {
                    static::register($type['type'], $type['meta'] ?? []);
                    continue;
                }
            }
            static::register($type);
        }
    }

    /**
     * Return all registered types. We still pass through Hook::applyFilters
     * to support existing modules using filters.
     *
     * @return string[]
     */
    public static function all(): array
    {
        return Hook::applyFilters('notification_type_values', array_values(static::$types));
    }

    /**
     * Get metadata for a type or null.
     *
     * @return array|null
     */
    public static function getMeta(string $type): ?array
    {
        return static::$meta[$type] ?? null;
    }

    public static function getLabel(string $type): ?string
    {
        $meta = static::getMeta($type);
        if (! empty($meta['label'])) {
            if (is_callable($meta['label'])) {
                return (string) call_user_func($meta['label'], $type);
            }
            return (string) $meta['label'];
        }
        return null;
    }

    public static function getIcon(string $type): ?string
    {
        $meta = static::getMeta($type);
        return $meta['icon'] ?? null;
    }

    /**
     * Does registry contain the given type?
     */
    public static function has(string $type): bool
    {
        return in_array($type, static::$types, true);
    }

    /**
     * Clear registered types — useful for testing.
     */
    public static function clear(): void
    {
        static::$types = [];
    }
}
