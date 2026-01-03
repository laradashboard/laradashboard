<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EmailVerificationBanner extends Component
{
    public bool $dismissed = false;

    public bool $resending = false;

    public ?string $message = null;

    public ?string $messageType = null;

    /**
     * Check if email verification is enabled in settings.
     */
    public function isEmailVerificationEnabled(): bool
    {
        return filter_var(
            config('settings.auth_enable_email_verification', '0'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * Check if current user needs email verification.
     */
    public function needsVerification(): bool
    {
        if (! $this->isEmailVerificationEnabled()) {
            return false;
        }

        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail();
    }

    /**
     * Dismiss the banner for this session.
     */
    public function dismiss(): void
    {
        $this->dismissed = true;
        session(['email_verification_banner_dismissed' => true]);
    }

    /**
     * Resend verification email.
     */
    public function resendVerification(): void
    {
        $this->resending = true;
        $this->message = null;
        $this->messageType = null;

        $user = Auth::user();

        if (! $user) {
            $this->message = __('You must be logged in to resend verification email.');
            $this->messageType = 'error';
            $this->resending = false;

            return;
        }

        if ($user->hasVerifiedEmail()) {
            $this->message = __('Your email is already verified.');
            $this->messageType = 'success';
            $this->resending = false;

            return;
        }

        try {
            $user->sendEmailVerificationNotification();
            $this->message = __('Verification email has been sent! Please check your inbox.');
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = __('Failed to send verification email. Please try again later.');
            $this->messageType = 'error';
        }

        $this->resending = false;
    }

    /**
     * Check if banner was previously dismissed in session.
     */
    public function mount(): void
    {
        $this->dismissed = session('email_verification_banner_dismissed', false);
    }

    public function render()
    {
        return view('livewire.components.email-verification-banner', [
            'showBanner' => $this->needsVerification() && ! $this->dismissed,
        ]);
    }
}
