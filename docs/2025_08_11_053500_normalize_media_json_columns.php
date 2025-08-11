<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        // Normalize JSON columns that may have been saved as quoted strings like "[]" instead of []
        DB::table('media')
            ->select(['id', 'manipulations', 'custom_properties', 'generated_conversions', 'responsive_images'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $updates = [];

                    foreach ([
                        'manipulations',
                        'custom_properties',
                        'generated_conversions',
                        'responsive_images',
                    ] as $field) {
                        $value = $row->{$field};

                        // Value might be a JSON string (e.g., "\"[]\"") or already an array/object
                        if (is_string($value)) {
                            $decoded = json_decode($value, true);

                            // If decoded is a string again (e.g., '[]'), replace with proper empty array
                            if (is_string($decoded)) {
                                // Treat the two common cases '[]' and '{}' as empty array/object respectively
                                if (trim($decoded) === '[]') {
                                    $updates[$field] = json_encode([]);
                                } elseif (trim($decoded) === '{}') {
                                    $updates[$field] = json_encode(new stdClass());
                                }
                            }

                            // If decoded is null but original is '[]' or '{}' as plain text (no extra quotes), also fix
                            if ($decoded === null && in_array(trim($value), ['[]', '{}'], true)) {
                                $updates[$field] = json_encode(trim($value) === '{}' ? new stdClass() : []);
                            }
                        }
                    }

                    if (! empty($updates)) {
                        DB::table('media')->where('id', $row->id)->update($updates);
                    }
                }
            });
    }

    public function down(): void
    {
        // No-op: normalization is safe and idempotent
    }
};
