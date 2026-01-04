<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Modules\ModuleUpdateService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fallback middleware for checking module updates when cron is not configured.
 *
 * This middleware triggers an update check in the background when:
 * - The user is on an admin route
 * - The last update check was too long ago (based on config)
 * - A rate limit lock can be acquired (to prevent concurrent checks)
 *
 * The check is done after the response is sent to avoid UI delays.
 */
class CheckModuleUpdates
{
    protected ModuleUpdateService $updateService;

    public function __construct(ModuleUpdateService $updateService)
    {
        $this->updateService = $updateService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only proceed if update checking is enabled
        if (! config('laradashboard.updates.enabled', true)) {
            return $response;
        }

        // Only check on admin module-related routes
        if (! $this->shouldCheckOnRoute($request)) {
            return $response;
        }

        // Check if we should trigger a fallback update check
        if (! $this->updateService->shouldTriggerFallbackCheck()) {
            return $response;
        }

        // Try to acquire a lock to prevent concurrent checks
        $lockKey = 'module_update_check_lock';
        $lock = Cache::lock($lockKey, 120);

        if ($lock->get()) {
            // Dispatch the update check after the response is sent
            app()->terminating(function () use ($lock) {
                try {
                    $this->updateService->checkForUpdates(forceRefresh: true);
                    Log::info('Fallback module update check completed');
                } catch (\Throwable $e) {
                    Log::warning('Fallback module update check failed: ' . $e->getMessage());
                } finally {
                    $lock->release();
                }
            });
        }

        return $response;
    }

    /**
     * Determine if the update check should run on this route.
     * Only trigger on admin module-related pages.
     */
    protected function shouldCheckOnRoute(Request $request): bool
    {
        $path = $request->path();

        // Only check on admin/modules routes
        return str_starts_with($path, 'admin/modules')
            || $path === 'admin/dashboard'
            || $path === 'admin';
    }
}
