<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HideDefaultLoginMiddleware
{
    /**
     * Handle an incoming request.
     *
     * If the user has configured a custom login URL and enabled hiding the default login URL,
     * this middleware will return a 404 for the default /login path.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if we should hide the default login URL
        $hideDefaultLogin = config('settings.hide_default_login_url', '0') === '1';
        $customLoginRoute = config('settings.custom_login_route');

        // Only hide if both conditions are met:
        // 1. hide_default_login_url is enabled
        // 2. a custom login route is configured
        if ($hideDefaultLogin && ! empty($customLoginRoute)) {
            // Check if this is the default login route
            if ($request->path() === 'login') {
                abort(404);
            }
        }

        return $next($request);
    }
}
