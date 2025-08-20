<?php

declare(strict_types=1);

namespace App\PageGenerator\Traits;

use App\PageGenerator\Contracts\PageContract;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Response;

trait HasPageGenerator
{
    protected function renderPage(PageContract $page): Response|Renderable
    {
        if ($page instanceof Renderable) {
            return response($page->render());
        }

        return response($page->render());
    }

    protected function makePage(string $pageClass, ...$parameters): PageContract
    {
        return app($pageClass, $parameters);
    }
}
