<?php

declare(strict_types=1);

namespace Modules\Squartup\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Squartup\Livewire\Admin\Dashboard;

class LivewireServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        //
    }

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        // Admin Livewire components
        Livewire::component('squartup::admin.dashboard', Dashboard::class);
    }
}
