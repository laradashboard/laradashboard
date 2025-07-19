<?php

namespace App\Services\Content;

use Illuminate\Support\Str;

class PostType
{
    public string $name = '';

    public string $label = '';

    public string $label_singular = '';

    public string $description = '';

    public bool $public = true;

    public bool $has_archive = true;

    public bool $hierarchical = false;

    public bool $show_in_menu = true;

    public bool $show_in_nav_menus = true;

    public bool $supports_title = true;

    public bool $supports_editor = true;

    public bool $supports_thumbnail = true;

    public bool $supports_excerpt = true;

    public bool $supports_custom_fields = true;

    public array $taxonomies = [];

    /**
     * Create a new post type instance
     */
    public function __construct(array $attributes = [])
    {
        $this->setAttributes($attributes);
    }

    /**
     * Set post type attributes
     */
    public function setAttributes(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        // Set defaults if not provided
        if (empty($this->label) && ! empty($this->name)) {
            $this->label = Str::plural(Str::title($this->name));
        }

        if (empty($this->label_singular) && ! empty($this->name)) {
            $this->label_singular = Str::title($this->name);
        }

        return $this;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'label_singular' => $this->label_singular,
            'description' => $this->description,
            'public' => $this->public,
            'has_archive' => $this->has_archive,
            'hierarchical' => $this->hierarchical,
            'show_in_menu' => $this->show_in_menu,
            'show_in_nav_menus' => $this->show_in_nav_menus,
            'supports_title' => $this->supports_title,
            'supports_editor' => $this->supports_editor,
            'supports_thumbnail' => $this->supports_thumbnail,
            'supports_excerpt' => $this->supports_excerpt,
            'supports_custom_fields' => $this->supports_custom_fields,
            'taxonomies' => $this->taxonomies,
        ];
    }

    /**
     * Check if the post type supports a feature
     */
    public function supports(string $feature): bool
    {
        $property = 'supports_'.$feature;

        return property_exists($this, $property) ? $this->$property : false;
    }

    /**
     * Get icon for this post type
     */
    public function getIcon(): string
    {
        return match ($this->name) {
            'post' => 'lucide:file-text',
            'page' => 'lucide:file',
            default => 'lucide:files'
        };
    }
}
