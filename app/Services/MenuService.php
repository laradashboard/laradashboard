<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\Term;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MenuService
{
    /**
     * Cache key prefix for menus.
     */
    protected const CACHE_PREFIX = 'menu_';

    /**
     * Cache TTL in seconds (1 hour).
     */
    protected const CACHE_TTL = 3600;

    /**
     * Get a menu by its location.
     */
    public function getMenuByLocation(string $location): ?Menu
    {
        return Cache::remember(
            self::CACHE_PREFIX . 'location_' . $location,
            self::CACHE_TTL,
            fn () => Menu::forLocation($location)
        );
    }

    /**
     * Get nested menu items for a location.
     */
    public function getNestedItemsForLocation(string $location): Collection
    {
        $menu = $this->getMenuByLocation($location);

        if (! $menu) {
            return collect();
        }

        return Cache::remember(
            self::CACHE_PREFIX . 'items_' . $location,
            self::CACHE_TTL,
            fn () => $menu->getNestedItems()
        );
    }

    /**
     * Create a new menu.
     */
    public function createMenu(array $data): Menu
    {
        $menu = Menu::create([
            'name' => $data['name'],
            'location' => $data['location'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        $this->clearMenuCache($menu->location);

        return $menu;
    }

    /**
     * Update a menu.
     */
    public function updateMenu(Menu $menu, array $data): Menu
    {
        $oldLocation = $menu->location;

        $menu->update([
            'name' => $data['name'] ?? $menu->name,
            'location' => $data['location'] ?? $menu->location,
            'description' => $data['description'] ?? $menu->description,
            'status' => $data['status'] ?? $menu->status,
        ]);

        // Clear cache for old and new locations
        $this->clearMenuCache($oldLocation);
        if ($oldLocation !== $menu->location) {
            $this->clearMenuCache($menu->location);
        }

        return $menu;
    }

    /**
     * Delete a menu and all its items.
     */
    public function deleteMenu(Menu $menu): bool
    {
        $location = $menu->location;
        $deleted = $menu->delete();

        $this->clearMenuCache($location);

        return (bool) $deleted;
    }

    /**
     * Create a menu item.
     */
    public function createMenuItem(Menu $menu, array $data): MenuItem
    {
        $position = $data['position'] ?? $this->getNextPosition($menu->id, $data['parent_id'] ?? null);

        $item = MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $data['parent_id'] ?? null,
            'label' => $data['label'],
            'type' => $data['type'] ?? 'custom',
            'target' => $data['target'] ?? null,
            'target_blank' => $data['target_blank'] ?? false,
            'icon' => $data['icon'] ?? null,
            'css_classes' => $data['css_classes'] ?? null,
            'position' => $position,
            'meta' => $data['meta'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        $this->clearMenuCache($menu->location);

        return $item;
    }

    /**
     * Update a menu item.
     */
    public function updateMenuItem(MenuItem $item, array $data): MenuItem
    {
        $item->update([
            'parent_id' => $data['parent_id'] ?? $item->parent_id,
            'label' => $data['label'] ?? $item->label,
            'type' => $data['type'] ?? $item->type,
            'target' => $data['target'] ?? $item->target,
            'target_blank' => $data['target_blank'] ?? $item->target_blank,
            'icon' => $data['icon'] ?? $item->icon,
            'css_classes' => $data['css_classes'] ?? $item->css_classes,
            'position' => $data['position'] ?? $item->position,
            'meta' => $data['meta'] ?? $item->meta,
            'status' => $data['status'] ?? $item->status,
        ]);

        $this->clearMenuCache($item->menu->location);

        return $item;
    }

    /**
     * Delete a menu item.
     */
    public function deleteMenuItem(MenuItem $item): bool
    {
        $location = $item->menu->location;
        $deleted = $item->delete();

        $this->clearMenuCache($location);

        return (bool) $deleted;
    }

    /**
     * Update menu items order (for drag-drop reordering).
     */
    public function updateItemsOrder(Menu $menu, array $items): void
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $itemData) {
                MenuItem::where('id', $itemData['id'])->update([
                    'parent_id' => $itemData['parent_id'] ?? null,
                    'position' => $itemData['position'],
                ]);
            }
        });

        $this->clearMenuCache($menu->location);
    }

    /**
     * Get the next position for a new item.
     */
    protected function getNextPosition(int $menuId, ?int $parentId = null): int
    {
        $maxPosition = MenuItem::where('menu_id', $menuId)
            ->where('parent_id', $parentId)
            ->max('position');

        return ($maxPosition ?? -1) + 1;
    }

    /**
     * Clear menu cache for a location.
     */
    public function clearMenuCache(?string $location = null): void
    {
        if ($location) {
            Cache::forget(self::CACHE_PREFIX . 'location_' . $location);
            Cache::forget(self::CACHE_PREFIX . 'items_' . $location);
        } else {
            // Clear all menu caches
            foreach (Menu::getAvailableLocations() as $loc => $name) {
                Cache::forget(self::CACHE_PREFIX . 'location_' . $loc);
                Cache::forget(self::CACHE_PREFIX . 'items_' . $loc);
            }
        }
    }

    /**
     * Get available pages for menu item selection.
     */
    public function getAvailablePages(): Collection
    {
        return Post::where('post_type', 'page')
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);
    }

    /**
     * Get available posts for menu item selection.
     */
    public function getAvailablePosts(): Collection
    {
        return Post::where('post_type', 'post')
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);
    }

    /**
     * Get available categories for menu item selection.
     */
    public function getAvailableCategories(): Collection
    {
        if (! class_exists(Term::class)) {
            return collect();
        }

        return Term::where('taxonomy', 'category')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    /**
     * Get available tags for menu item selection.
     */
    public function getAvailableTags(): Collection
    {
        if (! class_exists(Term::class)) {
            return collect();
        }

        return Term::where('taxonomy', 'tag')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    /**
     * Get available routes for menu item selection.
     */
    public function getAvailableRoutes(): array
    {
        $routes = [];
        $routeCollection = \Illuminate\Support\Facades\Route::getRoutes()->getRoutes();

        foreach ($routeCollection as $route) {
            $name = $route->getName();
            if ($name && ! str_starts_with($name, 'admin.') && ! str_starts_with($name, 'api.')) {
                $routes[$name] = $name;
            }
        }

        ksort($routes);

        return $routes;
    }

    /**
     * Duplicate a menu with all its items.
     */
    public function duplicateMenu(Menu $menu, string $newLocation): Menu
    {
        $newMenu = $this->createMenu([
            'name' => $menu->name . ' (Copy)',
            'location' => $newLocation,
            'description' => $menu->description,
            'status' => 'inactive',
        ]);

        $this->duplicateMenuItems($menu, $newMenu);

        return $newMenu;
    }

    /**
     * Duplicate menu items from one menu to another.
     */
    protected function duplicateMenuItems(Menu $sourceMenu, Menu $targetMenu, ?int $sourceParentId = null, ?int $targetParentId = null): void
    {
        $items = MenuItem::where('menu_id', $sourceMenu->id)
            ->where('parent_id', $sourceParentId)
            ->orderBy('position')
            ->get();

        foreach ($items as $item) {
            $newItem = $this->createMenuItem($targetMenu, [
                'parent_id' => $targetParentId,
                'label' => $item->label,
                'type' => $item->type,
                'target' => $item->target,
                'target_blank' => $item->target_blank,
                'icon' => $item->icon,
                'css_classes' => $item->css_classes,
                'position' => $item->position,
                'meta' => $item->meta,
                'status' => $item->status,
            ]);

            // Recursively duplicate children
            $this->duplicateMenuItems($sourceMenu, $targetMenu, $item->id, $newItem->id);
        }
    }

    /**
     * Import menu items from an array structure.
     */
    public function importMenuItems(Menu $menu, array $items, ?int $parentId = null): void
    {
        foreach ($items as $position => $itemData) {
            $item = $this->createMenuItem($menu, [
                'parent_id' => $parentId,
                'label' => $itemData['label'],
                'type' => $itemData['type'] ?? 'custom',
                'target' => $itemData['target'] ?? null,
                'target_blank' => $itemData['target_blank'] ?? false,
                'icon' => $itemData['icon'] ?? null,
                'position' => $position,
                'status' => 'active',
            ]);

            // Import children recursively
            if (! empty($itemData['children'])) {
                $this->importMenuItems($menu, $itemData['children'], $item->id);
            }
        }
    }

    /**
     * Export menu to array structure.
     */
    public function exportMenu(Menu $menu): array
    {
        return [
            'name' => $menu->name,
            'location' => $menu->location,
            'description' => $menu->description,
            'items' => $this->exportMenuItems($menu->getNestedItems()),
        ];
    }

    /**
     * Export menu items to array structure.
     */
    protected function exportMenuItems(Collection $items): array
    {
        return $items->map(function (MenuItem $item) {
            $data = [
                'label' => $item->label,
                'type' => $item->type,
                'target' => $item->target,
                'target_blank' => $item->target_blank,
                'icon' => $item->icon,
            ];

            if ($item->hasChildren()) {
                $data['children'] = $this->exportMenuItems($item->children);
            }

            return $data;
        })->values()->toArray();
    }
}
