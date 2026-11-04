<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single-use OAuth authorization code. Only the SHA-256 hash is stored.
 *
 * @property int $id
 * @property int $mcp_oauth_client_id
 * @property int $user_id
 * @property string $redirect_uri
 * @property string $code_challenge
 * @property \Illuminate\Support\Carbon $expires_at
 */
class McpOAuthAuthorizationCode extends Model
{
    use MassPrunable;

    protected $table = 'mcp_oauth_authorization_codes';

    protected $fillable = [
        'code_hash',
        'mcp_oauth_client_id',
        'user_id',
        'redirect_uri',
        'code_challenge',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'mcp_oauth_client_id' => 'integer',
            'user_id' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * @return Builder<McpOAuthAuthorizationCode>
     */
    public function prunable(): Builder
    {
        return static::query()->where('expires_at', '<=', now());
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
