<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Mcp\McpTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

pest()->use(RefreshDatabase::class);

test('issue token stores a labeled sanctum token name', function () {
    $user = User::factory()->create();
    $service = app(McpTokenService::class);

    $issued = $service->issueToken($user, 'Cursor blog automation');

    expect($issued['plain_text'])->not->toBeEmpty();
    expect($issued['token']->name)->toBe('laradashboard-mcp: Cursor blog automation');
    expect($service->tokenLabel($issued['token']))->toBe('Cursor blog automation');
    expect($service->isMcpToken($issued['token']))->toBeTrue();
});

test('legacy unnamed mcp tokens remain recognized', function () {
    $user = User::factory()->create();
    $service = app(McpTokenService::class);

    $plainText = $service->createToken($user);
    $token = PersonalAccessToken::findToken($plainText);

    expect($token)->not->toBeNull();
    expect($service->isMcpToken($token))->toBeTrue();
    expect($service->tokenLabel($token))->toBe('MCP Agent');
});

test('list tokens includes labeled and legacy mcp tokens', function () {
    $user = User::factory()->create();
    $service = app(McpTokenService::class);

    $service->createToken($user);
    $service->issueToken($user, 'Claude Desktop');

    expect($service->listTokensForUser($user))->toHaveCount(2);
});
