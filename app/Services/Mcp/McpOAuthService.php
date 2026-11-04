<?php

declare(strict_types=1);

namespace App\Services\Mcp;

use App\Exceptions\McpOAuthException;
use App\Models\McpOAuthAuthorizationCode;
use App\Models\McpOAuthClient;
use App\Models\McpOAuthRefreshToken;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Minimal OAuth 2.1 authorization server for remote MCP clients.
 *
 * Supports dynamic client registration (RFC 7591), the authorization code
 * grant with mandatory PKCE (S256) for public clients, and rotating refresh
 * tokens. Issued access tokens are ordinary Sanctum MCP agent tokens, so the
 * existing MCP middleware, abilities and token management keep working.
 */
class McpOAuthService
{
    public const SCOPE = 'mcp:use';

    public const AUTHORIZATION_CODE_TTL_MINUTES = 10;

    public const ACCESS_TOKEN_TTL_MINUTES = 60;

    public const REFRESH_TOKEN_TTL_DAYS = 30;

    public function __construct(
        protected McpTokenService $tokenService,
    ) {
    }

    /**
     * @param  array<int, string>  $redirectUris
     */
    public function registerClient(string $name, array $redirectUris): McpOAuthClient
    {
        return McpOAuthClient::query()->create([
            'client_id' => (string) Str::uuid(),
            'name' => $name,
            'redirect_uris' => array_values(array_unique($redirectUris)),
        ]);
    }

    public function findClient(string $clientId): ?McpOAuthClient
    {
        return McpOAuthClient::query()->where('client_id', $clientId)->first();
    }

    /**
     * Why this user may not connect MCP clients, or null when they may.
     * The same rules as creating an MCP agent token by hand, checked both at
     * consent time and on every refresh.
     */
    public function authorizationDenialReason(User $user): ?string
    {
        if ((bool) config('app.demo_mode', false)) {
            return __('MCP is unavailable in demo mode.');
        }

        if (! $user->can('manage', Setting::class)) {
            return __('You are not allowed to connect MCP clients.');
        }

        if (! $this->tokenService->hasMcpCompatibleAbilities($user)) {
            return __('Your account does not have any MCP-compatible permissions (post.view or post.create).');
        }

        return null;
    }

    /**
     * @param  array<string, string|null>  $params
     */
    public function redirectUrl(string $redirectUri, array $params): string
    {
        $query = http_build_query(array_filter($params, fn ($value) => $value !== null && $value !== ''));

        return $redirectUri.(str_contains($redirectUri, '?') ? '&' : '?').$query;
    }

