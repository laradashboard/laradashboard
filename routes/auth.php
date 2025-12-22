<?php

declare(strict_types=1);

/**
 * Auth controller imports.
 */
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
|
| Authentication related routes. These routes handle login, registration,
| password reset, and email verification.
|
| Login is always available. Registration and password reset features
| can be controlled via settings.
|
*/

// Guest routes (not logged in)
Route::group(['middleware' => 'guest'], function () {
    // Login Routes - Always available
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])
        ->middleware(['recaptcha:login', 'throttle:20,1']);

    // Registration Routes - Controlled by auth_enable_public_registration setting
    Route::middleware(['public.auth:register'])->group(function () {
        Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [RegisterController::class, 'register'])
            ->middleware(['recaptcha:registration', 'throttle:20,1']);
    });

    // Password Reset Routes - Controlled by auth_enable_password_reset setting
    Route::middleware(['public.auth:password_reset'])->group(function () {
        Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
            ->middleware(['recaptcha:forgot_password', 'throttle:20,1'])->name('password.email');
        Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('password/reset', [ResetPasswordController::class, 'reset'])
            ->middleware('throttle:20,1')->name('password.update');
    });
});

// Email Verification Routes (requires auth, not guest)
Route::middleware('auth')->group(function () {
    Route::get('email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('email/resend', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');
});

// Logout Route (requires auth)
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
