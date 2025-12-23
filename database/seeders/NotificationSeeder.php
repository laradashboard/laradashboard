<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\NotificationType;
use App\Enums\ReceiverType;
use App\Models\EmailTemplate;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $created = 0;
        $skipped = 0;

        // Forgot Password Notification (essential - created by migration)
        $existing = Notification::where('name', 'Forgot Password Notification')->first();
        if ($existing) {
            $skipped++;
        } else {
            $template = EmailTemplate::where('name', 'Forgot Password')->first();
            if ($template) {
                Notification::create([
                    'uuid' => Str::uuid(),
                    'name' => 'Forgot Password Notification',
                    'description' => 'Automated notification sent when a user requests password reset',
                    'notification_type' => NotificationType::FORGOT_PASSWORD->value,
                    'email_template_id' => $template->id,
                    'receiver_type' => ReceiverType::USER->value,
                    'is_active' => true,
                    'is_deleteable' => false,
                    'created_by' => 1,
                ]);
                $created++;
            }
        }

        if ($created > 0) {
            $this->command->info("✓ Created {$created} notification(s).");
        }
        if ($skipped > 0) {
            $this->command->info("→ Skipped {$skipped} notification(s) (already exist from migration).");
        }
    }
}
