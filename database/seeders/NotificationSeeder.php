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

}
