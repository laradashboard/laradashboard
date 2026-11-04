<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\McpOAuthClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A remote MCP client registered through OAuth dynamic client registration.
 *
 * @property int $id
 * @property string $client_id
 * @property string $name
 * @property array<int, string> $redirect_uris
 */
class McpOAuthClient extends Model
{
    /** @use HasFactory<McpOAuthClientFactory> */
    use HasFactory;

    protected $table = 'mcp_oauth_clients';

    protected $fillable = [
        'client_id',
        'name',
        'redirect_uris',
    ];

    protected function casts(): array
    {
        return [
            'redirect_uris' => 'array',
        ];
    }

    /**
     * Exact match, except that loopback redirects may use any port
     * (OAuth 2.1 §8.4.2, RFC 8252 §7.3): desktop clients pick a free one.
     */
    public function hasRedirectUri(string $redirectUri): bool
    {
        $registered = $this->redirect_uris ?? [];

        if (in_array($redirectUri, $registered, true)) {
            return true;
        }

        $candidate = self::loopbackWithoutPort($redirectUri);

        return $candidate !== null && in_array($candidate, array_map(self::loopbackWithoutPort(...), $registered), true);
    }

    /**
     * The URI with its port removed, or null when it isn't an http loopback URI.
     */
    protected static function loopbackWithoutPort(string $uri): ?string
    {
        $parts = parse_url($uri);

        if ($parts === false || strtolower($parts['scheme'] ?? '') !== 'http') {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');

        if (! in_array(trim($host, '[]'), ['127.0.0.1', '::1', 'localhost'], true)) {
            return null;
        }

        return 'http://'.$host.($parts['path'] ?? '').(isset($parts['query']) ? '?'.$parts['query'] : '');
    }

    /**
     * @return HasMany<McpOAuthAuthorizationCode, $this>
     */
    public function authorizationCodes(): HasMany
    {
        return $this->hasMany(McpOAuthAuthorizationCode::class, 'mcp_oauth_client_id');
    }

    /**
     * @return HasMany<McpOAuthRefreshToken, $this>
     */
    public function refreshTokens(): HasMany
    {
        return $this->hasMany(McpOAuthRefreshToken::class, 'mcp_oauth_client_id');
    }
}
