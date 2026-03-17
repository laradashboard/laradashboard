<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Post;

abstract class BaseContentPage extends BaseFrontendPage
{
    public ?Post $page = null;

    public function mount(string $slug): void
    {
        $this->page = $this->query()->findPublishedPageBySlug($slug);
    }
}
