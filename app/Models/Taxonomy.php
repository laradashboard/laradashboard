<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Taxonomy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'label_singular',
        'description',
        'public',
        'hierarchical',
        'show_in_menu',
        'show_featured_image',
        'post_types',
    ];

    protected $casts = [
        'public' => 'boolean',
        'hierarchical' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_featured_image' => 'boolean',
        'post_types' => 'array',
    ];

    /**
     * Get all the terms for this taxonomy
     */
    public function terms(): HasMany
    {
        return $this->hasMany(Term::class, 'taxonomy', 'name');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'name';
    }
}
