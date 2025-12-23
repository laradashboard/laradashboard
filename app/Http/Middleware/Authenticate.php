<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // If the request does not expect JSON, redirect to the appropriate login page.
        if (! $request->expectsJson()) {
            // Check if the request is for an admin route
            if ($request->is('admin/*') || $request->is('admin')) {
                $hideAdminUrl = config('settings.hide_admin_url', '0') === '1';

                // If hide admin URL is enabled, show 403.
                if ($hideAdminUrl) {
                    return abort(403, 'Unauthorized access');
                }

                return route('admin.login');
            }

            // For frontend routes, redirect to the appropriate login.
            // Use custom login route if configured and default is hidden
            $customLoginRoute = config('settings.custom_login_route');
            $hideDefaultLogin = config('settings.hide_default_login_url', '0') === '1';

            if ($customLoginRoute && $hideDefaultLogin) {
                return url($customLoginRoute);
            }

            return route('login');
        }

        return null;
    }
}
