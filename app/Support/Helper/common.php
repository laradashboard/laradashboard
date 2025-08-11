<?php

declare(strict_types=1);

use App\Services\ImageService;
use App\Services\LanguageService;
use App\Services\MenuService\AdminMenuItem;
use App\Services\MenuService\AdminMenuService;
use App\Services\Modules\ModuleService;
use App\Services\PasswordService;
use App\Services\SlugService;
use Illuminate\Foundation\Vite;

function get_module_asset_paths(): array
{
    return app(ModuleService::class)->getModuleAssetPath();
}

/**
 * support for vite hot reload overriding manifest file.
 */
function module_vite_compile(string $module, string $asset, ?string $hotFilePath = null, $manifestFile = '.vite/manifest.json'): Vite
{
    return app(ModuleService::class)
        ->moduleViteCompile($module, $asset, $hotFilePath, $manifestFile);
}

/**
 * Invoke a method on the SettingService.
 *
 * @param  string  $method  The method name to invoke
 * @param  mixed  ...$parameters  The parameters to pass to the method
 *
 * @return mixed  The result of the method invocation
 */
function invoke_setting(string $method, ...$parameters): mixed
{
    $service = app(App\Services\SettingService::class);

    if (! method_exists($service, $method)) {
        throw new \InvalidArgumentException("Method {$method} does not exist on SettingService");
    }

    return $service->{$method}(...$parameters);
}

/**
 * Add a new setting.
 *
 * @param  string  $optionName  The name of the setting option
 * @param  mixed  $optionValue  The value of the setting option
 * @param  bool  $autoload  Whether to autoload this setting (default: false)
 */
function add_setting(string $optionName, mixed $optionValue, bool $autoload = false): void
{
    invoke_setting('addSetting', $optionName, $optionValue, $autoload);
}

/**
 * Update an existing setting.
 *
 * @param  string  $optionName  The name of the setting option
 * @param  mixed  $optionValue  The value of the setting option
 * @param  bool|null  $autoload  Whether to autoload this setting (default: null)
 */
function update_setting(string $optionName, mixed $optionValue, ?bool $autoload = null): bool
{
    return invoke_setting('updateSetting', $optionName, $optionValue, $autoload);
}

/**
 * Delete a setting.
 *
 * @param  string  $optionName  The name of the setting option
 */
function delete_setting(string $optionName): bool
{
    return invoke_setting('deleteSetting', $optionName);
}

/**
 * Get a setting value.
 *
 * @param  string  $optionName  The name of the setting option
 * @param  mixed  $default  The default value if the setting does not exist
 *
 * @return mixed  The setting value or the default value
 */
function get_setting(string $optionName, mixed $default = null): mixed
{
    try {
        return invoke_setting('getSetting', $optionName) ?? $default;
    } catch (\Exception $e) {
        return $default;
    }
}

/**
 * Get all settings.
 *
 * @param  int|bool|null  $autoload  Autoload setting (default: true)
 * @return array  All settings
 */
function get_settings(int|bool|null $autoload = true): array
{
    return invoke_setting('getSettings', $autoload);
}

/**
 * Store uploaded image and return its public URL.
 *
 * @param  \Illuminate\Http\Request|array  $input  Either the full request or a file from validated input
 * @param  string  $fileKey  The key name (e.g., 'photo')
 * @param  string  $path  Target relative path (e.g., 'uploads/contacts')
 */
function store_image_url($input, string $fileKey, string $path): ?string
{
    return app(ImageService::class)
        ->storeImageAndGetUrl($input, $fileKey, $path);
}

/**
 * Delete an image from the public path.
 *
 * @param  string  $imageUrl  The URL of the image to delete
 * @return bool  True if the image was deleted, false otherwise
 */
function delete_image_from_public_path(string $imageUrl): bool
{
    return app(ImageService::class)
        ->deleteImageFromPublic($imageUrl);
}

/**
 * Add a menu item to the admin sidebar.
 *
 * @param  array|AdminMenuItem  $item  The menu item configuration array or instance
 * @param  string|null  $group  The group to add the item to (defaults to 'Main')
 */
function add_menu_item(array|AdminMenuItem $item, ?string $group = null): void
{
    app(AdminMenuService::class)->addMenuItem($item, $group);
}

/**
 * Get the list of available languages with their flags.
 */
function get_languages(): array
{
    return app(LanguageService::class)->getActiveLanguages();
}

/**
 * Get the SVG icon for a given name.
 *
 * @param  string  $name  The name of the icon file (without .svg extension)
 * @param  string  $classes  Additional CSS classes to apply to the SVG
 * @param  string  $fallback  Fallback icon name if the SVG file does not exist
 * @return string  The SVG icon HTML or an Iconify icon if the SVG does not exist
 */
function svg_icon(string $name, string $classes = '', string $fallback = ''): string
{
    return app(ImageService::class)
        ->getSvgIcon($name, $classes, $fallback);
}

/**
 * Generate a unique slug for a given string.
 *
 * @param  string  $string  The base string to generate the slug from
 * @param  string  $table   The table name to check for uniqueness
 * @param  string  $column  The column name to check for uniqueness (default: 'slug')
 * @param  string  $separator  The separator to use in the slug (default: '-')
 *
 * @return string  The generated unique slug
 */
function generate_unique_slug(string $string, string $table = 'users', string $column = 'slug', string $separator = '-'): string
{
    return app(SlugService::class)
        ->generateSlugFromString(
            $string,
            $table,
            $column,
            $separator
        );
}

/**
 * Generate a secure password.
 *
 * @param  int  $length  The length of the password
 * @param  bool  $includeSpecialChars  Whether to include special characters
 *
 * @return string  The generated password
 */
function generate_secure_password(int $length = 12, bool $includeSpecialChars = true): string
{
    return app(PasswordService::class)
        ->generatePassword($length, $includeSpecialChars);
}
