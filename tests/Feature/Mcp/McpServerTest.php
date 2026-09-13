<?php

declare(strict_types=1);

use App\Mcp\Servers\LaraDashboardServer;
use App\Mcp\Tools\CreatePostTool;
use App\Mcp\Tools\GenerateSeoMetaTool;
use App\Mcp\Tools\GetPostTool;
use App\Mcp\Tools\ListPostsTool;
use App\Models\Post;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\Builder\PostImageService;
use App\Services\Mcp\McpTokenService;
use App\Services\Mcp\McpSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    foreach (['post.view', 'post.create', 'post.edit', 'settings.edit'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => Role::ADMIN, 'guard_name' => 'web']);
    $this->user->assignRole($role);
    $this->user->syncPermissions(['post.view', 'post.create', 'post.edit', 'settings.edit']);

    app(McpTokenService::class)->createToken($this->user);
    $accessToken = $this->user->tokens()->latest()->first();
    $this->user->withAccessToken($accessToken);

    $this->actingAs($this->user);
});

function enableMcp(): void
{
    Setting::query()->updateOrCreate(
        ['option_name' => Setting::MCP_ENABLED],
        ['option_value' => '1', 'autoload' => true]
    );

    config(['settings.'.Setting::MCP_ENABLED => '1']);
}

test('mcp endpoint returns 404 when disabled', function () {
    config(['settings.'.Setting::MCP_ENABLED => '0']);

    $plainToken = app(McpTokenService::class)->createToken($this->user);

    $this->withHeader('Authorization', 'Bearer '.$plainToken)
        ->post('/mcp', [])
        ->assertNotFound();
});

test('mcp endpoint rejects regular api tokens when enabled', function () {
    enableMcp();

    $regularToken = $this->user->createToken('auth_token')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$regularToken)
        ->post('/mcp', [])
        ->assertForbidden();
});

test('list posts tool returns published posts', function () {
    Post::factory()->create([
        'title' => 'MCP Test Post',
        'user_id' => $this->user->id,
        'post_type' => 'post',
    ]);

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(ListPostsTool::class, ['post_type' => 'post', 'per_page' => 5])
        ->assertOk()
        ->assertSee('MCP Test Post');
});

test('get post tool returns a single post', function () {
    $post = Post::factory()->create([
        'title' => 'Single MCP Post',
        'user_id' => $this->user->id,
        'post_type' => 'post',
    ]);

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(GetPostTool::class, ['post_id' => $post->id, 'post_type' => 'post'])
        ->assertOk()
        ->assertSee('Single MCP Post');
});

test('create post tool creates a draft post manually', function () {
    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(CreatePostTool::class, [
            'title' => 'Created Via MCP',
            'content' => '<h2>Section One</h2><p>Hello from MCP</p>',
            'status' => 'draft',
            'post_type' => 'post',
            'include_images' => false,
        ])
        ->assertOk()
        ->assertSee('Created Via MCP');

    $post = Post::query()->where('title', 'Created Via MCP')->first();

    expect($post)->not->toBeNull()
        ->and($post->design_json)->toBeArray()
        ->and($post->design_json['blocks'] ?? [])->not->toBeEmpty()
        ->and(collect($post->design_json['blocks'])->pluck('type')->all())->toContain('heading', 'text');
});

test('create post tool creates larabuilder blocks from plain text content', function () {
    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(CreatePostTool::class, [
            'title' => 'Plain Text MCP Post',
            'content' => "Intro paragraph.\n\nSection heading\n\nAnother paragraph.",
            'status' => 'draft',
            'post_type' => 'post',
            'include_images' => false,
        ])
        ->assertOk();

    $post = Post::query()->where('title', 'Plain Text MCP Post')->first();

    expect($post?->design_json['blocks'] ?? [])->not->toBeEmpty();
});

