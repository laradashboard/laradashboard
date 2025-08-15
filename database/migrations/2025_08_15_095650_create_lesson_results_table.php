<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lesson_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_course_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('status');
            $table->text('teacher_note')->nullable();
            $table->date('course_date');
            $table->dateTime('update_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_results');
    }
};
