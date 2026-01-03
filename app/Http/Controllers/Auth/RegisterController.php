<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\Hooks\AuthActionHook;
use App\Enums\Hooks\AuthFilterHook;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\RegistrationWelcomeNotification;
use App\Support\Facades\Hook;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     */
    public function showRegistrationForm(): View
    {
        Hook::doAction(AuthActionHook::BEFORE_REGISTER_FORM_RENDER);

        $pageTitle = Hook::applyFilters(
            AuthFilterHook::REGISTER_PAGE_TITLE,
            config('settings.auth_register_page_title') ?: __('Create Account')
        );

        $pageDescription = Hook::applyFilters(
            AuthFilterHook::REGISTER_PAGE_DESCRIPTION,
            config('settings.auth_register_page_description') ?: __('Fill in the form below to create your account')
        );

        $showLoginLink = filter_var(
            config('settings.auth_enable_public_login', '1'),
            FILTER_VALIDATE_BOOLEAN
        );

        $viewName = Hook::applyFilters(AuthFilterHook::REGISTER_VIEW, 'auth.register');

        return view($viewName, compact(
            'pageTitle',
            'pageDescription',
            'showLoginLink'
        ));
    }

    /**
     * Get the post register redirect path.
     */
    protected function redirectTo(): string
    {
        $defaultRedirect = config('settings.auth_redirect_after_register', '/');

        return Hook::applyFilters(AuthFilterHook::REGISTER_REDIRECT_PATH, $defaultRedirect);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = Hook::applyFilters(AuthFilterHook::REGISTER_VALIDATION_RULES, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $messages = Hook::applyFilters(AuthFilterHook::REGISTER_VALIDATION_MESSAGES, []);

        return Validator::make($data, $rules, $messages);
    }

    /**
     * Create a new user instance after a valid registration.
     */
    protected function create(array $data): ?User
    {
        Hook::doAction(AuthActionHook::BEFORE_REGISTRATION, $data);

        // Generate username from email (part before @)
        $username = $this->generateUniqueUsername($data['email']);

        $userData = Hook::applyFilters(AuthFilterHook::REGISTER_USER_DATA, [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => $username,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user = User::create($userData);

        // Assign default role (Subscriber by default for public registration)
        $defaultRole = Hook::applyFilters(
            AuthFilterHook::REGISTER_DEFAULT_ROLE,
            config('settings.auth_default_user_role', 'Subscriber')
        );

        if ($defaultRole && $user) {
            try {
                $user->assignRole($defaultRole);
            } catch (\Exception $e) {
                // Role might not exist, log the error
                \Illuminate\Support\Facades\Log::warning("Could not assign role '{$defaultRole}' to new user: ".$e->getMessage());
            }
        }

        Hook::doAction(AuthActionHook::AFTER_REGISTRATION_SUCCESS, $user, $data);

        return $user;
    }

    /**
     * Handle a registration request for the application.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request): RedirectResponse|JsonResponse
    {
        $this->validator($request->all())->validate();

        try {
            $user = $this->create($request->all());

            // Send registration welcome email
            $this->sendWelcomeEmail($user);

            // Only fire Registered event (which sends verification email) if:
            // 1. Email verification is enabled in settings
            // 2. Mail is properly configured
            if ($this->shouldSendVerificationEmail()) {
                try {
                    event(new Registered($user));
                } catch (\Exception $e) {
                    // Log the error but don't fail registration
                    Log::warning('Could not send verification email: '.$e->getMessage());
                }
            }

            $this->guard()->login($user);

            if ($response = $this->registered($request, $user)) {
                return $response;
            }

            return $request->wantsJson()
                ? new JsonResponse([], 201)
                : redirect($this->redirectPath());
        } catch (\Exception $e) {
            Hook::doAction(AuthActionHook::AFTER_REGISTRATION_FAILED, $request, $e);

            throw $e;
        }
    }

    /**
     * Send the welcome email to the newly registered user.
     */
    protected function sendWelcomeEmail(User $user): void
    {
        // Check if mail is properly configured
        $mailFrom = config('mail.from.address');
        if (empty($mailFrom)) {
            return;
        }

        try {
            $user->notify(new RegistrationWelcomeNotification());
        } catch (\Exception $e) {
            // Log the error but don't fail registration
            Log::warning('Could not send welcome email: '.$e->getMessage());
        }
    }

    /**
     * Check if verification email should be sent.
     */
    protected function shouldSendVerificationEmail(): bool
    {
        // Check if email verification is enabled
        $verificationEnabled = filter_var(
            config('settings.auth_enable_email_verification', '0'),
            FILTER_VALIDATE_BOOLEAN
        );

        if (! $verificationEnabled) {
            return false;
        }

        // Check if mail is properly configured
        $mailFrom = config('mail.from.address');

        return ! empty($mailFrom);
    }

    /**
     * Generate a unique username from email.
     */
    protected function generateUniqueUsername(string $email): string
    {
        // Get the part before @ from email
        $baseUsername = strtolower(explode('@', $email)[0]);

        // Remove any special characters, keep only alphanumeric and underscores
        $baseUsername = preg_replace('/[^a-z0-9_]/', '', $baseUsername);

        // Ensure it's not empty
        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }

        // Check if username exists, if so append a number
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.$counter;
            $counter++;
        }

        return $username;
    }
}
