<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ActionType;
use App\Models\PostMeta;
use App\Concerns\HasActionLogTrait;

class PostMetaObserver
{
    use HasActionLogTrait;

    public function created(PostMeta $postMeta): void
    {
        $this->storeActionLog(ActionType::CREATED, ['postMeta' => $postMeta]);
    }

    public function updated(PostMeta $postMeta): void
    {
        $this->storeActionLog(ActionType::UPDATED, ['postMeta' => $postMeta]);
    }

    public function deleted(PostMeta $postMeta): void
    {
        $this->storeActionLog(ActionType::DELETED, ['postMeta' => $postMeta]);
    }
}
