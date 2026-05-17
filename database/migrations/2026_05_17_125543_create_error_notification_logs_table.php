<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('error_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('error_hash', 64)->unique();
            $table->string('level', 32);
            $table->text('message');
            $table->string('file')->nullable();
            $table->unsignedInteger('line')->nullable();
            $table->unsignedInteger('occurrences')->default(1);
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index('notified_at');
            $table->index('last_seen_at');
        });

        $defaults = [
            Setting::ERROR_NOTIFICATIONS_ENABLED => '1',
            Setting::ERROR_NOTIFICATIONS_EMAIL => '',
            Setting::ERROR_NOTIFICATIONS_TIME => '09:00',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(
                ['option_name' => $key],
                ['option_value' => $value, 'autoload' => true]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('error_notification_logs');

        Setting::whereIn('option_name', [
            Setting::ERROR_NOTIFICATIONS_ENABLED,
            Setting::ERROR_NOTIFICATIONS_EMAIL,
            Setting::ERROR_NOTIFICATIONS_TIME,
        ])->delete();
    }
};
