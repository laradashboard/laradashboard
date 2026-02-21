<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\QueryBuilderTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Menu extends Model
{
    use HasFactory;
    use QueryBuilderTrait;

    protected $fillable = [
        'name',
        'location',
        'description',
        'status',
    ];

    /**
     * Get all items for this menu.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('position');
    }

    /**
     * Get only root-level items (no parent).
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->orderBy('position');
    }

    /**
     * Get hierarchical menu items as a nested collection.
     */
    public function getNestedItems(): Collection
    {
        $items = $this->items()
            ->where('status', 'active')
            ->get()
            ->keyBy('id');

        return $this->buildTree($items);
    }

    /**
     * Build nested tree structure from flat collection.
     */
    protected function buildTree(Collection $items, ?int $parentId = null): Collection
    {
        return $items
            ->filter(fn (MenuItem $item) => $item->parent_id === $parentId)
            ->sortBy('position')
            ->map(function (MenuItem $item) use ($items) {
                $item->setRelation('children', $this->buildTree($items, $item->id));

                return $item;
            })
            ->values();
    }

    /**
     * Scope to get active menus only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Get menu by location.
     */
    public static function forLocation(string $location): ?self
    {
        return static::where('location', $location)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Get available menu locations.
     */
    public static function getAvailableLocations(): array
    {
        return [
            'primary' => __('Primary Navigation'),
            'footer-1' => __('Footer Column 1'),
            'footer-2' => __('Footer Column 2'),
            'footer-3' => __('Footer Column 3'),
            'mobile' => __('Mobile Navigation'),
        ];
    }

    /**
     * Check if this menu is assigned to a location.
     */
    public function isAssignedTo(string $location): bool
    {
        return $this->location === $location;
    }
}
