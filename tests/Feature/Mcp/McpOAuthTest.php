<?php

declare(strict_types=1);

use App\Models\McpOAuthClient;
use App\Models\McpOAuthRefreshToken;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\Mcp\McpOAuthService;
use App\Services\Mcp\McpTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

const OAUTH_REDIRECT = 'https://claude.ai/api/mcp/auth_callback';
const OAUTH_VERIFIER = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk-test-verifier';

beforeEach(function () {
    foreach (['post.view', 'post.create', 'post.edit', 'settings.edit', 'settings.view'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $this->user->assignRole(Role::firstOrCreate(['name' => Role::ADMIN, 'guard_name' => 'web']));
    $this->user->syncPermissions(['post.view', 'post.create', 'post.edit', 'settings.edit', 'settings.view']);

    Setting::query()->updateOrCreate(
        ['option_name' => Setting::MCP_ENABLED],
        ['option_value' => '1', 'autoload' => true]
    );
    config(['settings.'.Setting::MCP_ENABLED => '1']);

    $this->client = McpOAuthClient::factory()->create(['name' => 'Claude', 'redirect_uris' => [OAUTH_REDIRECT]]);
});

function oauthChallenge(string $verifier = OAUTH_VERIFIER): string
{
    return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
}

/**
 * @return array<string, string>
 */
function authorizeParams(McpOAuthClient $client, array $overrides = []): array
{
    return array_merge([
        'response_type' => 'code',
        'client_id' => $client->client_id,
        'redirect_uri' => OAUTH_REDIRECT,
        'code_challenge' => oauthChallenge(),
        'code_challenge_method' => 'S256',
        'state' => 'xyz-state',
        'scope' => 'mcp:use',
    ], $overrides);
}

function approveAndGetCode(object $test): string
{
    $response = $test->actingAs($test->user)
        ->post(route('mcp.oauth.authorize.store'), authorizeParams($test->client, ['decision' => 'approve']));

    $response->assertRedirect();
    parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);

    expect($query['state'])->toBe('xyz-state')
        ->and($query['iss'])->toBe(url('/'));

    return $query['code'];
}

function exchangeCode(object $test, string $code, string $verifier = OAUTH_VERIFIER): \Illuminate\Testing\TestResponse
{
    return $test->post(route('mcp.oauth.token'), [
        'grant_type' => 'authorization_code',
        'client_id' => $test->client->client_id,
        'code' => $code,
        'redirect_uri' => OAUTH_REDIRECT,
        'code_verifier' => $verifier,
    ]);
}

function mcpInitialize(object $test, string $accessToken): \Illuminate\Testing\TestResponse
{
    return $test->withHeaders([
        'Authorization' => 'Bearer '.$accessToken,
        'Accept' => 'application/json, text/event-stream',
    ])->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-06-18',
            'capabilities' => new stdClass(),
            'clientInfo' => ['name' => 'test', 'version' => '1.0'],
        ],
    ]);
}

test('discovery documents are published when mcp is enabled', function () {
    $this->getJson('/.well-known/oauth-protected-resource/mcp')
        ->assertOk()
        ->assertJsonPath('resource', url('/mcp'))
        ->assertJsonPath('authorization_servers.0', url('/'));

    $this->getJson('/.well-known/oauth-authorization-server')
        ->assertOk()
        ->assertJsonPath('authorization_endpoint', route('mcp.oauth.authorize'))
        ->assertJsonPath('token_endpoint', route('mcp.oauth.token'))
        ->assertJsonPath('registration_endpoint', route('mcp.oauth.register'))
        ->assertJsonPath('code_challenge_methods_supported', ['S256']);
});

test('only the mcp endpoint is advertised as a protected resource', function () {
    $this->getJson('/.well-known/oauth-protected-resource')->assertOk()->assertJsonPath('resource', url('/mcp'));
    $this->getJson('/.well-known/oauth-protected-resource/admin')->assertNotFound();
});

test('discovery documents are hidden when mcp is disabled', function () {
    config(['settings.'.Setting::MCP_ENABLED => '0']);

    $this->getJson('/.well-known/oauth-protected-resource/mcp')->assertNotFound();
    $this->getJson('/.well-known/oauth-authorization-server')->assertNotFound();
});

test('unauthenticated mcp requests point clients to the resource metadata', function () {
    $response = $this->withHeaders(['Accept' => 'application/json, text/event-stream'])
        ->postJson('/mcp', []);

    $response->assertUnauthorized();
    expect($response->headers->get('WWW-Authenticate'))
        ->toContain('resource_metadata="'.url('/.well-known/oauth-protected-resource/mcp').'"');
});

test('clients can register dynamically', function () {
    $this->postJson(route('mcp.oauth.register'), [
        'client_name' => 'Claude',
        'redirect_uris' => [OAUTH_REDIRECT],
        'token_endpoint_auth_method' => 'none',
        'grant_types' => ['authorization_code', 'refresh_token'],
        'response_types' => ['code'],
    ])
        ->assertCreated()
        ->assertJsonPath('client_name', 'Claude')
        ->assertJsonPath('redirect_uris', [OAUTH_REDIRECT])
        ->assertJsonStructure(['client_id']);
});

