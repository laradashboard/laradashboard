<?php

declare(strict_types=1);

namespace App\View\Concerns;

use App\Services\MenuService;
use Illuminate\Support\Collection;

/** @phpstan-ignore trait.unused */
trait HasMenuData
{
    /**
     * Load nested menu items for a given location.
     */
    protected function loadMenuItems(string $location): Collection
    {
        try {
            $menuService = app(MenuService::class);

            return $menuService->getNestedItemsForLocation($location);
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Load footer columns from core Menu locations.
     */
    protected function loadFooterColumns(array $locations = ['footer-1', 'footer-2', 'footer-3']): array
    {
        $columns = [];

        try {
            $menuService = app(MenuService::class);

            foreach ($locations as $location) {
                $items = $menuService->getNestedItemsForLocation($location);
                if ($items->isNotEmpty()) {
                    $menu = $menuService->getMenuByLocation($location);
                    $columns[] = [
                        'title' => $menu ? $menu->name : __('Links'),
                        'items' => $items,
                        'isMenu' => true,
                    ];
                }
            }
        } catch (\Exception $e) {
            // MenuService not available
        }

        return $columns;
    }
}
