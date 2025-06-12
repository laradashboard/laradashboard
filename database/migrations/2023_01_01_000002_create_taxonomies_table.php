<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('taxonomies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('label_singular');
            $table->string('description')->nullable();
            $table->boolean('show_featured_image')->default(false);
            $table->boolean('public')->default(true);
            $table->boolean('hierarchical')->default(false);
            $table->boolean('show_in_menu')->default(true);
            $table->json('post_types')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxonomies');
    }
};
