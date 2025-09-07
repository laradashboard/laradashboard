<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ActionType;
use App\Models\Media;
use App\Concerns\HasActionLogTrait;

class MediaObserver
{
    use HasActionLogTrait;

    public function created(Media $media): void
    {
        $this->storeActionLog(ActionType::CREATED, ['media' => $media]);
    }

    public function updated(Media $media): void
    {
        $this->storeActionLog(ActionType::UPDATED, ['media' => $media]);
    }

    public function deleted(Media $media): void
    {
        $this->storeActionLog(ActionType::DELETED, ['media' => $media]);
    }
}
