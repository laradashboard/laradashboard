<?php

namespace App\Providers;

use App\Services\Content\ContentService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class ContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentService::class, function ($app) {
            return new ContentService();
        });
    }

    public function boot(): void
    {
        // Skip registering taxonomies if tables don't exist yet.
        try {
            if (! Schema::hasTable('taxonomies')) {
                return;
            }

            // Register default post types.
            $this->registerDefaultPostTypes();

            // Register default taxonomies.
            $this->registerDefaultTaxonomies();
        } catch (QueryException $e) {
            // Handle database connection issues or other query-related errors
            // Just exit gracefully for now.
            return;
        }
    }

    protected function tablesExist(array $tables): bool
    {
        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    protected function registerDefaultPostTypes(): void
    {
        $contentService = app(ContentService::class);

        // Register post type.
        $contentService->registerPostType([
            'name' => 'post',
            'label' => 'Posts',
            'label_singular' => 'Post',
            'description' => 'Default post type for blog entries',
            'taxonomies' => ['category', 'tag'],
        ]);

        // Register page type.
        $contentService->registerPostType([
            'name' => 'page',
            'label' => 'Pages',
            'label_singular' => 'Page',
            'description' => 'Default post type for static pages',
            'has_archive' => false,
            'hierarchical' => true,
            'supports_excerpt' => false,
            'taxonomies' => [],
        ]);

        // Allow other plugins/modules to register post types.
        if (function_exists('ld_do_action')) {
            ld_do_action('register_post_types', $contentService);
        }
    }

    protected function registerDefaultTaxonomies(): void
    {
        $contentService = app(ContentService::class);

        // Register category taxonomy for posts.
        $contentService->registerTaxonomy([
            'name' => 'category',
            'label' => 'Categories',
            'label_singular' => 'Category',
            'description' => 'Default taxonomy for categorizing posts',
            'hierarchical' => true,
            'show_featured_image' => true,
        ], 'post');

        // Register tag taxonomy for posts.
        $contentService->registerTaxonomy([
            'name' => 'tag',
            'label' => 'Tags',
            'label_singular' => 'Tag',
            'description' => 'Default taxonomy for tagging posts',
            'hierarchical' => false,
            'show_featured_image' => true,
        ], 'post');

        // Allow other plugins/modules to register taxonomies
        if (function_exists('ld_do_action')) {
            ld_do_action('register_taxonomies', $contentService);
        }
    }

    protected function getPostTypeIcon(string $postType): string
    {
        return match ($postType) {
            'post' => 'lucide:file-text',
            'page' => 'lucide:file',
            default => 'lucide:files'
        };
    }

    protected function getTaxonomyIcon(string $taxonomy): string
    {
        return match ($taxonomy) {
            'category' => 'lucide:folder',
            'tag' => 'lucide:tags',
            default => 'lucide:bookmark'
        };
    }
}
