<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Builder\BlockRenderer;
use App\Services\Content\ContentService;
use App\Services\Content\PostType;
use App\Concerns\QueryBuilderTrait;
use App\Concerns\HasMedia;
use App\Enums\PostStatus;
use App\Observers\PostObserver;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[ObservedBy([PostObserver::class])]
class Post extends Model implements SpatieHasMedia
{
    use HasFactory;
    use QueryBuilderTrait;
    use HasMedia;

    protected $fillable = [
        'user_id',
        'post_type',
        'title',
        'slug',
        'excerpt',
        'content',
        'design_json',
        'status',
        'meta',
        'parent_id',
        'published_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'design_json' => 'array',
        'published_at' => 'datetime',
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

            if (empty($post->user_id) && Auth::check()) {
                $post->user_id = Auth::id();
            }
        });
    }

    public static function getPostStatuses(): array
    {
        return collect(PostStatus::cases())
            ->mapWithKeys(fn ($case) => [$case->value => __(Str::of($case->name)->title()->toString())])
            ->toArray();
    }

    /**
     * Get the user that owns the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the author of the post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the post-type object for this post
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
     * @param  mixed  $default
     * @return mixed
     */
    public function getMeta(string $key, $default = null)
    {
        $meta = $this->postMeta()->where('meta_key', $key)->first();

        return $meta ? $meta->getAttribute('meta_value') : $default;
    }

    /**
     * Set a meta-value
     *
     * @param  mixed  $value
     */
    public function setMeta(string $key, $value): PostMeta
    {
        $meta = $this->postMeta()->updateOrCreate(
            ['meta_key' => $key],
            ['meta_value' => $value]
        );

        return $meta instanceof PostMeta ? $meta : new PostMeta($meta->getAttributes());
    }

    /**
     * Delete a meta value
     */
    public function deleteMeta(string $key): bool
    {
        return $this->postMeta()->where('meta_key', $key)->delete() > 0;
    }

    /**
     * Get all meta as array with full info
     */
    public function getAllMeta(): array
    {
        // Make sure we're loading the postMeta relationship
        if (! $this->relationLoaded('postMeta')) {
            $this->load('postMeta');
        }

        return $this->postMeta
            ->mapWithKeys(function ($meta) {
                return [
                    $meta->getAttribute('meta_key') => [
                        'value' => $meta->getAttribute('meta_value') ?? '',
                        'type' => $meta->getAttribute('type') ?? 'input',
                        'default_value' => $meta->getAttribute('default_value') ?? '',
                    ],
                ];
            })
            ->toArray();
    }

    /**
     * Get all meta as simple key-value pairs
     */
    public function getAllMetaValues(): array
    {
        return $this->postMeta()
            ->pluck('meta_value', 'meta_key')
            ->toArray();
    }

    /**
     * Register media collections for posts
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Register media conversions for posts
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Preview conversion for admin interface
        $this->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300);

        // Thumbnail for featured images
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10);

        // Medium size for content display
        $this->addMediaConversion('medium')
            ->width(500)
            ->height(500);

        // Large size for detailed view
        $this->addMediaConversion('large')
            ->width(1000)
            ->height(1000);
    }

    /**
     * Get the featured image URL
     */
    public function getFeaturedImageUrl(string $conversion = ''): ?string
    {
        $media = $this->getFirstMedia('featured');

        if (! $media) {
            return null;
        }

        return $conversion ? $media->getUrl($conversion) : $media->getUrl();
    }

    /**
     * Check if post has featured image
     */
    public function hasFeaturedImage(): bool
    {
        return $this->hasMedia('featured');
    }

    /**
     * Get gallery images
     */
    public function getGalleryImages(): array
    {
        return $this->getMedia('gallery')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'original' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb'),
                'medium' => $media->getUrl('medium'),
                'large' => $media->getUrl('large'),
            ];
        })->toArray();
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
        $query->where('status', PostStatus::PUBLISHED->value)
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
     * Apply tag filter to the query
     */
    public function scopeFilterByTag(Builder $query, $tagId): void
    {
        $query->whereHas('terms', function ($q) use ($tagId) {
            $q->where('id', $tagId)
                ->where('taxonomy', 'tag');
        });
    }

    /**
     * Check if this post type supports a specific feature
     *
     * @param  string  $feature  Feature name (e.g., 'editor', 'thumbnail', 'excerpt')
     */
    public function supportsFeature(string $feature): bool
    {
        $postType = $this->getPostTypeObject();

        return $postType ? $postType->supports($feature) : false;
    }

    /**
     * Render the post content with dynamic blocks processed.
     *
     * Processes any dynamic blocks (like CRM Contact) through server-side
     * rendering via BlockRenderer. Use this method instead of accessing
     * $post->content directly when displaying content.
     *
     * @param  string  $context  The rendering context ('page', 'email', 'campaign')
     * @return string The processed HTML content
     */
    public function renderContent(string $context = 'page'): string
    {
        if (empty($this->content)) {
            return '';
        }

        return app(BlockRenderer::class)->processContent($this->content, $context);
    }

    /**
     * Get searchable columns for the model.
     */
    protected function getSearchableColumns(): array
    {
        return ['title', 'excerpt', 'content'];
    }

    /**
     * Get columns that should be excluded from sorting.
     */
    protected function getExcludedSortColumns(): array
    {
        return ['content', 'excerpt', 'meta'];
    }
}
