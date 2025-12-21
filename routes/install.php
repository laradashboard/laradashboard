<?php

use App\Livewire\Install\InstallWizard;
use App\Models\User;
use App\Services\InstallationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Installation Routes
|--------------------------------------------------------------------------
|
| These routes handle the WordPress-like installation wizard for first-time
| setup of the application. They are protected by the RedirectIfInstalled
| middleware to prevent access after installation is complete.
|
*/

Route::middleware(['web', 'redirect.if.installed'])
    ->prefix('install')
    ->name('install.')
    ->group(function () {
        Route::get('/', InstallWizard::class)->name('welcome');

        // Complete installation and auto-login (uses regular POST to avoid session issues)
        Route::post('/complete', function () {
            $adminUserId = request()->input('admin_user_id');

            // Complete the installation
            app(InstallationService::class)->completeInstallation();

            // Clear wizard session data
            session()->forget('install_wizard_data');

            // Auto-login the admin user
            if ($adminUserId) {
                $user = User::find($adminUserId);
                if ($user) {
                    Auth::login($user);
                }
            }

            // Redirect to dashboard
            return redirect()->route('admin.dashboard');
        })->name('complete');
    });
