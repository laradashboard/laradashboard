<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Services\Content\PostType;
use App\Services\ContentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_type',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'meta',
        'parent_id',
        'published_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'published_at' => 'datetime'
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            
            if (empty($post->user_id) && auth()->check()) {
                $post->user_id = auth()->id();
            }
        });
    }

    /**
     * Get the user that owns the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post type object for this post
     * 
     * @return PostType|null
     */
    public function getPostTypeObject(): ?PostType
    {
        return app(ContentService::class)->getPostType($this->post_type);
    }

    /**
     * Get the parent post.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    /**
     * Get the child posts.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Post::class, 'parent_id');
    }

    /**
     * The terms that belong to the post.
     */
    public function terms(): BelongsToMany
    {
        return $this->belongsToMany(Term::class, 'term_relationships');
    }

    /**
     * Get categories for the post
     */
    public function categories()
    {
        return $this->terms()->where('taxonomy', 'category');
    }

    /**
     * Get tags for the post
     */
    public function tags()
    {
        return $this->terms()->where('taxonomy', 'tag');
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'publish')
              ->where(function ($query) {
                  $query->whereNull('published_at')
                        ->orWhere('published_at', '<=', now());
              });
    }

    /**
     * Scope a query to only include posts of a given type.
     */
    public function scopeType(Builder $query, string $type): void
    {
        $query->where('post_type', $type);
    }

    /**
     * Check if this post type supports a specific feature
     * 
     * @param string $feature Feature name (e.g., 'editor', 'thumbnail', 'excerpt')
     * @return bool
     */
    public function supportsFeature(string $feature): bool
    {
        $postType = $this->getPostTypeObject();
        return $postType ? $postType->supports($feature) : false;
    }
}
