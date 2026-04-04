<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Services\Emails\EmailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Password;

class AccountCreatedNotification extends BaseNotification
{
    use Queueable;

    /**
     * The password reset token for the user.
     */
    private string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly string $loginUrl,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Generate a password reset token so the user can set their own password.
        $this->token = Password::broker()->createToken($notifiable);

        $setPasswordUrl = config('app.url') . route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false);

        // Try to get the custom notification.
        $notification = Notification::where('notification_type', NotificationType::ACCOUNT_CREATED->value)
            ->where('is_active', true)
            ->where('is_deleteable', false)
            ->with('emailTemplate')
            ->first();

        // If custom notification exists and has a template, use it.
        if ($notification && ! empty($notification->emailTemplate)) {
            return $this->buildCustomEmail($notification, $setPasswordUrl, $notifiable);
        }

        // Fallback to default Laravel email.
        return (new MailMessage())
            ->subject(__('Your Account on :app_name', ['app_name' => config('app.name')]))
            ->greeting(__('Hello :name!', ['name' => $notifiable->full_name]))
            ->line(__('An administrator has created an account for you on :app_name.', ['app_name' => config('app.name')]))
            ->line(__('Your username is: **:username**', ['username' => $notifiable->username]))
            ->action(__('Set Your Password'), $setPasswordUrl)
            ->line(__('This link will expire in :minutes minutes.', ['minutes' => config('auth.passwords.users.expire', 60)]))
            ->line(__('You can also log in directly at: :url', ['url' => $this->loginUrl]));
    }

    /**
     * Build custom email using the custom template.
     */
    private function buildCustomEmail(Notification $notification, string $setPasswordUrl, object $notifiable): MailMessage
    {
        return (new EmailSender())
            ->setSubject($notification->emailTemplate->subject ?? __('Your Account on :app_name', ['app_name' => config('app.name')]))
            ->setContent($notification->emailTemplate->renderContent('email'))
            ->getMailMessage(
                $notification->from_email,
                [
                    'set_password_url' => $setPasswordUrl,
                    'login_url' => $this->loginUrl,
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

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
