<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ActionType;
use App\Models\User;
use App\Concerns\HasActionLogTrait;
use App\Services\UserService;

class UserObserver
{
    use HasActionLogTrait;

    public function created(User $user): void
    {
        $this->storeActionLog(ActionType::CREATED, ['user' => $user]);
        UserService::clearUserDropdownCache();
    }

    public function updated(User $user): void
    {
        $this->storeActionLog(ActionType::UPDATED, ['user' => $user]);
        UserService::clearUserDropdownCache();
    }

    public function deleted(User $user): void
    {
        $this->storeActionLog(ActionType::DELETED, ['user' => $user]);
        UserService::clearUserDropdownCache();
    }
}
