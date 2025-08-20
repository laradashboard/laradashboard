<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Pages\User\UserCreate;
use App\Pages\User\UserEdit;
use App\Pages\User\UserList;
use App\PageGenerator\Traits\HasPageGenerator;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class PageGeneratorUsersController extends Controller
{
    use HasPageGenerator;

    public function index(Request $request): Renderable
    {
        $page = $this->makePage(UserList::class, $request);

        return $this->renderPage($page);
    }

    public function create(Request $request): Renderable
    {
        $page = $this->makePage(UserCreate::class, $request);

        return $this->renderPage($page);
    }

    public function edit(Request $request, User $user): Renderable
    {
        $page = $this->makePage(UserEdit::class, $request, $user);

        return $this->renderPage($page);
    }

    // Alternative: Direct usage without trait
    public function indexAlternative(Request $request): Renderable
    {
        return app(UserList::class, [$request]);
    }

    // Alternative: Custom view override
    public function createAlternative(Request $request): Renderable
    {
        $page = app(UserCreate::class, [$request]);

        // Override view if needed
        $page->setView('custom.users.create');

        return $page;
    }
}
