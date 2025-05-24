<?php

use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Vite as ViteFacade;
use App\Services\LanguageService;
use App\Services\MenuService\AdminMenuItem;
use App\Services\MenuService\AdminMenuService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

function get_module_asset_paths(): array
{
    $paths = [];
    if (file_exists('build/manifest.json')) {
        $files = json_decode(file_get_contents('build/manifest.json'), true);
        foreach ($files as $file) {
            $paths[] = $file['src'];
        }
    }
    return $paths;
}

function handle_ld_setting(string $method, ...$parameters): mixed
{
    return app(App\Services\SettingService::class)->{$method}(...$parameters);
}

function add_setting(string $optionName, mixed $optionValue, bool $autoload = false): void
{
    handle_ld_setting('addSetting', $optionName, $optionValue, $autoload);
}

function update_setting(string $optionName, mixed $optionValue, ?bool $autoload = null): bool
{
    return handle_ld_setting('updateSetting', $optionName, $optionValue, $autoload);
}

function delete_setting(string $optionName): bool
{
    return handle_ld_setting('deleteSetting', $optionName);
}

function get_setting(string $optionName): mixed
{
    return handle_ld_setting('getSetting', $optionName);
}

function get_settings(int|bool|null $autoload = true): array
{
    return handle_ld_setting('getSettings', $autoload);
}

if (!function_exists('storeImageAndGetUrl')) {
    function storeImageAndGetUrl($request, $fileName, $path)
    {
        if ($request->hasFile($fileName)) {
            $uploadedFile = $request->file($fileName);
            $fileName = $fileName . '.' . $uploadedFile->getClientOriginalExtension();
            $targetPath = public_path($path);
            if (!file_exists($targetPath)) {
                mkdir($targetPath, 0777, true);
            }
            $uploadedFile->move($targetPath, $fileName);
            return asset($path . '/' . $fileName);
        }
        return null;
    }
}

if (!function_exists('deleteImageFromPublic')) {
    function deleteImageFromPublic(string $imageUrl)
    {
        $urlParts = parse_url($imageUrl);
        $filePath = ltrim($urlParts['path'], '/');
        if (File::exists(public_path($filePath))) {
            if (File::delete(public_path($filePath))) {
                Log::info("File deleted successfully: " . $filePath);
            } else {
                Log::error("Failed to delete file: " . $filePath);
            }
        } else {
            Log::warning("File does not exist: " . $filePath);
        }
    }
}


if (!function_exists('module_vite_compile')) {
    /**
     * support for vite hot reload overriding manifest file.
     */
    function module_vite_compile(string $module, string $asset, ?string $hotFilePath = null, $manifestFile = '.vite/manifest.json'): Vite
    {
        return ViteFacade::useHotFile($hotFilePath ?: storage_path('vite.hot'))
            ->useBuildDirectory($module)
            ->useManifestFilename($manifestFile)
            ->withEntryPoints([$asset]);
    }
}

if (!function_exists('add_menu_item')) {
    /**
     * Add a menu item to the admin sidebar.
     * 
     * @param array|AdminMenuItem $item The menu item configuration array or instance
     * @param string|null $group The group to add the item to (defaults to 'Main')
     * @return void
     */
    function add_menu_item(array|AdminMenuItem $item, ?string $group = null): void
    {
        app(AdminMenuService::class)->addMenuItem($item, $group);
    }
}

if (!function_exists('get_languages')) {
    /**
     * Get the list of available languages with their flags.
     *
     * @return array
     */
    function get_languages(): array
    {
        return app(LanguageService::class)->getActiveLanguages();
    }
}

/**
 * Content management helpers
 */
if (!function_exists('register_post_type')) {
    /**
     * Register a new post type
     * 
     * @param string $name Post type name
     * @param array $args Post type arguments
     * @return array
     */
    function register_post_type(string $name, array $args = [])
    {
        $args['name'] = $name;
        return app(\App\Services\ContentService::class)->registerPostType($args);
    }
}

if (!function_exists('register_taxonomy')) {
    /**
     * Register a new taxonomy
     * 
     * @param string $name Taxonomy name
     * @param array $args Taxonomy arguments
     * @param string|array|null $postTypes Post types to associate with
     * @return \App\Models\Taxonomy|null
     */
    function register_taxonomy(string $name, array $args = [], $postTypes = null)
    {
        $args['name'] = $name;
        return app(\App\Services\ContentService::class)->registerTaxonomy($args, $postTypes);
    }
}

if (!function_exists('get_post_types')) {
    /**
     * Get all registered post types
     * 
     * @return \Illuminate\Support\Collection
     */
    function get_post_types()
    {
        return app(\App\Services\ContentService::class)->getPostTypes();
    }
}

if (!function_exists('get_post_type')) {
    /**
     * Get a specific post type
     * 
     * @param string $name Post type name
     * @return array|null
     */
    function get_post_type(string $name)
    {
        return app(\App\Services\ContentService::class)->getPostType($name);
    }
}

if (!function_exists('get_taxonomies')) {
    /**
     * Get all registered taxonomies
     * 
     * @return \Illuminate\Support\Collection
     */
    function get_taxonomies()
    {
        return app(\App\Services\ContentService::class)->getTaxonomies();
    }
}

if (!function_exists('get_posts')) {
    /**
     * Get posts with various filtering options
     * 
     * @param array $args Query arguments
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function get_posts(array $args = [])
    {
        $query = \App\Models\Post::query();
        
        // Post type filter
        if (isset($args['post_type'])) {
            $query->where('post_type', $args['post_type']);
        }
        
        // Status filter (default to published)
        if (isset($args['status'])) {
            $query->where('status', $args['status']);
        } else {
            $query->where('status', 'publish');
            $query->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
        }
        
        // Taxonomy query
        if (isset($args['tax_query']) && is_array($args['tax_query'])) {
            foreach ($args['tax_query'] as $tax_query) {
                if (isset($tax_query['taxonomy']) && isset($tax_query['terms'])) {
                    $query->whereHas('terms', function ($q) use ($tax_query) {
                        $q->where('taxonomy', $tax_query['taxonomy'])
                          ->whereIn('id', (array) $tax_query['terms']);
                    });
                }
            }
        }
        
        // Order
        $orderBy = $args['orderby'] ?? 'published_at';
        $order = $args['order'] ?? 'desc';
        $query->orderBy($orderBy, $order);
        
        // Limit
        if (isset($args['limit'])) {
            $query->limit($args['limit']);
        }
        
        // Offset
        if (isset($args['offset'])) {
            $query->offset($args['offset']);
        }
        
        return $query->get();
    }
}

if (!function_exists('get_post_type_icon')) {
    /**
     * Get the icon for a post type
     * 
     * @param string $postType Post type name
     * @return string Icon class
     */
    function get_post_type_icon(string $postType): string
    {
        return match($postType) {
            'post' => 'bi bi-file-earmark-text',
            'page' => 'bi bi-file-earmark',
            default => 'bi bi-collection'
        };
    }
}

if (!function_exists('get_taxonomy_icon')) {
    /**
     * Get the icon for a taxonomy
     * 
     * @param string $taxonomy Taxonomy name
     * @return string Icon class
     */
    function get_taxonomy_icon(string $taxonomy): string
    {
        return match($taxonomy) {
            'category' => 'bi bi-folder',
            'tag' => 'bi bi-tags',
            default => 'bi bi-bookmark'
        };
    }
}
