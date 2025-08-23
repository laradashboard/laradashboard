<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'settings.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Setting $setting): bool
    {
        return $this->checkPermission($user, 'settings.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'settings.edit');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ?Setting $setting = null): bool
    {
        return $this->checkPermission($user, 'settings.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Setting $setting): bool
    {
        return $this->checkPermission($user, 'settings.edit');
    }

    /**
     * Determine whether the user can manage settings.
     */
    public function manage(User $user): bool
    {
        return $this->checkPermission($user, 'settings.edit');
    }
}
