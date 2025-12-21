<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Routes that should be excluded from the installation check.
     */
    protected array $excludedRoutes = [
        'install',
        'install/*',
        'livewire/*',
        '_debugbar/*',
    ];

    /**
     * Asset extensions that should be excluded from the installation check.
     */
    protected array $excludedExtensions = [
        'css',
        'js',
        'png',
        'jpg',
        'jpeg',
        'gif',
        'svg',
        'ico',
        'woff',
        'woff2',
        'ttf',
        'eot',
        'map',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip installation check during testing
        if (app()->runningInConsole() || app()->environment('testing')) {
            return $next($request);
        }

        // Skip if this is an asset request
        if ($this->isAssetRequest($request)) {
            return $next($request);
        }

        // Skip if this is an excluded route
        if ($this->isExcludedRoute($request)) {
            return $next($request);
        }

        // Check if installation is needed
        if ($this->needsInstallation()) {
            return redirect()->route('install.welcome');
        }

        return $next($request);
    }

    /**
     * Check if the request is for a static asset.
     */
    protected function isAssetRequest(Request $request): bool
    {
        $path = $request->path();
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return in_array(strtolower($extension), $this->excludedExtensions);
    }

    /**
     * Check if the request path is excluded from installation check.
     */
    protected function isExcludedRoute(Request $request): bool
    {
        $path = $request->path();

        foreach ($this->excludedRoutes as $pattern) {
            if ($path === $pattern || fnmatch($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the application needs installation.
     */
    protected function needsInstallation(): bool
    {
        // Check if APP_KEY exists and is valid
        if (! $this->hasValidAppKey()) {
            return true;
        }

        // Check if we can connect to the database
        if (! $this->canConnectToDatabase()) {
            return true;
        }

        // Check if installation is completed
        if (! $this->isInstallationCompleted()) {
            return true;
        }

        return false;
    }

    /**
     * Check if APP_KEY exists and is valid.
     */
    protected function hasValidAppKey(): bool
    {
        $key = config('app.key');

        if (empty($key)) {
            return false;
        }

        // Check if it's a valid base64 key
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);

            return $decoded !== false && strlen($decoded) === 32;
        }

        return strlen($key) === 32;
    }

    /**
     * Check if we can connect to the database.
     */
    protected function canConnectToDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if installation is completed by checking the settings table.
     */
    protected function isInstallationCompleted(): bool
    {
        try {
            // Check if settings table exists
            if (! Schema::hasTable('settings')) {
                return false;
            }

            // Check if installation_completed setting exists and is '1'
            $setting = DB::table('settings')
                ->where('option_name', Setting::INSTALLATION_COMPLETED)
                ->first();

            return $setting && $setting->option_value === '1';
        } catch (\Exception $e) {
            return false;
        }
    }
}
