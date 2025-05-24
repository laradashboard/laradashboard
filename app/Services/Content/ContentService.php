<?php

namespace App\Services\Content;

use App\Models\Taxonomy;
use App\Services\Content\PostType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class ContentService
{
    /**
     * Register a new post type
     */
    public function registerPostType(array $args): PostType
    {
        // Create a PostType object
        $postType = new PostType($args);
        
        if (empty($postType->name)) {
            throw new \InvalidArgumentException('Post type name is required');
        }

        // Get current post types
        $postTypes = $this->getPostTypes();
        
        // Add or update post type
        $postTypes[$postType->name] = $postType;
        
        // Store in cache (using serialized array format)
        $postTypesArray = collect($postTypes)->map->toArray()->all();
        Cache::put('post_types', $postTypesArray, now()->addDay());

        return $postType;
    }

    /**
     * Register a new taxonomy
     */
    public function registerTaxonomy(array $args, $postTypes = null): ?Taxonomy
    {
        // Default values
        $defaults = [
            'name' => null,
            'label' => null,
            'label_singular' => null,
            'description' => '',
            'public' => true,
            'hierarchical' => false,
            'show_in_menu' => true,
        ];

        $args = array_merge($defaults, $args);

        // Required values
        if (empty($args['name'])) {
            return null;
        }

        // Set defaults for blank values
        if (empty($args['label'])) {
            $args['label'] = Str::plural(Str::title($args['name']));
        }

        if (empty($args['label_singular'])) {
            $args['label_singular'] = Str::title($args['name']);
        }

        // Handle post types
        if (!empty($postTypes)) {
            if (is_string($postTypes)) {
                $postTypes = [$postTypes];
            } elseif (!is_array($postTypes)) {
                $postTypes = [];
            }
            
            $args['post_types'] = $postTypes;
        }

        // Create or update the taxonomy
        $taxonomy = Taxonomy::updateOrCreate(
            ['name' => $args['name']],
            $args
        );

        // Clear cache
        $this->clearTaxonomiesCache();

        return $taxonomy;
    }

    /**
     * Get all registered post types
     */
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

    /**
     * Get a specific post type by name
     */
    public function getPostType(string $name): ?PostType
    {
        $postTypes = $this->getPostTypes();
        return $postTypes[$name] ?? null;
    }

    /**
     * Get all registered taxonomies
     */
    public function getTaxonomies()
    {
        return Cache::remember('taxonomies', 3600, function () {
            return Taxonomy::all();
        });
    }

    /**
     * Clear post types cache
     */
    public function clearPostTypesCache()
    {
        Cache::forget('post_types');
    }

    /**
     * Clear taxonomies cache
     */
    public function clearTaxonomiesCache()
    {
        Cache::forget('taxonomies');
    }
}
