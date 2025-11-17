<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Models\Notification;
use App\Enums\ReceiverType;
use App\Models\NotificationType;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createForgotPasswordNotification();
        $this->createActivityNotifications();

        $this->command->info('✓ Notifications created successfully!');
    }

    /**
     * Create Forgot Password Notification
     */
    private function createForgotPasswordNotification(): void
    {
        // Get the Forgot Password email template
        $template = EmailTemplate::where('name', 'Forgot Password')->first();

        if (! $template) {
            $this->command->warn('⚠ Forgot Password email template not found. Skipping notification creation.');
            return;
        }

        // Create Forgot Password Notification
        Notification::updateOrCreate(
            ['name' => 'Forgot Password Notification'],
            [
                'uuid' => Str::uuid(),
                'description' => 'Automated notification sent when a user requests password reset',
                'notification_type' => NotificationType::FORGOT_PASSWORD,
                'email_template_id' => $template->id,
                'receiver_type' => ReceiverType::USER,
                'is_active' => true,
                'is_deleteable' => false,
                'track_opens' => true,
                'track_clicks' => true,
                'from_email' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'created_by' => 1,
            ]
        );
    }

    /**
     * Create Activity Management Notifications
     */
    private function createActivityNotifications(): void
    {
        // Get the Activity email templates
        $activityCreatedTemplate = EmailTemplate::where('name', 'Activity Created')->first();
        $activityUpdatedTemplate = EmailTemplate::where('name', 'Activity Updated')->first();
        $activityDeletedTemplate = EmailTemplate::where('name', 'Activity Deleted')->first();

        // Activity Created Notification
        Notification::updateOrCreate(
            ['name' => 'Activity Created Notification'],
            [
                'uuid' => Str::uuid(),
                'description' => 'Notification sent when a new activity is created',
                'notification_type' => NotificationType::ACTIVITY_CREATED,
                'email_template_id' => $activityCreatedTemplate?->id,
                'receiver_type' => ReceiverType::USER,
                'is_active' => true,
                'is_deleteable' => false,
                'track_opens' => true,
                'track_clicks' => true,
                'from_email' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'created_by' => 1,
            ]
        );

        // Activity Updated Notification
        Notification::updateOrCreate(
            ['name' => 'Activity Updated Notification'],
            [
                'uuid' => Str::uuid(),
                'description' => 'Notification sent when an activity is updated',
                'notification_type' => NotificationType::ACTIVITY_UPDATED,
                'email_template_id' => $activityUpdatedTemplate?->id,
                'receiver_type' => ReceiverType::USER,
                'is_active' => true,
                'is_deleteable' => false,
                'track_opens' => true,
                'track_clicks' => true,
                'from_email' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'created_by' => 1,
            ]
        );

        // Activity Deleted Notification
        Notification::updateOrCreate(
            ['name' => 'Activity Deleted Notification'],
            [
                'uuid' => Str::uuid(),
                'description' => 'Notification sent when an activity is deleted',
                'notification_type' => NotificationType::ACTIVITY_DELETED,
                'email_template_id' => $activityDeletedTemplate?->id,
                'receiver_type' => ReceiverType::USER,
                'is_active' => true,
                'is_deleteable' => false,
                'track_opens' => true,
                'track_clicks' => true,
                'from_email' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'created_by' => 1,
            ]
        );
    }
}
