<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\MenuService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        private readonly MenuService $menuService
    ) {
    }

    /**
     * Display a listing of menus.
     */
    public function index(): Renderable
    {
        $this->authorize('viewAny', Menu::class);

        $this->setBreadcrumbTitle(__('Menus'))
            ->setBreadcrumbIcon('lucide:menu')
            ->setBreadcrumbActionButton(
                route('admin.menus.create'),
                __('New Menu'),
                'feather:plus',
                'menu.create'
            );

        $menus = Menu::withCount('items')->orderBy('name')->get();
        $locations = Menu::getAvailableLocations();

        return $this->renderViewWithBreadcrumbs('backend.pages.menus.index', [
            'menus' => $menus,
            'locations' => $locations,
        ]);
    }

    /**
     * Show the form for creating a new menu.
     */
    public function create(): Renderable
    {
        $this->authorize('create', Menu::class);

        $this->setBreadcrumbTitle(__('New Menu'))
            ->setBreadcrumbIcon('lucide:menu')
            ->addBreadcrumbItem(__('Menus'), route('admin.menus.index'));

        $locations = Menu::getAvailableLocations();
        $usedLocations = Menu::pluck('location')->toArray();

        return $this->renderViewWithBreadcrumbs('backend.pages.menus.create', [
            'locations' => $locations,
            'usedLocations' => $usedLocations,
        ]);
    }

    /**
     * Store a newly created menu.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Menu::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:100|unique:menus,location',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $menu = $this->menuService->createMenu($validated);

        session()->flash('success', __('Menu has been created.'));

        return redirect()->route('admin.menus.builder', $menu->id);
    }

    /**
     * Show the menu builder interface.
     */
    public function builder(int $id): Renderable
    {
        $menu = Menu::with(['items' => fn ($q) => $q->orderBy('position')])->findOrFail($id);

        $this->authorize('update', $menu);

        $this->setBreadcrumbTitle(__('Edit Menu: :name', ['name' => $menu->name]))
            ->setBreadcrumbIcon('lucide:menu')
            ->addBreadcrumbItem(__('Menus'), route('admin.menus.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.menus.builder', [
            'menu' => $menu,
            'nestedItems' => $menu->getNestedItems(),
            'locations' => Menu::getAvailableLocations(),
            'itemTypes' => MenuItem::getAvailableTypes(),
            'pages' => $this->menuService->getAvailablePages(),
            'posts' => $this->menuService->getAvailablePosts(),
            'categories' => $this->menuService->getAvailableCategories(),
            'tags' => $this->menuService->getAvailableTags(),
            'routes' => $this->menuService->getAvailableRoutes(),
        ]);
    }

    /**
     * Update menu settings.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $menu = Menu::findOrFail($id);

        $this->authorize('update', $menu);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:100|unique:menus,location,' . $menu->id,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $this->menuService->updateMenu($menu, $validated);

        session()->flash('success', __('Menu has been updated.'));

        return redirect()->route('admin.menus.builder', $menu->id);
    }

    /**
     * Delete a menu.
     */
    public function destroy(int $id): RedirectResponse
    {
        $menu = Menu::findOrFail($id);

        $this->authorize('delete', $menu);

        $this->menuService->deleteMenu($menu);

        session()->flash('success', __('Menu has been deleted.'));

        return redirect()->route('admin.menus.index');
    }

    /**
     * Add a menu item via AJAX.
     */
    public function addItem(Request $request, int $menuId): JsonResponse
    {
        $menu = Menu::findOrFail($menuId);

        $this->authorize('update', $menu);

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:menu_items,id',
            'label' => 'required|string|max:255',
            'type' => 'required|in:page,post,category,tag,custom,route',
            'target' => 'nullable|string|max:255',
            'target_blank' => 'boolean',
            'icon' => 'nullable|string|max:100',
            'css_classes' => 'nullable|string|max:255',
        ]);

        $item = $this->menuService->createMenuItem($menu, $validated);

        return response()->json([
            'success' => true,
            'message' => __('Menu item added.'),
            'item' => $item,
        ]);
    }

    /**
     * Update a menu item via AJAX.
     */
    public function updateItem(Request $request, int $menuId, int $itemId): JsonResponse
    {
        $menu = Menu::findOrFail($menuId);
        $item = MenuItem::where('menu_id', $menuId)->findOrFail($itemId);

        $this->authorize('update', $menu);

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:menu_items,id',
            'label' => 'required|string|max:255',
            'type' => 'required|in:page,post,category,tag,custom,route',
            'target' => 'nullable|string|max:255',
            'target_blank' => 'boolean',
            'icon' => 'nullable|string|max:100',
            'css_classes' => 'nullable|string|max:255',
        ]);

        $this->menuService->updateMenuItem($item, $validated);

        return response()->json([
            'success' => true,
            'message' => __('Menu item updated.'),
            'item' => $item->fresh(),
        ]);
    }

    /**
     * Delete a menu item via AJAX.
     */
    public function deleteItem(int $menuId, int $itemId): JsonResponse
    {
        $menu = Menu::findOrFail($menuId);
        $item = MenuItem::where('menu_id', $menuId)->findOrFail($itemId);

        $this->authorize('update', $menu);

        $this->menuService->deleteMenuItem($item);

        return response()->json([
            'success' => true,
            'message' => __('Menu item deleted.'),
        ]);
    }

    /**
     * Reorder menu items via AJAX.
     */
    public function reorderItems(Request $request, int $menuId): JsonResponse
    {
        $menu = Menu::findOrFail($menuId);

        $this->authorize('update', $menu);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.parent_id' => 'nullable|exists:menu_items,id',
            'items.*.position' => 'required|integer|min:0',
        ]);

        $this->menuService->updateItemsOrder($menu, $validated['items']);

        return response()->json([
            'success' => true,
            'message' => __('Menu order updated.'),
        ]);
    }

    /**
     * Duplicate a menu.
     */
    public function duplicate(Request $request, int $id): RedirectResponse
    {
        $menu = Menu::findOrFail($id);

        $this->authorize('create', Menu::class);

        $validated = $request->validate([
            'location' => 'required|string|max:100|unique:menus,location',
        ]);

        $newMenu = $this->menuService->duplicateMenu($menu, $validated['location']);

        session()->flash('success', __('Menu has been duplicated.'));

        return redirect()->route('admin.menus.builder', $newMenu->id);
    }
}
