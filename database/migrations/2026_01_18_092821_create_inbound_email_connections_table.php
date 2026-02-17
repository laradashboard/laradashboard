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
        if (Schema::hasTable('inbound_email_connections')) {
            return;
        }

        Schema::create('inbound_email_connections', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name');

            // IMAP Connection Settings
            $table->string('imap_host');
            $table->integer('imap_port')->default(993);
            $table->string('imap_encryption')->default('ssl'); // ssl, tls, none
            $table->string('imap_username');
            $table->text('imap_password'); // encrypted via model cast
            $table->string('imap_folder')->default('INBOX');
            $table->boolean('imap_validate_cert')->default(true);

            // Processing Settings
            $table->boolean('delete_after_processing')->default(false);
            $table->boolean('mark_as_read')->default(true);
            $table->integer('fetch_limit')->default(50); // emails per batch
            $table->integer('polling_interval')->default(5); // minutes

            // Link to outbound email connection (optional)
            $table->foreignId('email_connection_id')
                ->nullable()
                ->constrained('email_connections')
                ->nullOnDelete();

            // Status & Testing
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_checked_at')->nullable();
            $table->string('last_check_status')->nullable(); // success, failed
            $table->text('last_check_message')->nullable();
            $table->integer('emails_processed_count')->default(0);

            // Audit
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('is_active');
            $table->index('last_checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_email_connections');
    }
};
