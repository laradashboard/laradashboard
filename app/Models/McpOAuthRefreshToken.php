<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A rotating OAuth refresh token. Only the SHA-256 hash is stored.
 *
 * @property int $id
 * @property int $mcp_oauth_client_id
 * @property int $user_id
 * @property int|null $personal_access_token_id
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $revoked_at
 */
class McpOAuthRefreshToken extends Model
{
    use MassPrunable;

    protected $table = 'mcp_oauth_refresh_tokens';

    protected $fillable = [
        'token_hash',
        'mcp_oauth_client_id',
        'user_id',
        'personal_access_token_id',
        'expires_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'mcp_oauth_client_id' => 'integer',
            'user_id' => 'integer',
            'personal_access_token_id' => 'integer',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * Keep revoked rows for a week so refresh-token reuse can still be detected.
     *
     * @return Builder<McpOAuthRefreshToken>
     */
    public function prunable(): Builder
    {
        return static::query()
            ->where('expires_at', '<=', now())
            ->orWhere('revoked_at', '<=', now()->subDays(7));
    }

    /**
     * @return BelongsTo<McpOAuthClient, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(McpOAuthClient::class, 'mcp_oauth_client_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
