<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'label', 
        'label_singular', 
        'description', 
        'public', 
        'has_archive', 
        'hierarchical', 
        'show_in_menu', 
        'show_in_nav_menus', 
        'supports_title', 
        'supports_editor', 
        'supports_thumbnail', 
        'supports_excerpt', 
        'supports_custom_fields', 
        'taxonomies'
    ];

    protected $casts = [
        'public' => 'boolean',
        'has_archive' => 'boolean',
        'hierarchical' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_in_nav_menus' => 'boolean',
        'supports_title' => 'boolean',
        'supports_editor' => 'boolean',
        'supports_thumbnail' => 'boolean',
        'supports_excerpt' => 'boolean',
        'supports_custom_fields' => 'boolean',
        'taxonomies' => 'array'
    ];

    /**
     * Get all the posts for this post type
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'post_type', 'name');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'name';
    }
}
