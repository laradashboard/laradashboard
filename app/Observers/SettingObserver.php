<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ActionType;
use App\Models\Setting;
use App\Concerns\HasActionLogTrait;

class SettingObserver
{
    use HasActionLogTrait;

    public function created(Setting $setting): void
    {
        $this->storeActionLog(ActionType::CREATED, ['setting' => $setting]);
    }

    public function updated(Setting $setting): void
    {
        $this->storeActionLog(ActionType::UPDATED, ['setting' => $setting]);
    }

    public function deleted(Setting $setting): void
    {
        $this->storeActionLog(ActionType::DELETED, ['setting' => $setting]);
    }
}
