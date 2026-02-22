<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;

class MenuPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'menu.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Menu $menu): bool
    {
        return $this->checkPermission($user, 'menu.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'menu.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Menu $menu): bool
    {
        return $this->checkPermission($user, 'menu.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Menu $menu): bool
    {
        return $this->checkPermission($user, 'menu.delete');
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->checkPermission($user, 'menu.delete');
    }

    /**
     * Determine whether the user can manage menu items.
     */
    public function manageItems(User $user, Menu $menu): bool
    {
        return $this->checkPermission($user, 'menu.edit');
    }
}