test('registration rejects insecure redirect uris', function (string $uri) {
    $this->postJson(route('mcp.oauth.register'), ['redirect_uris' => [$uri]])
        ->assertStatus(400)
        ->assertJsonPath('error', 'invalid_redirect_uri');
})->with([
    'plain http' => 'http://evil.example.com/callback',
    'fragment' => 'https://example.com/callback#frag',
    'relative' => '/callback',
]);

test('registration overrides unsupported client metadata instead of failing', function () {
    $this->postJson(route('mcp.oauth.register'), [
        'redirect_uris' => [OAUTH_REDIRECT],
        'token_endpoint_auth_method' => 'client_secret_post',
        'grant_types' => ['authorization_code', 'client_credentials'],
    ])
        ->assertCreated()
        ->assertJsonPath('token_endpoint_auth_method', 'none')
        ->assertJsonPath('grant_types', ['authorization_code', 'refresh_token']);
});

test('registration allows loopback http for desktop clients', function () {
    $this->postJson(route('mcp.oauth.register'), ['redirect_uris' => ['http://127.0.0.1:33418/callback']])
        ->assertCreated();
});

test('authorization requires a signed-in user', function () {
    $this->get(route('mcp.oauth.authorize', authorizeParams($this->client)))
        ->assertRedirect(route('login'));
});

test('authorization shows the consent screen', function () {
    $this->actingAs($this->user)
        ->get(route('mcp.oauth.authorize', authorizeParams($this->client)))
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertSee('Connect Claude?')
        ->assertSee('claude.ai')
        ->assertSee('mcp:posts.read');
});

test('authorization rejects a redirect uri the client did not register', function () {
    $this->actingAs($this->user)
        ->get(route('mcp.oauth.authorize', authorizeParams($this->client, ['redirect_uri' => 'https://evil.example.com/cb'])))
        ->assertStatus(400);
});

test('authorization errors after the redirect uri is verified go back to the client', function () {
    $response = $this->actingAs($this->user)
        ->get(route('mcp.oauth.authorize', authorizeParams($this->client, ['code_challenge_method' => 'plain'])));

    expect((string) $response->headers->get('Location'))->toStartWith(OAUTH_REDIRECT.'?')
        ->toContain('error=invalid_request')
        ->toContain('state=xyz-state');
});

test('authorization rejects an unknown client without redirecting', function () {
    $this->actingAs($this->user)
        ->get(route('mcp.oauth.authorize', authorizeParams($this->client, ['client_id' => 'nope'])))
        ->assertStatus(400);
});

test('loopback redirect uris match on any port', function () {
    $client = McpOAuthClient::factory()->create(['redirect_uris' => ['http://127.0.0.1:33418/callback']]);

    expect($client->hasRedirectUri('http://127.0.0.1:51000/callback'))->toBeTrue()
        ->and($client->hasRedirectUri('http://127.0.0.1:51000/other'))->toBeFalse()
        ->and($this->client->hasRedirectUri('https://claude.ai:8443/api/mcp/auth_callback'))->toBeFalse();
});

test('users without mcp permissions are sent back with access_denied', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('mcp.oauth.authorize', authorizeParams($this->client)));

    expect((string) $response->headers->get('Location'))->toStartWith(OAUTH_REDIRECT.'?')
        ->toContain('error=access_denied')
        ->not->toContain('code=');
});

test('denying redirects back with access_denied', function () {
    $response = $this->actingAs($this->user)
        ->post(route('mcp.oauth.authorize.store'), authorizeParams($this->client, ['decision' => 'deny']));

    $location = (string) $response->headers->get('Location');
    expect($location)->toStartWith(OAUTH_REDIRECT.'?')
        ->toContain('error=access_denied')
        ->toContain('state=xyz-state')
        ->not->toContain('code=');
});

test('the full flow issues an mcp agent token that works on the mcp endpoint', function () {
    $code = approveAndGetCode($this);

    $tokens = exchangeCode($this, $code)
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('expires_in', McpOAuthService::ACCESS_TOKEN_TTL_MINUTES * 60)
        ->json();

    $stored = PersonalAccessToken::findToken($tokens['access_token']);
    expect($stored)->not->toBeNull()
        ->and(app(McpTokenService::class)->isMcpToken($stored))->toBeTrue()
        ->and($stored->expires_at)->not->toBeNull()
        ->and($stored->can('mcp:posts.read'))->toBeTrue();

    auth()->forgetGuards();
    mcpInitialize($this, $tokens['access_token'])->assertOk();
});

test('authorization codes are single use', function () {
    $code = approveAndGetCode($this);

    exchangeCode($this, $code)->assertOk();
    exchangeCode($this, $code)->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
});

test('a wrong pkce verifier is rejected', function () {
    $code = approveAndGetCode($this);

    exchangeCode($this, $code, str_repeat('a', 43))
        ->assertStatus(400)
        ->assertJsonPath('error', 'invalid_grant');
});

