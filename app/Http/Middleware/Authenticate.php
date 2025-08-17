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
                $disableRedirect = config('settings.disable_default_admin_redirect', '0') === '1';

                // If redirect is disabled, show 403.
                if ($disableRedirect) {
                    return abort(403, 'Unauthorized access');
                }

                return route('admin.login');
            }

            // For frontend routes, redirect to frontend login.
            return route('login');
        }

        return null;
    }
}
