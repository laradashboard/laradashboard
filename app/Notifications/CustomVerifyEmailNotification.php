<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Services\Emails\EmailSender;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmailNotification extends VerifyEmail
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        // Try to get the custom notification.
        $notification = Notification::where('notification_type', NotificationType::EMAIL_VERIFICATION->value)
            ->where('is_active', true)
            ->where('is_deleteable', false)
            ->with('emailTemplate')
            ->first();

        // If custom notification exists and has a template, use it.
        if ($notification && ! empty($notification->emailTemplate)) {
            return $this->buildCustomEmail($notification, $verificationUrl, $notifiable);
        }

        // Fallback to default Laravel email.
        return (new MailMessage())
            ->subject(__('Verify Email Address'))
            ->line(__('Please click the button below to verify your email address.'))
            ->action(__('Verify Email Address'), $verificationUrl)
            ->line(__('If you did not create an account, no further action is required.'));
    }

    /**
     * Build custom email using the custom template.
     */
    private function buildCustomEmail(Notification $notification, string $verificationUrl, object $notifiable): MailMessage
    {
        $expirationMinutes = Config::get('auth.verification.expire', 60);

        return (new EmailSender())
            ->setSubject($notification->emailTemplate->subject ?? __('Verify Email Address'))
            ->setContent($notification->emailTemplate->body_html)
            ->getMailMessage(
                $notification->from_email,
                [
                    'verification_url' => $verificationUrl,
                    'expiry_time' => $expirationMinutes . ' minutes',

                    // Notifiable user data.
                    'first_name' => $notifiable->first_name,
                    'last_name' => $notifiable->last_name,
                    'full_name' => $notifiable->full_name,
                    'username' => $notifiable->username,
                    'email' => $notifiable->email,
                ]
            );
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
