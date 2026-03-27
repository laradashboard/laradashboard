<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Post;

abstract class BaseHomePage extends BaseFrontendPage
{
    public ?Post $page = null;

    public function mount(): void
    {
        $this->page = $this->query()->findHomepage();
    }
}
