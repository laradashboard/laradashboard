<?php

namespace App\Services\MenuService;

use App\Services\MenuService\AdminMenuItem;
use Illuminate\Support\Facades\Route;

class SidebarMenuService
{
    protected $groups = [];

    /**
     * Add a menu item to the sidebar.
     *
     * @param AdminMenuItem|array $item The menu item or configuration array
     * @param string|null $group The group to add the item to
     * @return void
     * @throws \InvalidArgumentException
     */
    public function addMenuItem($item, $group = null)
    {
        $group = $group ?: __('Main');
        $menuItem = $this->createAdminMenuItem($item);
        if (!isset($this->groups[$group])) {
            $this->groups[$group] = [];
        }

        if ($menuItem->userHasPermission()) {
            $this->groups[$group][] = $menuItem;
        }
    }

    /**
     * Create an AdminMenuItem from an array.
     *
     * @param array $data Configuration array
     * @return AdminMenuItem
     */
    protected function createAdminMenuItem(array $data): AdminMenuItem
    {
        $menuItem = new AdminMenuItem();
        if (isset($data['children']) && is_array($data['children'])) {
            $children = [];
            foreach ($data['children'] as $child) {
                $children[] = $this->createAdminMenuItem($child);
            }
            $data['children'] = $children;
        }

        return $menuItem->setAttributes($data);
    }

    public function getMenu()
    {
        $this->addMenuItem([
            'label' => __('Dashboard'),
            'icon' => 'dashboard.svg',
            'route' => route('admin.dashboard'),
            'active' => Route::is('admin.dashboard'),
            'id' => 'dashboard',
            'priority' => 1,
            'permissions' => 'dashboard.view'
        ]);

        $this->addMenuItem([
            'label' => __('Roles & Permissions'),
            'icon' => 'key.svg',
            'id' => 'roles-submenu',
            'active' => Route::is('admin.roles.*'),
            'priority' => 10,
            'permissions' => ['role.create', 'role.view', 'role.edit', 'role.delete'],
            'children' => [
                [
                    'label' => __('Roles'),
                    'route' => route('admin.roles.index'),
                    'active' => Route::is('admin.roles.index') || Route::is('admin.roles.edit'),
                    'priority' => 10,
                    'permissions' => 'role.view'
                ],
                [
                    'label' => __('New Role'),
                    'route' => route('admin.roles.create'),
                    'active' => Route::is('admin.roles.create'),
                    'priority' => 20,
                    'permissions' => 'role.create'
                ],
                [
                    'label' => __('Permissions'),
                    'route' => route('admin.permissions.index'),
                    'active' => Route::is('admin.permissions.index') || Route::is('admin.permissions.show'),
                    'priority' => 30,
                    'permissions' => 'role.view'
                ]
            ]
        ]);

        $this->addMenuItem([
            'label' => __('User'),
            'icon' => 'user.svg',
            'id' => 'users-submenu',
            'active' => Route::is('admin.users.*'),
            'priority' => 20,
            'permissions' => ['user.create', 'user.view', 'user.edit', 'user.delete'],
            'children' => [
                [
                    'label' => __('Users'),
                    'route' => route('admin.users.index'),
                    'active' => Route::is('admin.users.index') || Route::is('admin.users.edit'),
                    'priority' => 20,
                    'permissions' => 'user.view'
                ],
                [
                    'label' => __('New User'),
                    'route' => route('admin.users.create'),
                    'active' => Route::is('admin.users.create'),
                    'priority' => 10,
                    'permissions' => 'user.create'
                ]
            ]
        ]);

        $this->addMenuItem([
            'label' => __('Modules'),
            'icon' => 'three-dice.svg',
            'route' => route('admin.modules.index'),
            'active' => Route::is('admin.modules.index'),
            'id' => 'modules',
            'priority' => 30,
            'permissions' => 'module.view'
        ]);

        $this->addMenuItem([
            'label' => __('Monitoring'),
            'icon' => 'tv.svg',
            'id' => 'monitoring-submenu',
            'active' => Route::is('admin.actionlog.*'),
            'priority' => 40,
            'permissions' => ['pulse.view', 'actionlog.view'],
            'children' => [
                [
                    'label' => __('Action Logs'),
                    'route' => route('admin.actionlog.index'),
                    'active' => Route::is('admin.actionlog.index'),
                    'priority' => 20,
                    'permissions' => 'actionlog.view'
                ],
                [
                    'label' => __('Laravel Pulse'),
                    'route' => route('pulse'),
                    'active' => false,
                    'target' => '_blank',
                    'priority' => 10,
                    'permissions' => 'pulse.view'
                ]
            ]
        ]);

        $this->addMenuItem([
            'label' => __('Settings'),
            'icon' => 'settings.svg',
            'id' => 'settings-submenu',
            'active' => Route::is('admin.settings.*') || Route::is('admin.translations.*'),
            'priority' => 1,
            'permissions' => ['settings.edit', 'translations.view'],
            'children' => [
                [
                    'label' => __('General Settings'),
                    'route' => route('admin.settings.index'),
                    'active' => Route::is('admin.settings.index'),
                    'priority' => 20,
                    'permissions' => 'settings.edit'
                ],
                [
                    'label' => __('Translations'),
                    'route' => route('admin.translations.index'),
                    'active' => Route::is('admin.translations.*'),
                    'priority' => 10,
                    'permissions' => ['translations.view', 'translations.edit']
                ]
            ]
        ], __('More'));

        $this->addMenuItem([
            'label' => __('Logout'),
            'icon' => 'logout.svg',
            'route' => route('admin.dashboard'),
            'active' => false,
            'id' => 'logout',
            'priority' => 1,
            'html' => '
                <li class="hover:menu-item-active">
                    <form method="POST" action="' . route('logout') . '">
                        ' . csrf_field() . '
                        <button type="submit" class="menu-item group w-full text-left menu-item-inactive text-black dark:text-white hover:text-black">
                            <img src="' . asset('images/icons/logout.svg') . '" alt="Logout" class="menu-item-icon dark:invert">
                            <span class="menu-item-text">' . __('Logout') . '</span>
                        </button>
                    </form>
                </li>
            '
        ], __('More'));

        $this->sortMenuItemsByPriority();
        $result = $this->applyFiltersToMenuItems();

        return $result;
    }