test('expired authorization codes are rejected', function () {
    $code = approveAndGetCode($this);

    $this->travel(McpOAuthService::AUTHORIZATION_CODE_TTL_MINUTES + 1)->minutes();

    exchangeCode($this, $code)->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
});

test('unknown grant types are rejected', function () {
    $this->post(route('mcp.oauth.token'), ['grant_type' => 'password', 'client_id' => $this->client->client_id])
        ->assertStatus(400)
        ->assertJsonPath('error', 'unsupported_grant_type');
});

test('refresh tokens rotate and revoke the previous access token', function () {
    $first = exchangeCode($this, approveAndGetCode($this))->json();

    $second = $this->post(route('mcp.oauth.token'), [
        'grant_type' => 'refresh_token',
        'client_id' => $this->client->client_id,
        'refresh_token' => $first['refresh_token'],
    ])->assertOk()->json();

    expect($second['refresh_token'])->not->toBe($first['refresh_token'])
        ->and(PersonalAccessToken::findToken($first['access_token']))->toBeNull()
        ->and(PersonalAccessToken::findToken($second['access_token']))->not->toBeNull();
});

test('reusing a rotated refresh token revokes the whole grant', function () {
    $first = exchangeCode($this, approveAndGetCode($this))->json();

    $refresh = fn (string $token) => $this->post(route('mcp.oauth.token'), [
        'grant_type' => 'refresh_token',
        'client_id' => $this->client->client_id,
        'refresh_token' => $token,
    ]);

    $second = $refresh($first['refresh_token'])->assertOk()->json();

    $refresh($first['refresh_token'])->assertStatus(400)->assertJsonPath('error', 'invalid_grant');

    expect(PersonalAccessToken::findToken($second['access_token']))->toBeNull();
    $refresh($second['refresh_token'])->assertStatus(400);
    expect(McpOAuthRefreshToken::query()->whereNull('revoked_at')->count())->toBe(0);
});

test('refresh fails once the user loses mcp permissions', function () {
    $first = exchangeCode($this, approveAndGetCode($this))->json();

    $this->user->syncPermissions([]);
    $this->user->syncRoles([]);
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    $this->post(route('mcp.oauth.token'), [
        'grant_type' => 'refresh_token',
        'client_id' => $this->client->client_id,
        'refresh_token' => $first['refresh_token'],
    ])->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
});

test('unknown clients get invalid_client', function () {
    $this->post(route('mcp.oauth.token'), [
        'grant_type' => 'refresh_token',
        'client_id' => 'does-not-exist',
        'refresh_token' => 'nope',
    ])->assertStatus(401)->assertJsonPath('error', 'invalid_client');
});

test('revoking the connection in settings stops refresh', function () {
    $tokens = exchangeCode($this, approveAndGetCode($this))->json();
    $stored = PersonalAccessToken::findToken($tokens['access_token']);

    expect(app(McpTokenService::class)->revokeToken($this->user, $stored->id))->toBeTrue();

    $this->post(route('mcp.oauth.token'), [
        'grant_type' => 'refresh_token',
        'client_id' => $this->client->client_id,
        'refresh_token' => $tokens['refresh_token'],
    ])->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
});

test('refresh fails once the user can no longer manage settings', function () {
    $tokens = exchangeCode($this, approveAndGetCode($this))->json();

    // Keeps post permissions, loses the admin role that grants settings management.
    $this->user->syncRoles([]);
    $this->user->syncPermissions(['post.view', 'post.create']);
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    expect($this->user->fresh()->can('manage', Setting::class))->toBeFalse();

    $this->post(route('mcp.oauth.token'), [
        'grant_type' => 'refresh_token',
        'client_id' => $this->client->client_id,
        'refresh_token' => $tokens['refresh_token'],
    ])->assertStatus(400)->assertJsonPath('error', 'invalid_grant');

    expect(McpOAuthRefreshToken::query()->whereNull('revoked_at')->count())->toBe(0);
});

test('protocol errors are not reported as application errors', function () {
    \Illuminate\Support\Facades\Log::spy();

    $this->post(route('mcp.oauth.token'), ['grant_type' => 'password', 'client_id' => 'x'])->assertStatus(400);
    $this->post(route('mcp.oauth.token'), [
        'grant_type' => 'refresh_token',
        'client_id' => $this->client->client_id,
        'refresh_token' => 'nope',
    ])->assertStatus(400);

    \Illuminate\Support\Facades\Log::shouldNotHaveReceived('error');
    \Illuminate\Support\Facades\Log::shouldNotHaveReceived('log');
});

test('expired oauth rows are pruned', function () {
    exchangeCode($this, approveAndGetCode($this));
    approveAndGetCode($this);

    $this->travel(McpOAuthService::REFRESH_TOKEN_TTL_DAYS + 1)->days();
    $this->artisan('model:prune', ['--model' => [
        \App\Models\McpOAuthAuthorizationCode::class,
        McpOAuthRefreshToken::class,
    ]])->assertSuccessful();

    expect(\App\Models\McpOAuthAuthorizationCode::query()->count())->toBe(0)
        ->and(McpOAuthRefreshToken::query()->count())->toBe(0);
});
