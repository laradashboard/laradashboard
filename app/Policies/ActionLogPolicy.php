<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ActionLog;
use App\Models\User;

class ActionLogPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'actionlog.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ActionLog $actionLog): bool
    {
        return $this->checkPermission($user, 'actionlog.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'actionlog.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ActionLog $actionLog): bool
    {
        return $this->checkPermission($user, 'actionlog.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ActionLog $actionLog): bool
    {
        return $this->checkPermission($user, 'actionlog.delete');
    }
}
