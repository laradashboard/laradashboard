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
        if (Schema::hasTable('inbound_emails')) {
            return;
        }

        Schema::create('inbound_emails', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();

            // Connection Reference
            $table->foreignId('inbound_email_connection_id')
                ->constrained('inbound_email_connections')
                ->cascadeOnDelete();

            // Email Identifiers (for threading)
            $table->string('message_id')->nullable(); // Message-ID header
            $table->string('in_reply_to')->nullable(); // In-Reply-To header
            $table->text('references')->nullable(); // References header (can be long)

            // Email Headers
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('to_email')->nullable();
            $table->string('to_name')->nullable();
            $table->text('cc')->nullable(); // JSON array of cc addresses
            $table->string('subject')->nullable();
            $table->timestamp('email_date')->nullable(); // Date from email header

            // Email Content
            $table->longText('body_plain')->nullable(); // Plain text body
            $table->longText('body_html')->nullable(); // HTML body
            $table->longText('body_parsed')->nullable(); // Parsed reply content (stripped quotes)
            $table->json('attachments')->nullable(); // Attachment metadata

            // Processing Status
            $table->string('status')->default('pending'); // pending, processing, processed, failed
            $table->text('processing_error')->nullable();
            $table->timestamp('processed_at')->nullable();

            // Handler Information (which module/handler processed this)
            $table->string('handler_type')->nullable(); // e.g., 'crm.ticket'
            $table->string('handler_model_type')->nullable(); // e.g., 'Modules\Crm\Models\TicketReply'
            $table->unsignedBigInteger('handler_model_id')->nullable(); // ID of created model

            // Raw Storage
            $table->longText('raw_headers')->nullable(); // Original headers
            $table->string('imap_uid')->nullable(); // IMAP UID for reference

            $table->timestamps();

            // Indexes for efficient lookups
            $table->index('message_id');
            $table->index('in_reply_to');
            $table->index('from_email');
            $table->index('status');
            $table->index(['handler_type', 'status']);
            $table->index('email_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_emails');
    }
};
