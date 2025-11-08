<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\EmailStatus;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('email');
            $table->enum('status', EmailStatus::getValues())->default(EmailStatus::PENDING);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('tracking_data')->nullable(); // Open/click tracking data
            $table->integer('open_count')->default(0);
            $table->integer('click_count')->default(0);
            $table->foreign('campaign_id')->references('id')->on('email_campaigns')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['campaign_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaign_recipients');
    }
};