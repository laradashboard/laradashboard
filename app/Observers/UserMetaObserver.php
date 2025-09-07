<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ActionType;
use App\Models\UserMeta;
use App\Concerns\HasActionLogTrait;

class UserMetaObserver
{
    use HasActionLogTrait;

    public function created(UserMeta $userMeta): void
    {
        $this->storeActionLog(ActionType::CREATED, ['userMeta' => $userMeta]);
    }

    public function updated(UserMeta $userMeta): void
    {
        $this->storeActionLog(ActionType::UPDATED, ['userMeta' => $userMeta]);
    }

    public function deleted(UserMeta $userMeta): void
    {
        $this->storeActionLog(ActionType::DELETED, ['userMeta' => $userMeta]);
    }
}
