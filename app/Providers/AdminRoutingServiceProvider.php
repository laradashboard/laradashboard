<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AdminRoutingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerAdminAuthRedirects();
    }

    /**
     * Register redirects from old admin auth routes to the unified auth routes.
     *
     * This ensures backward compatibility for any links or bookmarks
     * pointing to /admin/login, /admin/password/reset, etc.
     */
    protected function registerAdminAuthRedirects(): void
    {
        Route::middleware(['web'])->group(function () {
            // Redirect old admin login routes to unified /login
            Route::get('admin/login', fn () => redirect()->route('login'))->name('admin.login');
            Route::post('admin/login', fn () => redirect()->route('login'))->name('admin.login.submit');

            // Redirect old admin password reset routes
            Route::get('admin/password/reset', fn () => redirect()->route('password.request'))->name('admin.password.request');
            Route::post('admin/password/email', fn () => redirect()->route('password.request'))->name('admin.password.email');
            Route::get('admin/password/reset/{token}', fn ($token) => redirect()->route('password.reset', ['token' => $token]))->name('admin.password.reset');
            Route::post('admin/password/reset', fn () => redirect()->route('password.request'))->name('admin.password.reset.submit');

            // Redirect old admin logout route
            Route::post('admin/logout/submit', fn () => redirect()->route('logout'))->name('admin.logout.submit');
        });
    }
}
