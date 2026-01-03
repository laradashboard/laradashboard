<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Services\Emails\EmailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class RegistrationWelcomeNotification extends BaseNotification
{
    use Queueable;

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
        // Try laradashboard dashboard first, fallback to admin dashboard or home
        $dashboardUrl = config('app.url');
        if (\Illuminate\Support\Facades\Route::has('laradashboard.dashboard.index')) {
            $dashboardUrl .= route('laradashboard.dashboard.index', [], false);
        } elseif (\Illuminate\Support\Facades\Route::has('admin.dashboard')) {
            $dashboardUrl .= route('admin.dashboard', [], false);
        } else {
            $dashboardUrl .= '/';
        }

        // Try to get the custom notification.
        $notification = Notification::where('notification_type', NotificationType::REGISTRATION_WELCOME->value)
            ->where('is_active', true)
            ->where('is_deleteable', false)
            ->with('emailTemplate')
            ->first();

        // If custom notification exists and has a template, use it.
        if ($notification && ! empty($notification->emailTemplate)) {
            return $this->buildCustomEmail($notification, $dashboardUrl, $notifiable);
        }

        // Fallback to default Laravel email.
        return (new MailMessage())
            ->subject(__('Welcome to :app_name!', ['app_name' => config('app.name')]))
            ->greeting(__('Hello :name!', ['name' => $notifiable->full_name]))
            ->line(__('Thank you for creating an account with us. We\'re excited to have you on board!'))
            ->line(__('Your account has been successfully created and you can now access all the features available to you.'))
            ->action(__('Go to Dashboard'), $dashboardUrl)
            ->line(__('If you have any questions, feel free to reach out to our support team.'));
    }

    /**
     * Build custom email using the custom template.
     */
    private function buildCustomEmail(Notification $notification, string $dashboardUrl, object $notifiable): MailMessage
    {
        return (new EmailSender())
            ->setSubject($notification->emailTemplate->subject ?? __('Welcome to :app_name!', ['app_name' => config('app.name')]))
            ->setContent($notification->emailTemplate->body_html)
            ->getMailMessage(
                $notification->from_email,
                [
                    'dashboard_url' => $dashboardUrl,
                    'registered_at' => $notifiable->created_at?->format('F j, Y') ?? now()->format('F j, Y'),

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