    /**
     * Redirect URIs must be absolute, without a fragment, and use HTTPS unless
     * they point at the local loopback interface (native/desktop clients).
     */
    public function isAllowedRedirectUri(string $uri): bool
    {
        $parts = parse_url($uri);

        if ($parts === false || ! isset($parts['scheme'], $parts['host']) || isset($parts['fragment'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower(trim($parts['host'], '[]'));

        if ($scheme === 'https') {
            return true;
        }

        return $scheme === 'http' && in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    /**
     * @return string The plain-text authorization code (only its hash is stored).
     */
    public function issueAuthorizationCode(
        McpOAuthClient $client,
        User $user,
        string $redirectUri,
        string $codeChallenge,
    ): string {
        $code = Str::random(64);

        McpOAuthAuthorizationCode::query()->create([
            'code_hash' => $this->hash($code),
            'mcp_oauth_client_id' => $client->id,
            'user_id' => $user->id,
            'redirect_uri' => $redirectUri,
            'code_challenge' => $codeChallenge,
            'expires_at' => now()->addMinutes(self::AUTHORIZATION_CODE_TTL_MINUTES),
        ]);

        return $code;
    }

    /**
     * @return array<string, int|string>
     */
    public function exchangeAuthorizationCode(
        string $clientId,
        string $code,
        string $redirectUri,
        string $codeVerifier,
    ): array {
        $client = $this->findClient($clientId) ?? throw McpOAuthException::invalidClient();

        $authCode = McpOAuthAuthorizationCode::query()
            ->with('user')
            ->where('code_hash', $this->hash($code))
            ->first();

        if ($authCode === null) {
            throw McpOAuthException::invalidGrant('The authorization code is invalid or has already been used.');
        }

        // Single use: whoever deletes the row first wins, so a replayed code always fails.
        $consumed = McpOAuthAuthorizationCode::query()->whereKey($authCode->id)->delete() > 0;

        if (! $consumed || $authCode->expires_at->isPast()) {
            throw McpOAuthException::invalidGrant('The authorization code is invalid or has expired.');
        }

        if ($authCode->mcp_oauth_client_id !== $client->id || ! hash_equals($authCode->redirect_uri, $redirectUri)) {
            throw McpOAuthException::invalidGrant('The authorization code was issued to another client or redirect URI.');
        }

        if (! $this->verifyPkce($codeVerifier, $authCode->code_challenge)) {
            throw McpOAuthException::invalidGrant('The PKCE code verifier does not match.');
        }

        return $this->issueTokens($client, $this->usableUser($authCode->user, $client));
    }

    /**
     * Exchange a refresh token for a new access/refresh token pair (rotation).
     * Presenting an already-rotated token revokes the whole grant, since that
     * means the token leaked.
     *
     * @return array<string, int|string>
     */
    public function refresh(string $clientId, string $refreshToken): array
    {
        $client = $this->findClient($clientId) ?? throw McpOAuthException::invalidClient();

        $token = McpOAuthRefreshToken::query()
            ->with('user')
            ->where('token_hash', $this->hash($refreshToken))
            ->where('mcp_oauth_client_id', $client->id)
            ->first();

        if ($token === null) {
            throw McpOAuthException::invalidGrant('The refresh token is invalid.');
        }

        if ($token->revoked_at !== null) {
            $this->revokeGrant($client, $token->user_id);

            throw McpOAuthException::invalidGrant('The refresh token has already been used. Please authorize again.');
        }

        $rotated = McpOAuthRefreshToken::query()
            ->whereKey($token->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]) > 0;

        if (! $rotated || $token->expires_at->isPast()) {
            throw McpOAuthException::invalidGrant('The refresh token is invalid or has expired.');
        }

        // Revoked from Settings → MCP (or otherwise deleted): the grant is over.
        if ($token->personal_access_token_id !== null
            && ! PersonalAccessToken::query()->whereKey($token->personal_access_token_id)->exists()) {
            $this->revokeGrant($client, $token->user_id);

            throw McpOAuthException::invalidGrant('This connection was revoked. Please authorize again.');
        }

        $this->deleteAccessToken($token->personal_access_token_id);

        return $this->issueTokens($client, $this->usableUser($token->user, $client));
    }

    public function verifyPkce(string $codeVerifier, string $codeChallenge): bool
    {
        if (preg_match('/^[A-Za-z0-9\-._~]{43,128}$/', $codeVerifier) !== 1) {
            return false;
        }

        $expected = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        return hash_equals($codeChallenge, $expected);
    }

    /**
     * Revoke every refresh and access token this client holds for the user.
     */
    public function revokeGrant(McpOAuthClient $client, int $userId): void
    {
        DB::transaction(function () use ($client, $userId): void {
            $tokens = McpOAuthRefreshToken::query()
                ->where('mcp_oauth_client_id', $client->id)
                ->where('user_id', $userId)
                ->get();

            foreach ($tokens as $token) {
                $this->deleteAccessToken($token->personal_access_token_id);
            }

            McpOAuthRefreshToken::query()
                ->where('mcp_oauth_client_id', $client->id)
                ->where('user_id', $userId)
                ->update(['revoked_at' => now()]);
        });
    }

    /**
     * @return array<string, int|string>
     */
    protected function issueTokens(McpOAuthClient $client, User $user): array
    {
        $issued = $this->tokenService->issueToken(
            $user,
            __('OAuth · :client', ['client' => $client->name]),
            now()->addMinutes(self::ACCESS_TOKEN_TTL_MINUTES),
        );

        $refreshToken = Str::random(64);

        McpOAuthRefreshToken::query()->create([
            'token_hash' => $this->hash($refreshToken),
            'mcp_oauth_client_id' => $client->id,
            'user_id' => $user->id,
            'personal_access_token_id' => $issued['token']->id,
            'expires_at' => now()->addDays(self::REFRESH_TOKEN_TTL_DAYS),
        ]);

        return [
            'access_token' => $issued['plain_text'],
            'token_type' => 'Bearer',
            'expires_in' => self::ACCESS_TOKEN_TTL_MINUTES * 60,
            'refresh_token' => $refreshToken,
            'scope' => self::SCOPE,
        ];
    }

    /**
     * The user must still exist and still be allowed to connect MCP clients.
     */
    protected function usableUser(?User $user, McpOAuthClient $client): User
    {
        if ($user === null || $this->authorizationDenialReason($user) !== null) {
            if ($user !== null) {
                $this->revokeGrant($client, $user->id);
            }

            throw McpOAuthException::invalidGrant('This account can no longer use MCP.');
        }

        return $user;
    }

    protected function deleteAccessToken(?int $tokenId): void
    {
        if ($tokenId !== null) {
            PersonalAccessToken::query()->whereKey($tokenId)->delete();
        }
    }

    protected function hash(string $value): string
    {
        return hash('sha256', $value);
    }
}
