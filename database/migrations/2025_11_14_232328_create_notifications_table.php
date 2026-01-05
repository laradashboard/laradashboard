<?php

declare(strict_types=1);

use App\Database\Concerns\TogglesForeignKeyConstraints;
use App\Enums\NotificationType;
use App\Enums\ReceiverType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
    use TogglesForeignKeyConstraints;
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('notification_type');
            $table->foreignId('email_template_id')->constrained('email_templates')->onDelete('cascade');
            $table->string('receiver_type');
            $table->json('receiver_ids')->nullable();
            $table->json('receiver_emails')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleteable')->default(true);
            $table->boolean('track_opens')->default(true);
            $table->boolean('track_clicks')->default(true);
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->string('reply_to_name')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('notification_type');
            $table->index('receiver_type');
            $table->index('is_active');
        });

        // Seed essential notifications
        $this->seedEssentialNotifications();
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }

    /**
     * Seed essential notifications required for core application functionality.
     */
    private function seedEssentialNotifications(): void
    {
        // Temporarily disable foreign key checks for seeding (no users exist yet during migration)
        $this->disableForeignKeyChecks();

        // Forgot Password Notification
        $forgotPasswordTemplate = DB::table('email_templates')->where('name', 'Forgot Password')->first();
        if ($forgotPasswordTemplate) {
            DB::table('notifications')->insert([
                'uuid' => (string) Str::uuid(),
                'name' => 'Forgot Password Notification',
                'description' => 'Automated notification sent when a user requests password reset',
                'notification_type' => NotificationType::FORGOT_PASSWORD->value,
                'email_template_id' => $forgotPasswordTemplate->id,
                'receiver_type' => ReceiverType::USER->value,
                'receiver_ids' => null,
                'receiver_emails' => null,
                'is_active' => true,
                'is_deleteable' => false,
                'track_opens' => true,
                'track_clicks' => true,
                'from_email' => null,
                'from_name' => null,
                'reply_to_email' => null,
                'reply_to_name' => null,
                'settings' => null,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Registration Welcome Notification
        $registrationTemplate = DB::table('email_templates')->where('name', 'Registration Welcome')->first();
        if ($registrationTemplate) {
            DB::table('notifications')->insert([
                'uuid' => (string) Str::uuid(),
                'name' => 'Registration Welcome Notification',
                'description' => 'Welcome email sent to new users after successful registration.',
                'notification_type' => NotificationType::REGISTRATION_WELCOME->value,
                'email_template_id' => $registrationTemplate->id,
                'receiver_type' => ReceiverType::USER->value,
                'receiver_ids' => json_encode([]),
                'receiver_emails' => json_encode([]),
                'is_active' => true,
                'is_deleteable' => false,
                'track_opens' => true,
                'track_clicks' => true,
                'from_email' => null,
                'from_name' => null,
                'reply_to_email' => null,
                'reply_to_name' => null,
                'settings' => json_encode([]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Email Verification Notification
        $verificationTemplate = DB::table('email_templates')->where('name', 'Email Verification')->first();
        if ($verificationTemplate) {
            DB::table('notifications')->insert([
                'uuid' => (string) Str::uuid(),
                'name' => 'Email Verification Notification',
                'description' => 'Email sent to users to verify their email address.',
                'notification_type' => NotificationType::EMAIL_VERIFICATION->value,
                'email_template_id' => $verificationTemplate->id,
                'receiver_type' => ReceiverType::USER->value,
                'receiver_ids' => json_encode([]),
                'receiver_emails' => json_encode([]),
                'is_active' => true,
                'is_deleteable' => false,
                'track_opens' => true,
                'track_clicks' => true,
                'from_email' => null,
                'from_name' => null,
                'reply_to_email' => null,
                'reply_to_name' => null,
                'settings' => json_encode([]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->enableForeignKeyChecks();
    }
};
