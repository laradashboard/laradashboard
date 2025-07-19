<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;

class ScreenshotGeneratorLoginController extends Controller
{
    /**
     * Login user with email for screenshot generation
     *
     * @param string $email
     * @return \Illuminate\Http\Response
     */
    public function login($email)
    {
        // Only allow this functionality in non-production environments
        if (App::environment('production')) {
            abort(404, 'This functionality is not available in production environment');
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            Auth::login($user);
            return redirect(request()->target ? request()->target : '/admin');
        }

        return response('Login failed', 404);
    }
}
