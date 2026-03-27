<?php

declare(strict_types=1);

namespace App\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Theme Facade
 *
 * @method static string|null active()
 * @method static mixed setting(string $key, mixed $default = null)
 * @method static string layout()
 * @method static string viewPrefix()
 * @method static bool isActive(string $alias)
 * @method static void registerDefaults(array $defaults)
 *
 * @see \App\Support\ThemeManager
 */
class Theme extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'theme';
    }
}