test('create post tool prepends image block when image generation succeeds', function () {
    $this->mock(PostImageService::class, function ($mock): void {
        $mock->shouldReceive('canGenerate')->once()->andReturnTrue();
        $mock->shouldReceive('generateImages')
            ->once()
            ->andReturn([['url' => '/uploads/posts/mcp-header.jpg', 'alt' => 'Header']]);
        $mock->shouldReceive('prependFeaturedImageBlocks')
            ->once()
            ->andReturnUsing(function (array $blocks): array {
                return array_merge([
                    ['type' => 'image', 'props' => ['src' => '/uploads/posts/mcp-header.jpg']],
                ], $blocks);
            });
    });

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(CreatePostTool::class, [
            'title' => 'Image MCP Post',
            'content' => '<p>Body content</p>',
            'include_images' => true,
            'image_count' => 1,
            'status' => 'draft',
            'post_type' => 'post',
        ])
        ->assertOk()
        ->assertSee('image_count');

    $post = Post::query()->where('title', 'Image MCP Post')->first();

    expect(collect($post?->design_json['blocks'] ?? [])->pluck('type')->all())->toContain('image');
});

test('generate seo meta tool generates metadata for a post', function () {
    $post = Post::factory()->create([
        'title' => 'SEO MCP Post',
        'content' => '<p>Content for SEO generation.</p>',
        'user_id' => $this->user->id,
        'post_type' => 'post',
    ]);

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(GenerateSeoMetaTool::class, ['post_id' => $post->id])
        ->assertOk()
        ->assertSee('SEO meta generated successfully');
});

test('admin cannot create mcp agent token when mcp is disabled', function () {
    config(['settings.'.Setting::MCP_ENABLED => '0']);

    $this->actingAs($this->user)
        ->postJson(route('admin.settings.mcp.tokens.store'), [
            'label' => 'Disabled MCP test',
        ])
        ->assertUnprocessable()
        ->assertJson([
            'success' => false,
            'message' => __('Enable MCP access in settings before creating an agent token.'),
        ]);
});

test('settings page disables token generation when mcp is disabled', function () {
    config(['settings.'.Setting::MCP_ENABLED => '0']);

    $this->actingAs($this->user)
        ->get(route('admin.settings.mcp.index'))
        ->assertOk()
        ->assertSee('Unsaved changes', false)
        ->assertSee('mcpEnabledSaved: false', false)
        ->assertSee('openTokenModal()', false);
});

test('admin can create mcp agent token when mcp is enabled', function () {
    enableMcp();

    $response = $this->actingAs($this->user)
        ->postJson(route('admin.settings.mcp.tokens.store'), [
            'label' => 'Cursor blog automation',
        ]);

    $response->assertCreated()
        ->assertJsonStructure(['success', 'token', 'abilities', 'token_record'])
        ->assertJsonPath('token_record.label', 'Cursor blog automation');

    $token = $this->user->tokens()
        ->where('name', 'laradashboard-mcp: Cursor blog automation')
        ->first();

    expect($token)->not->toBeNull();
});

test('mcp token creation requires a label', function () {
    enableMcp();

    $this->actingAs($this->user)
        ->postJson(route('admin.settings.mcp.tokens.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['label']);
});

test('cursor env config snippet uses environment variable placeholder', function () {
    $json = app(McpSettingsService::class)->cursorEnvConfigJson();

    expect($json)
        ->toContain('LARADASHBOARD_MCP_TOKEN')
        ->toContain('${env:LARADASHBOARD_MCP_TOKEN}');
});

test('settings page includes mcp tab content', function () {
    $response = $this->actingAs($this->user)
        ->get(route('admin.settings.mcp.index'));

    $response->assertOk()
        ->assertSee('Model Context Protocol (MCP)', false)
        ->assertSee('Enable MCP server', false)
        ->assertSee('Generate MCP token', false)
        ->assertSee('Connect Your AI Client', false)
        ->assertSeeText('Cursor (Desktop & CLI)')
        ->assertSee('agent mcp list', false)
        ->assertSee('LARADASHBOARD_MCP_TOKEN', false)
        ->assertSee('AI Client', false)
        ->assertSee('Search tools...', false)
        ->assertSee('copyButton', false);
});

test('settings mcp tab redirects to dedicated page', function () {
    $this->actingAs($this->user)
        ->get(route('admin.settings.index', ['tab' => 'mcp']))
        ->assertRedirect(route('admin.settings.mcp.index'));
});
