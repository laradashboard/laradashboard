<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OAuth 2.1 storage for remote MCP clients (e.g. Claude.ai custom connectors).
 *
 * Access tokens are regular Sanctum MCP agent tokens (personal_access_tokens),
 * so these tables only hold registered clients, short-lived authorization
 * codes and rotating refresh tokens.
 */
return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('mcp_oauth_clients')) {
            Schema::create('mcp_oauth_clients', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->unique();
                $table->string('name');
                $table->json('redirect_uris');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mcp_oauth_authorization_codes')) {
            Schema::create('mcp_oauth_authorization_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code_hash', 64)->unique();
                $table->foreignId('mcp_oauth_client_id')->constrained('mcp_oauth_clients')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('redirect_uri');
                $table->string('code_challenge', 128);
                $table->timestamp('expires_at');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mcp_oauth_refresh_tokens')) {
            Schema::create('mcp_oauth_refresh_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('token_hash', 64)->unique();
                $table->foreignId('mcp_oauth_client_id')->constrained('mcp_oauth_clients')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('personal_access_token_id')->nullable()->index();
                $table->timestamp('expires_at');
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();

                $table->index(['mcp_oauth_client_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mcp_oauth_refresh_tokens');
        Schema::dropIfExists('mcp_oauth_authorization_codes');
        Schema::dropIfExists('mcp_oauth_clients');
    }
};
