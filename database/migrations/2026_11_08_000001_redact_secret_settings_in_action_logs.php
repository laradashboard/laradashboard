<?php

declare(strict_types=1);

use App\Support\Settings\SensitiveSettingValue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Before v1.5.2 every setting change was written to the action log with its raw value,
 * including API keys, passwords and OAuth client secrets. Mask those values in existing
 * rows. (SettingObserver masks new entries.) Query builder only, see CLAUDE.md.
 */
return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('action_logs')) {
            return;
        }

        DB::table('action_logs')
            ->where('data', 'like', '%option_name%')
            ->orderBy('id')
            ->chunkById(200, function ($logs): void {
                foreach ($logs as $log) {
                    $data = json_decode((string) $log->data, true);
                    $setting = is_array($data) ? ($data['setting'] ?? null) : null;

                    if (! is_array($setting) || ! isset($setting['option_name'])) {
                        continue;
                    }

                    $value = $setting['option_value'] ?? null;

                    if (! SensitiveSettingValue::isSensitive((string) $setting['option_name']) || blank($value) || $value === SensitiveSettingValue::MASK) {
                        continue;
                    }

                    $data['setting']['option_value'] = SensitiveSettingValue::MASK;

                    DB::table('action_logs')->where('id', $log->id)->update(['data' => json_encode($data)]);
                }
            });
    }

    public function down(): void
    {
        // Redacted values can't be restored.
    }
};
