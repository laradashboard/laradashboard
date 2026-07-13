<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('error_notification_logs')) {
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
        }

        $defaults = [
            'error_notifications_enabled' => '1',
            'error_notifications_email' => '',
            'error_notifications_time' => '09:00',
        ];

        $now = now();
        foreach ($defaults as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['option_name' => $key],
                [
                    'option_value' => $value,
                    'autoload' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('error_notification_logs');

        DB::table('settings')->whereIn('option_name', [
            'error_notifications_enabled',
            'error_notifications_email',
            'error_notifications_time',
        ])->delete();
    }
};
