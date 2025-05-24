<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'taxonomy',
        'description',
        'parent_id'
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Term $term) {
            if (empty($term->slug)) {
                $term->slug = Str::slug($term->name);
            }
        });
    }

    /**
     * Get the taxonomy that owns the term.
     */
    public function taxonomyModel(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class, 'taxonomy', 'name');
    }

    /**
     * Get the parent term.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'parent_id');
    }

    /**
     * Get the child terms.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Term::class, 'parent_id');
    }

    /**
     * The posts that belong to the term.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'term_relationships');
    }
}
