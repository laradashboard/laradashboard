<?php

declare(strict_types=1);

namespace Modules\Squartup\Livewire\Admin;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('squartup::layouts.admin')]
class Dashboard extends Component
{
    public function render(): View
    {
        return view('squartup::livewire.admin.dashboard');
    }
}
