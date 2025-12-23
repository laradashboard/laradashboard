<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminRedirectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only handle exact /admin path
        if ($request->path() === 'admin') {
            // If user is authenticated, let the route handle it normally (dashboard)
            if (Auth::check()) {
                return $next($request);
            }

            // For non-authenticated users:
            $hideAdminUrl = config('settings.hide_admin_url', '0') === '1';

            // If hide admin URL is enabled, show 403
            if ($hideAdminUrl) {
                abort(403, __('Unauthorized access'));
            }

            // Redirect to the appropriate login URL
            $customLoginRoute = config('settings.custom_login_route');
            $hideDefaultLogin = config('settings.hide_default_login_url', '0') === '1';

            if ($customLoginRoute && $hideDefaultLogin) {
                return redirect()->to(url($customLoginRoute));
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
