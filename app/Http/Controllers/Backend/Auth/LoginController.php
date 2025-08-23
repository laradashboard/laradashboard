<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Services\DemoAppService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    public function __construct(private readonly DemoAppService $demoAppService)
    {
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::ADMIN_DASHBOARD;

    public function showLoginForm()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }

        $this->demoAppService->maybeSetDemoLocaleToEnByDefault();

        $email = app()->environment('local') ? 'superadmin@example.com' : '';
        $password = app()->environment('local') ? '12345678' : '';

        return view('backend.auth.login')->with(compact('email', 'password'));
    }

    public function login(LoginRequest $request): RedirectResponse|Response
    {
        $credentialsList = [
            // ['email' => $request->email, 'password' => $request->password],
            // ['username' => $request->email, 'password' => $request->password],
            ['phone' => $request->phone, 'password' => $request->password], // added phone
        ];
        foreach ($credentialsList as $credentials) {
            if (Auth::guard('web')->attempt($credentials, $request->remember)) {
                // $this->demoAppService->maybeSetDemoLocaleToEnByDefault();

                // Auto-verify phone in development
                if (isset($credentials['phone'])) {
                    $user = Auth::guard('web')->user();
                    if (!$user->phone_verified_at) {
                        $user->phone_verified_at = now();
                        $user->save();
                    }
                }

                session()->flash('success', 'Successfully Logged in!');
                return redirect()->route('admin.dashboard');
            }else {
                \Log::info('Attempt failed', $credentials);
            }
        }
        return $this->sendFailedLoginResponse($request);
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('web')->logout();

        return redirect()->route('admin.login');
    }
}
