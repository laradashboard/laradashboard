<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Menus;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\MenuService;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class Builder extends Component
{
    public Menu $menu;

    public Collection $nestedItems;

    public array $pages = [];

    public array $posts = [];

    public array $categories = [];

    public array $tags = [];

    public array $routes = [];

    // Form state
    public bool $showAddForm = false;

    public bool $showEditForm = false;

    public ?int $editingItemId = null;

    public ?int $addToParentId = null;

    // Form fields
    public string $label = '';

    public string $type = 'custom';

    public string $target = '';

    public bool $targetBlank = false;

    public string $icon = '';

    public string $cssClasses = '';

    protected MenuService $menuService;

    protected $rules = [
        'label' => 'required|string|max:255',
        'type' => 'required|in:page,post,category,tag,custom,route',
        'target' => 'nullable|string|max:255',
        'targetBlank' => 'boolean',
        'icon' => 'nullable|string|max:100',
        'cssClasses' => 'nullable|string|max:255',
    ];

    public function boot(MenuService $menuService): void
    {
        $this->menuService = $menuService;
    }

    public function mount(Menu $menu): void
    {
        $this->menu = $menu;
        $this->loadItems();
        $this->loadSelectOptions();
    }

    public function loadItems(): void
    {
        $this->nestedItems = $this->menu->getNestedItems();
    }

    public function loadSelectOptions(): void
    {
        $this->pages = $this->menuService->getAvailablePages()
            ->mapWithKeys(fn ($p) => [$p->slug => $p->title])
            ->toArray();

        $this->posts = $this->menuService->getAvailablePosts()
            ->mapWithKeys(fn ($p) => [$p->slug => $p->title])
            ->toArray();

        $this->categories = $this->menuService->getAvailableCategories()
            ->mapWithKeys(fn ($c) => [$c->slug => $c->name])
            ->toArray();

        $this->tags = $this->menuService->getAvailableTags()
            ->mapWithKeys(fn ($t) => [$t->slug => $t->name])
            ->toArray();

        $this->routes = $this->menuService->getAvailableRoutes();
    }

    public function showAddItemForm(?int $parentId = null): void
    {
        $this->resetForm();
        $this->addToParentId = $parentId;
        $this->showAddForm = true;
        $this->showEditForm = false;
    }

    public function editItem(int $itemId): void
    {
        $item = MenuItem::find($itemId);
        if (! $item) {
            return;
        }

        $this->resetForm();
        $this->editingItemId = $itemId;
        $this->label = $item->label;
        $this->type = $item->type;
        $this->target = $item->target ?? '';
        $this->targetBlank = $item->target_blank;
        $this->icon = $item->icon ?? '';
        $this->cssClasses = $item->css_classes ?? '';
        $this->showEditForm = true;
        $this->showAddForm = false;
    }

    public function saveItem(): void
    {
        $this->validate();

        $data = [
            'label' => $this->label,
            'type' => $this->type,
            'target' => $this->target ?: null,
            'target_blank' => $this->targetBlank,
            'icon' => $this->icon ?: null,
            'css_classes' => $this->cssClasses ?: null,
        ];

        if ($this->editingItemId) {
            $item = MenuItem::find($this->editingItemId);
            if ($item) {
                $this->menuService->updateMenuItem($item, $data);
                session()->flash('success', __('Menu item updated.'));
            }
        } else {
            $data['parent_id'] = $this->addToParentId;
            $this->menuService->createMenuItem($this->menu, $data);
            session()->flash('success', __('Menu item added.'));
        }

        $this->resetForm();
        $this->loadItems();
        $this->dispatch('items-updated');
    }

    public function deleteItem(int $itemId): void
    {
        $item = MenuItem::where('menu_id', $this->menu->id)->find($itemId);
        if ($item) {
            $this->menuService->deleteMenuItem($item);
            session()->flash('success', __('Menu item deleted.'));
            $this->loadItems();
            $this->dispatch('items-updated');
        }
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->showAddForm = false;
        $this->showEditForm = false;
        $this->editingItemId = null;
        $this->addToParentId = null;
        $this->label = '';
        $this->type = 'custom';
        $this->target = '';
        $this->targetBlank = false;
        $this->icon = '';
        $this->cssClasses = '';
        $this->resetValidation();
    }

    #[On('items-reordered')]
    public function handleReorder(array $items): void
    {
        $this->reorderItems($items);
    }

    /**
     * Reorder menu items from drag-drop.
     */
    public function reorderItems(array $items): void
    {
        $this->menuService->updateItemsOrder($this->menu, $items);
        $this->loadItems();
        session()->flash('success', __('Menu order updated.'));
        $this->dispatch('items-updated');
    }

    public function getTargetOptions(): array
    {
        return match ($this->type) {
            'page' => $this->pages,
            'post' => $this->posts,
            'category' => $this->categories,
            'tag' => $this->tags,
            'route' => $this->routes,
            default => [],
        };
    }

    public function updatedType(): void
    {
        // Reset target when type changes
        $this->target = '';
    }

    public function render()
    {
        return view('livewire.admin.menus.builder', [
            'itemTypes' => MenuItem::getAvailableTypes(),
            'targetOptions' => $this->getTargetOptions(),
        ]);
    }
}