    protected function sortMenuItemsByPriority()
    {
        foreach ($this->groups as &$groupItems) {
            usort($groupItems, function ($a, $b) {
                return $a->toArray()['priority'] <=> $b->toArray()['priority'];
            });
        }
    }

    protected function applyFiltersToMenuItems()
    {
        $result = [];
        foreach ($this->groups as $group => $items) {
            $menuArr = array_map(function ($item) {
                return $item->toArray();
            }, $items);
            $result[$group] = ld_apply_filters('sidebar_menu_' . strtolower($group), $menuArr);
        }

        return $result;
    }

    public function render($groupItems, $textColorVar = 'textColor', $submenusVar = 'submenus')
    {
        $html = '';
        foreach ($groupItems as $groupItem) {
            // Filter before menu
            $filterKey = $groupItem['id'] ?? (\Str::slug($groupItem['label']) ?? '');
            $html .= ld_apply_filters('sidebar_menu_before_' . $filterKey, '');
            if (isset($groupItem['htmlData'])) {
                $html .= $groupItem['htmlData'];
            } else if (!empty($groupItem['children'])) {
                $submenuId = $groupItem['id'] ?? \Str::slug($groupItem['label']) . '-submenu';
                $isActive = $groupItem['active'] ? 'menu-item-active' : 'menu-item-inactive';
                $html .= '<li x-data class="hover:menu-item-active">';
                $html .= '<button :style="`color: ${' . $textColorVar . '}`" class="menu-item group w-full text-left ' . $isActive . '" type="button" @click="toggleSubmenu(\'' . $submenuId . '\')">';
                if (!empty($groupItem['icon'])) {
                    $html .= '<img src="' . asset('images/icons/' . $groupItem['icon']) . '" alt="' . e($groupItem['label']) . '" class="menu-item-icon dark:invert">';
                }
                $html .= '<span class="menu-item-text">' . e($groupItem['label']) . '</span>';
                $html .= '<img src="' . asset('images/icons/chevron-down.svg') . '" alt="Arrow" class="menu-item-arrow dark:invert transition-transform duration-300" :class="' . $submenusVar . '[\'' . $submenuId . '\'] ? \'rotate-180\' : \'\'">';
                $html .= '</button>';
                $html .= '<ul id="' . $submenuId . '" x-show="' . $submenusVar . '[\'' . $submenuId . '\']"
                        x-transition:enter="transition-all ease-in-out duration-300"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-[500px]"
                        x-transition:leave="transition-all ease-in-out duration-300"
                        x-transition:leave-start="opacity-100 max-h-[500px]"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="submenu pl-12 mt-2 space-y-2 overflow-hidden">';
                $html .= $this->render($groupItem['children'], $textColorVar, $submenusVar);
                $html .= '</ul>';
                $html .= '</li>';
            } else {
                $isActive = $groupItem['active'] ? 'menu-item-active' : 'menu-item-inactive';
                $target = !empty($groupItem['target']) ? ' target="' . e($groupItem['target']) . '"' : '';
                $html .= '<li class="hover:menu-item-active">';
                $html .= '<a :style="`color: ${' . $textColorVar . '}`" href="' . ($groupItem['route'] ?? '#') . '" class="menu-item group ' . $isActive . '"' . $target . '>';
                if (!empty($groupItem['icon'])) {
                    $html .= '<img src="' . asset('images/icons/' . $groupItem['icon']) . '" alt="' . e($groupItem['label']) . '" class="menu-item-icon dark:invert">';
                }
                $html .= '<span class="menu-item-text">' . e($groupItem['label']) . '</span>';
                $html .= '</a>';
                $html .= '</li>';
            }

            // Filter after menu
            $html .= ld_apply_filters('sidebar_menu_after_' . $filterKey, '');
        }
        return $html;
    }
}
