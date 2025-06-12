<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->string('meta_key');
            $table->longText('meta_value')->nullable();
            $table->string('type')->default('input'); // input, textarea, select, number, email, url, etc.
            $table->text('default_value')->nullable();
            $table->timestamps();

            $table->index(['post_id', 'meta_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_meta');
    }
};
