<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Notification;
use App\Models\NotificationType;
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

        // Try to get the custom notification
        $notification = Notification::where('notification_type', NotificationType::FORGOT_PASSWORD)
            ->where('is_active', true)
            ->with('emailTemplate')
            ->first();

        // If custom notification exists and has a template, use it
        if ($notification && $notification->emailTemplate) {
            return $this->buildCustomEmail($notification, $notifiable, $url);
        }

        // Fallback to default Laravel email
        return (new MailMessage())
            ->subject(__('Reset Password Notification'))
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->action(__('Reset Password'), $url)
            ->line(__('If you did not request a password reset, no further action is required.'));
    }

    /**
     * Build custom email from template.
     */
    private function buildCustomEmail($notification, $notifiable, $url)
    {
        $template = $notification->emailTemplate;

        // Prepare template variables
        $data = [
            'app_name' => config('app.name'),
            'user_name' => $notifiable->first_name ?? $notifiable->name ?? $notifiable->email,
            'reset_url' => $url,
            'reset_token' => $this->token,
            'expiry_time' => config('auth.passwords.users.expire', 60) . ' minutes',
        ];

        // Replace variables in subject.
        $subject = $template->subject;
        foreach ($data as $key => $value) {
            $subject = str_replace('{' . $key . '}', $value, $subject);
        }

        // Replace variables in body.
        $bodyHtml = ! empty($notification->body_html) ? $notification->body_html : $template->body_html;
        foreach ($data as $key => $value) {
            $bodyHtml = str_replace('{' . $key . '}', $value, $bodyHtml);
        }

        // Build mail message with custom content.
        $mailMessage = (new MailMessage())
            ->subject($subject)
            ->view('emails.custom-html', ['content' => $bodyHtml]);

        // Set from email and name if specified in notification.
        if ($notification->from_email) {
            $mailMessage->from($notification->from_email, $notification->from_name ?? config('app.name'));
        }

        return $mailMessage;
    }
}
