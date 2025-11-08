<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Hooks\SettingFilterHook;
use App\Support\Facades\Hook;
use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}