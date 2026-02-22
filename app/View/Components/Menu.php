<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\Menu as MenuModel;
use App\Services\MenuService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Menu extends Component
{
    public ?MenuModel $menu;

    public Collection $items;

    /**
     * Create a new component instance.
     *
     * @param  string  $location  The menu location to render
     * @param  int|null  $depth  Maximum nesting depth (null for unlimited)
     * @param  string  $class  CSS classes to add to the menu container
     * @param  string  $itemClass  CSS classes to add to each menu item
     * @param  string  $linkClass  CSS classes to add to each link
     * @param  string  $activeClass  CSS class for active items
     * @param  bool  $showIcons  Whether to display icons
     */
    public function __construct(
        public string $location = 'primary',
        public ?int $depth = null,
        public string $class = '',
        public string $itemClass = '',
        public string $linkClass = '',
        public string $activeClass = 'active',
        public bool $showIcons = true,
    ) {
        $menuService = app(MenuService::class);
        $this->items = $menuService->getNestedItemsForLocation($this->location);
        $this->menu = $menuService->getMenuByLocation($this->location);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.menu');
    }

    /**
     * Check if the menu has items.
     */
    public function hasItems(): bool
    {
        return $this->items->isNotEmpty();
    }

    /**
     * Filter items based on max depth.
     */
    public function filterByDepth(Collection $items, int $currentDepth = 0): Collection
    {
        if ($this->depth !== null && $currentDepth >= $this->depth) {
            return collect();
        }

        return $items->map(function ($item) use ($currentDepth) {
            if ($item->hasChildren() && ($this->depth === null || $currentDepth + 1 < $this->depth)) {
                $item->setRelation('children', $this->filterByDepth($item->children, $currentDepth + 1));
            } else {
                $item->setRelation('children', collect());
            }

            return $item;
        });
    }
}
