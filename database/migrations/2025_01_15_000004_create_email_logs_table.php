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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('subject');
            $table->longText('body_html')->nullable();
            $table->json('headers')->nullable();
            $table->string('message_id')->nullable(); // Email service provider message ID
            $table->string('status');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('tracking_data')->nullable();
            $table->string('provider')->nullable(); // Email service provider (e.g., SendGrid, Mailgun)
            $table->json('provider_response')->nullable();
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->foreign('template_id')->references('id')->on('email_templates')->onDelete('set null');
            $table->foreign('sent_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['to_email', 'status']);
            $table->index(['sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
