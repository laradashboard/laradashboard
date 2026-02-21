<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\QueryBuilderTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;

class MenuItem extends Model
{
    use HasFactory;
    use QueryBuilderTrait;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'type',
        'target',
        'target_blank',
        'icon',
        'css_classes',
        'position',
        'meta',
        'status',
    ];

    protected $casts = [
        'target_blank' => 'boolean',
        'position' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Get the menu this item belongs to.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the parent menu item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get child menu items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('position');
    }

    /**
     * Get all descendants recursively.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Scope to get active items only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get root items only.
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the URL for this menu item.
     */
    public function getUrl(): string
    {
        return match ($this->type) {
            'page' => $this->getPageUrl(),
            'post' => $this->getPostUrl(),
            'category' => $this->getCategoryUrl(),
            'tag' => $this->getTagUrl(),
            'route' => $this->getRouteUrl(),
            'custom' => $this->target ?? '#',
            default => '#',
        };
    }

    /**
     * Get URL for page type.
     */
    protected function getPageUrl(): string
    {
        if (! $this->target) {
            return '#';
        }

        // Check if it's a route name first
        try {
            if (Route::has($this->target)) {
                return route($this->target);
            }
        } catch (\Exception $e) {
            // Not a valid route name
        }

        // Otherwise treat as slug/path
        return url($this->target);
    }

    /**
     * Get URL for post type.
     */
    protected function getPostUrl(): string
    {
        if (! $this->target) {
            return '#';
        }

        $post = Post::where('slug', $this->target)
            ->where('status', 'published')
            ->first();

        if ($post) {
            return url("/post/{$post->slug}");
        }

        return '#';
    }

    /**
     * Get URL for category type.
     */
    protected function getCategoryUrl(): string
    {
        if (! $this->target) {
            return '#';
        }

        return url("/category/{$this->target}");
    }

    /**
     * Get URL for tag type.
     */
    protected function getTagUrl(): string
    {
        if (! $this->target) {
            return '#';
        }

        return url("/tag/{$this->target}");
    }

    /**
     * Get URL for route type.
     */
    protected function getRouteUrl(): string
    {
        if (! $this->target) {
            return '#';
        }

        try {
            return route($this->target);
        } catch (\Exception $e) {
            return '#';
        }
    }

    /**
     * Check if this item has children.
     */
    public function hasChildren(): bool
    {
        // Check if children relation is loaded and has items
        if ($this->relationLoaded('children')) {
            $children = $this->getRelation('children');

            return $children !== null && $children->isNotEmpty();
        }

        // Fall back to database query
        return $this->children()->exists();
    }

    /**
     * Get children collection safely.
     */
    public function getChildren(): \Illuminate\Support\Collection
    {
        if ($this->relationLoaded('children')) {
            return $this->getRelation('children') ?? collect();
        }

        return $this->children()->get();
    }

    /**
     * Get depth level of this item.
     */
    public function getDepth(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    /**
     * Get available item types.
     */
    public static function getAvailableTypes(): array
    {
        return [
            'custom' => __('Custom URL'),
            'page' => __('Page'),
            'post' => __('Post'),
            'category' => __('Category'),
            'tag' => __('Tag'),
            'route' => __('Route'),
        ];
    }

    /**
     * Check if this item is active (current URL matches).
     */
    public function isActive(): bool
    {
        $currentUrl = url()->current();
        $itemUrl = $this->getUrl();

        // Exact match
        if ($currentUrl === $itemUrl) {
            return true;
        }

        // Check if any child is active
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $child) {
                if ($child->isActive()) {
                    return true;
                }
            }
        }

        return false;
    }
}
