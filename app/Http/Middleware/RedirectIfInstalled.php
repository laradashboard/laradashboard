<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfInstalled
{
    /**
     * Handle an incoming request.
     *
     * Prevents access to installation wizard if already installed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isInstallationCompleted()) {
            return redirect()->route('admin.dashboard')
                ->with('warning', __('Application is already installed.'));
        }

        return $next($request);
    }

    /**
     * Check if installation is completed.
     */
    protected function isInstallationCompleted(): bool
    {
        try {
            // Check if we can connect to database
            DB::connection()->getPdo();

            // Check if settings table exists
            if (! Schema::hasTable('settings')) {
                return false;
            }

            // Check if installation_completed setting exists and is '1'
            $setting = DB::table('settings')
                ->where('option_name', 'installation_completed')
                ->first();

            return $setting && $setting->option_value === '1';
        } catch (\Exception $e) {
            // If we can't connect to database, installation is not complete
            return false;
        }
    }
}
