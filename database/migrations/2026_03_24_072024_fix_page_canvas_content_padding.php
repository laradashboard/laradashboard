<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix contentPadding for full-width pages.
 *
 * Full-width pages (width: 100%) use section blocks that handle their own
 * padding, so the canvas contentPadding should be 0px to avoid double padding.
 */
return new class () extends Migration {
    public function up(): void
    {
        DB::table('posts')
            ->whereNotNull('design_json')
            ->where('post_type', 'page')
            ->orderBy('id')
            ->each(function ($post) {
                $designJson = is_string($post->design_json)
                    ? json_decode($post->design_json, true)
                    : $post->design_json;

                if (! is_array($designJson)) {
                    return;
                }

                $width = $designJson['canvasSettings']['width'] ?? null;
                $padding = $designJson['canvasSettings']['contentPadding'] ?? null;

                if ($width === '100%' && $padding === '24px') {
                    $designJson['canvasSettings']['contentPadding'] = '0px';

                    DB::table('posts')
                        ->where('id', $post->id)
                        ->update(['design_json' => json_encode($designJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
                }
            });
    }

    public function down(): void
    {
        DB::table('posts')
            ->whereNotNull('design_json')
            ->where('post_type', 'page')
            ->orderBy('id')
            ->each(function ($post) {
                $designJson = is_string($post->design_json)
                    ? json_decode($post->design_json, true)
                    : $post->design_json;

                if (! is_array($designJson)) {
                    return;
                }

                $width = $designJson['canvasSettings']['width'] ?? null;
                $padding = $designJson['canvasSettings']['contentPadding'] ?? null;

                if ($width === '100%' && $padding === '0px') {
                    $designJson['canvasSettings']['contentPadding'] = '24px';

                    DB::table('posts')
                        ->where('id', $post->id)
                        ->update(['design_json' => json_encode($designJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
                }
            });
    }
};
