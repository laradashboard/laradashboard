<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ensure posts.design_json exists before data-fix migrations that query it.
 *
 * Older installs created posts before Lara Builder added design_json to the
 * baseline create_posts migration. Fresh installs get the column from
 * create_posts; this migration covers upgrades and partial installs.
 */
return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('posts') || Schema::hasColumn('posts', 'design_json')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->json('design_json')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('posts') || ! Schema::hasColumn('posts', 'design_json')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('design_json');
        });
    }
};
