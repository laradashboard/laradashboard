<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Services\Content\PostType;
use App\Services\Content\ContentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Traits\QueryBuilderTrait;

class Post extends Model
{
    use HasFactory, QueryBuilderTrait;

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
     * Get the post meta.
     */
    public function postMeta(): HasMany
    {
        return $this->hasMany(PostMeta::class);
    }

    /**
     * Get a specific meta value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getMeta(string $key, $default = null)
    {
        $meta = $this->postMeta()->where('meta_key', $key)->first();
        return $meta ? $meta->meta_value : $default;
    }

    /**
     * Set a meta value
     * 
     * @param string $key
     * @param mixed $value
     * @return PostMeta
     */
    public function setMeta(string $key, $value): PostMeta
    {
        return $this->postMeta()->updateOrCreate(
            ['meta_key' => $key],
            ['meta_value' => $value]
        );
    }

    /**
     * Delete a meta value
     * 
     * @param string $key
     * @return bool
     */
    public function deleteMeta(string $key): bool
    {
        return $this->postMeta()->where('meta_key', $key)->delete() > 0;
    }

    /**
     * Get all meta as array with full info
     * 
     * @return array
     */
    public function getAllMeta(): array
    {
        // Make sure we're loading the postMeta relationship
        if (!$this->relationLoaded('postMeta')) {
            $this->load('postMeta');
        }

        return $this->postMeta
            ->mapWithKeys(function ($meta) {
                return [
                    $meta->meta_key => [
                        'value' => $meta->meta_value ?? '',
                        'type' => $meta->type ?? 'input',
                        'default_value' => $meta->default_value ?? ''
                    ]
                ];
            })
            ->toArray();
    }

    /**
     * Get all meta as simple key-value pairs
     * 
     * @return array
     */
    public function getAllMetaValues(): array
    {
        return $this->postMeta()
            ->pluck('meta_value', 'meta_key')
            ->toArray();
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
     * Apply category filter to the query
     */
    public function scopeFilterByCategory(Builder $query, $categoryId): void
    {
        $query->whereHas('terms', function ($q) use ($categoryId) {
            $q->where('id', $categoryId)
              ->where('taxonomy', 'category');
        });
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

    /**
     * Get searchable columns for the model.
     *
     * @return array
     */
    protected function getSearchableColumns(): array
    {
        return ['title', 'excerpt', 'content'];
    }
    
    /**
     * Get columns that should be excluded from sorting.
     *
     * @return array
     */
    protected function getExcludedSortColumns(): array
    {
        return ['content', 'excerpt', 'meta'];
    }
}
