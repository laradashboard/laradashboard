<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfileEmailVerification extends Component
{
    public bool $resending = false;

    public ?string $message = null;

    public ?string $messageType = null;

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
            $this->message = __('Verification email has been sent! Please check your inbox at :email.', ['email' => $user->email]);
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = __('Failed to send verification email. Please try again later.');
            $this->messageType = 'error';
        }

        $this->resending = false;
    }

    public function render()
    {
        return view('livewire.components.profile-email-verification');
    }
}
