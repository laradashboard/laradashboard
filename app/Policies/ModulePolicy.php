<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Module;
use App\Models\User;

class ModulePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'module.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Module $module): bool
    {
        return $this->checkPermission($user, 'module.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'module.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Module $module): bool
    {
        return $this->checkPermission($user, 'module.activate');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Module $module): bool
    {
        return $this->checkPermission($user, 'module.deactivate');
    }

    /**
     * Determine whether the user can activate the module.
     */
    public function activate(User $user, Module $module): bool
    {
        return $this->checkPermission($user, 'module.activate');
    }

    /**
     * Determine whether the user can deactivate the module.
     */
    public function deactivate(User $user, Module $module): bool
    {
        return $this->checkPermission($user, 'module.deactivate');
    }
}
