<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Models\Notification;
use App\Enums\ReceiverType;
use App\Enums\NotificationType;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $this->createForgotPasswordNotification();
        $this->createCrmActivityNotifications();

        $this->command->info('✓ Notifications created successfully!');
    }

    private function createForgotPasswordNotification(): void
    {
        $template = EmailTemplate::where('name', 'Forgot Password')->first();

        if (! $template) {
            $this->command->warn('⚠ Forgot Password email template not found. Skipping notification creation.');
            return;
        }

        Notification::updateOrCreate(
            ['name' => 'Forgot Password Notification'],
            [
                'uuid' => Str::uuid(),
                'description' => 'Automated notification sent when a user requests password reset',
                'notification_type' => NotificationType::FORGOT_PASSWORD->value,
                'email_template_id' => $template->id,
                'receiver_type' => ReceiverType::USER->value,
                'is_active' => true,
                'is_deleteable' => false,
                'track_opens' => true,
                'track_clicks' => true,
                'created_by' => 1,
            ]
        );
    }

    private function createCrmActivityNotifications(): void
    {
        $activityCreatedTemplate = EmailTemplate::where('name', 'Activity Created')->first();
        if ($activityCreatedTemplate) {
            Notification::updateOrCreate(
                ['name' => 'Activity Created Notification'],
                [
                    'uuid' => Str::uuid(),
                    'description' => 'Notification sent when a new activity is created',
                    'notification_type' => 'activity_created',
                    'email_template_id' => $activityCreatedTemplate->id,
                    'receiver_type' => ReceiverType::USER->value,
                    'is_active' => true,
                    'is_deleteable' => true,
                    'track_opens' => true,
                    'track_clicks' => true,
                    'created_by' => 1,
                ]
            );
        }

        $activityUpdatedTemplate = EmailTemplate::where('name', 'Activity Updated')->first();
        if ($activityUpdatedTemplate) {
            Notification::updateOrCreate(
                ['name' => 'Activity Updated Notification'],
                [
                    'uuid' => Str::uuid(),
                    'description' => 'Notification sent when an activity is updated',
                    'notification_type' => 'activity_updated',
                    'email_template_id' => $activityUpdatedTemplate->id,
                    'receiver_type' => ReceiverType::USER->value,
                    'is_active' => true,
                    'is_deleteable' => true,
                    'track_opens' => true,
                    'track_clicks' => true,
                    'created_by' => 1,
                ]
            );
        }

        $activityDeletedTemplate = EmailTemplate::where('name', 'Activity Deleted')->first();
        if ($activityDeletedTemplate) {
            Notification::updateOrCreate(
                ['name' => 'Activity Deleted Notification'],
                [
                    'uuid' => Str::uuid(),
                    'description' => 'Notification sent when an activity is deleted',
                    'notification_type' => 'activity_deleted',
                    'email_template_id' => $activityDeletedTemplate->id,
                    'receiver_type' => ReceiverType::USER->value,
                    'is_active' => true,
                    'is_deleteable' => true,
                    'track_opens' => true,
                    'track_clicks' => true,
                    'created_by' => 1,
                ]
            );
        }
    }
}
