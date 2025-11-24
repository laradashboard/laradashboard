<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Notification;
use App\Models\NotificationType;
use App\Services\Emails\EmailManager;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class AdminResetPasswordNotification extends BaseResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url(route('admin.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // Try to get the custom notification.
        $notification = Notification::where('notification_type', NotificationType::FORGOT_PASSWORD)
            ->where('is_active', true)
            ->where('is_deleteable', false)
            ->with('emailTemplate')
            ->first();

        // If custom notification exists and has a template, use it.
        if ($notification && !empty($notification->emailTemplate)) {
            return $this->buildCustomEmail($notification, $url);
        }

        // Fallback to default Laravel email.
        return (new MailMessage())
            ->subject(__('Reset Password Notification'))
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->action(__('Reset Password'), $url)
            ->line(__('If you did not request a password reset, no further action is required.'));
    }

    /**
     * Build custom email from template.
     */
    private function buildCustomEmail($notification, $url)
    {
        return app(EmailManager::class)
            ->sendEmail(
                !empty($notification->subject) ? $notification->subject : $notification->emailTemplate->subject,
                !empty($notification->body_html) ? $notification->body_html : $notification->emailTemplate->body_html,
                $notification->from_email,
                [
                    'reset_url' => $url,
                    'reset_token' => $this->token,
                    'expiry_time' => config('auth.passwords.users.expire', 60) . ' minutes'
                ]
            );
    }
}
