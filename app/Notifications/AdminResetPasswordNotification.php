<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Notification;
use App\Models\NotificationType;
use App\Services\Emails\EmailSender;
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
        if ($notification && ! empty($notification->emailTemplate)) {
            return $this->buildCustomEmail($notification, $url, $notifiable);
        }

        // Fallback to default Laravel email.
        return (new MailMessage())
            ->subject(__('Reset Password Notification'))
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->action(__('Reset Password'), $url)
            ->line(__('If you did not request a password reset, no further action is required.'));
    }

    private function buildCustomEmail($notification, $url, $notifiable): MailMessage
    {
        return (new EmailSender(
            $notification->emailTemplate->subject ?? __('Reset Password Notification'),
            $notification->emailTemplate->body_html ?? __('You are receiving this email because we received a password reset request for your account. Please click the button below to reset your password.'),
        ))
            ->getMailMessage(
                $notification->from_email,
                [
                    'reset_url' => $url,
                    'reset_token' => $this->token,
                    'expiry_time' => config('auth.passwords.users.expire', 60) . ' minutes',

                    // Notifiable user data.
                    'first_name' => $notifiable->first_name,
                    'last_name' => $notifiable->last_name,
                    'full_name' => $notifiable->full_name,
                    'username' => $notifiable->username,
                    'email' => $notifiable->email,
                ]
            );
    }
}
