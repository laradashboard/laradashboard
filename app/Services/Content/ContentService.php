<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Models\Taxonomy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ContentService
{
    public function registerPostType(array $args): PostType
    {
        // Default values.
        $defaults = [
            'name' => null,
            'label' => null,
            'label_singular' => null,
            'description' => '',
            'public' => true,
            'has_archive' => true,
            'hierarchical' => false,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'supports_title' => true,
            'supports_editor' => true,
            'supports_thumbnail' => true,
            'supports_excerpt' => true,
            'supports_custom_fields' => true,
            'supports_taxonomies' => true,
            'taxonomies' => [],
        ];

        $args = array_merge($defaults, $args);

        // Required values
        if (empty($args['name'])) {
            throw new \InvalidArgumentException('Post type name is required');
        }

        // Set taxonomies correctly.
        if (! empty($args['supports_taxonomies']) && isset($args['taxonomies'])) {
            if (is_string($args['taxonomies'])) {
                $args['taxonomies'] = explode(',', $args['taxonomies']);
            }
            $args['taxonomies'] = array_map('trim', $args['taxonomies']);
        } else {
            $args['taxonomies'] = [];
        }

        // Create a PostType object.
        $postType = new PostType($args);

        // Get current post types.
        $postTypes = $this->getPostTypes();

        // Add or update post type.
        $postTypes[$postType->name] = $postType;

        // Store in cache (using serialized array format).
        $postTypesArray = collect($postTypes)->map->toArray();
        Cache::put('post_types', $postTypesArray, now()->addDay());

        return $postType;
    }

    public function registerTaxonomy(array $args, $postTypes = null): ?Taxonomy
    {
        // Default values.
        $defaults = [
            'name' => null,
            'label' => null,
            'label_singular' => null,
            'description' => '',
            'public' => true,
            'hierarchical' => false,
            'show_in_menu' => true,
            'show_featured_image' => false,
        ];

        $args = array_merge($defaults, $args);

        // Required values.
        if (empty($args['name'])) {
            return null;
        }

        // Set defaults for blank values.
        if (empty($args['label'])) {
            $args['label'] = Str::plural(Str::title($args['name']));
        }

        if (empty($args['label_singular'])) {
            $args['label_singular'] = Str::title($args['name']);
        }

        // Handle post types and update them to include this taxonomy.
        if (! empty($postTypes)) {
            if (is_string($postTypes)) {
                $postTypes = [$postTypes];
            } elseif (! is_array($postTypes)) {
                $postTypes = [];
            }

            $args['post_types'] = $postTypes;

            // Update existing post types to include this taxonomy.
            $this->addTaxonomyToPostTypes($args['name'], $postTypes);
        }

        // Create or update the taxonomy.
        $taxonomy = Taxonomy::updateOrCreate(
            ['name' => $args['name']],
            $args
        );

        // Clear cache.
        $this->clearTaxonomiesCache();

        return $taxonomy;
    }

    protected function addTaxonomyToPostTypes(string $taxonomy, array $postTypeNames): void
    {
        $postTypes = $this->getPostTypes();
        $updated = false;

        foreach ($postTypeNames as $name) {
            if (isset($postTypes[$name])) {
                $postType = $postTypes[$name];
                if (! in_array($taxonomy, $postType->taxonomies)) {
                    $postType->taxonomies[] = $taxonomy;
                    $updated = true;
                }
            }
        }

        if ($updated) {
            // Store updated post types in cache.
            $postTypesArray = collect($postTypes)->map->toArray();
            Cache::put('post_types', $postTypesArray, now()->addDay());
        }
    }

    public function getPostTypes(): Collection
    {
        $postTypesArray = Cache::get('post_types', []);

        // Convert arrays back to PostType objects
        $postTypes = collect();
        foreach ($postTypesArray as $name => $data) {
            $postTypes[$name] = new PostType($data);
        }

        return $postTypes;
    }

    public function getPostType(?string $name): ?PostType
    {
        $postTypes = $this->getPostTypes();

        return $postTypes[$name] ?? null;
    }

    public function getTaxonomies()
    {
        return Cache::remember('taxonomies', 3600, function () {
            return Taxonomy::all();
        });
    }

    public function clearPostTypesCache(): void
    {
        Cache::forget('post_types');
    }

    public function clearTaxonomiesCache(): void
    {
        Cache::forget('taxonomies');
    }
}
