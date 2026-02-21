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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
            $table->string('label');
            $table->enum('type', ['page', 'post', 'category', 'tag', 'custom', 'route'])->default('custom');
            $table->string('target')->nullable();
            $table->boolean('target_blank')->default(false);
            $table->string('icon', 100)->nullable();
            $table->string('css_classes')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->json('meta')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
