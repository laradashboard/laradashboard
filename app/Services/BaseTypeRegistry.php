<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Facades\Hook;
use Illuminate\Support\Str;

class BaseTypeRegistry
{
    /**
     * Optional enum class associated with this registry.
     *
     * @var class-string<\BackedEnum>|null
     */
    protected static ?string $enumClass = null;

    /**
     * Central storage for types keyed by class to isolate per-subclass storage.
     *
     * @var array<string, string[]>
     */
    protected static array $allTypes = [];

    /**
     * Central storage for meta keyed by class.
     *
     * @var array<string, array<string, array>>
     */
    protected static array $allMeta = [];

    /**
     * Register a notification type.
     */
    public static function register(string $type, array $meta = []): void
    {
        $key = static::class;
        if (! isset(static::$allTypes[$key])) {
            static::$allTypes[$key] = [];
        }
        if (! in_array($type, static::$allTypes[$key], true)) {
            static::$allTypes[$key][] = $type;
        }
        if (! empty($meta)) {
            if (! isset(static::$allMeta[$key])) {
                static::$allMeta[$key] = [];
            }
            static::$allMeta[$key][$type] = $meta;
        }
    }

    /**
     * Register multiple types at once. Each element may be either:
     * - a simple string value
     * - an array with shape: ['type' => 'value', 'meta' => ['label' => fn() => 'Label', ...]]
     *
     * @param array<int, string|array{type:string,meta?:array<string, string|callable>}> $types
     */
    public static function registerMany(array $types): void
    {
        foreach ($types as $type) {
            if (is_array($type)) {
                if (isset($type['type'])) {
                    static::register($type['type'], $type['meta'] ?? []);
                    continue;
                }
            }
            static::register((string) $type);
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
        $key = static::class;
        if (! isset(static::$allTypes[$key])) {
            static::$allTypes[$key] = [];
        }

        return Hook::applyFilters(
            static::getFilterName(),
            array_map(function ($type) {
                return Str::snake($type);
            }, array_values(
                static::$allTypes[$key]
            ))
        );
    }

    /**
     * Return the hook filter name to apply when calling all(). Override in subclasses.
     */
    protected static function getFilterName(): string
    {
        return 'type_values';
    }

    /**
     * Get metadata for a type or null.
     *
     * @return array|null
     */
    public static function getMeta(string $type): ?array
    {
        $key = static::class;
        if (! isset(static::$allMeta[$key])) {
            return null;
        }
        return static::$allMeta[$key][$type] ?? null;
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
        if (empty($meta['icon'])) {
            return null;
        }
        if (is_callable($meta['icon'])) {
            return (string) call_user_func($meta['icon'], $type);
        }
        return (string) $meta['icon'];
    }

    public static function getColor(string $type): ?string
    {
        $meta = static::getMeta($type);
        if (empty($meta['color'])) {
            return null;
        }
        if (is_callable($meta['color'])) {
            return (string) call_user_func($meta['color'], $type);
        }
        return (string) $meta['color'];
    }

    /**
     * Does registry contain the given type?
     */
    public static function has(string $type): bool
    {
        $key = static::class;
        if (! isset(static::$allTypes[$key])) {
            return false;
        }
        return in_array($type, static::$allTypes[$key], true);
    }

    /**
     * Clear registered types — useful for testing.
     */
    public static function clear(): void
    {
        $key = static::class;
        static::$allTypes[$key] = [];
        static::$allMeta[$key] = [];
    }

    /**
     * Get refreshed values, ensuring enum class is called to register base types.
     *
     * @return string[]
     */
    public static function getRefreshedValues(): array
    {
        $values = static::all();

        if (empty($values)) {
            if (! empty(static::$enumClass)) {
                $enumClass = static::$enumClass;
                // If enum class has a getValues method (custom), call it to let enum register base values
                $callable = [$enumClass, 'getValues'];
                if (is_callable($callable)) {
                    call_user_func($callable);
                } elseif (is_subclass_of($enumClass, \BackedEnum::class)) {
                    // Fallback: use native cases to register values if the enum does not expose getValues
                    foreach ($enumClass::cases() as $case) {
                        static::register((string) $case->value);
                    }
                }
            }
            $values = static::all();
        }

        return $values;
    }

    /**
     * Get dropdown items as [value => label].
     *
     * @return array<string, string>
     */
    public static function getDropdownItems(): array
    {
        return collect(static::getRefreshedValues())
            ->mapWithKeys(function ($type) {
                $label = static::getLabel($type);
                if (empty($label) && ! empty(static::$enumClass) && is_subclass_of(static::$enumClass, \BackedEnum::class)) {
                    $enumClass = static::$enumClass;
                    $enum = null;
                    $enum = call_user_func([$enumClass, 'tryFrom'], $type);
                    if ($enum !== null && method_exists($enum, 'label')) {
                        $label = $enum->label();
                    }
                }
                if (empty($label)) {
                    $label = ucfirst(str_replace('_', ' ', $type));
                }
                return [$type => $label];
            })
            ->toArray();
    }
}
