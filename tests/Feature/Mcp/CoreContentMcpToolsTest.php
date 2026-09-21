<?php

declare(strict_types=1);

use App\Mcp\Servers\LaraDashboardServer;
use App\Mcp\Tools\AssignPostTermsTool;
use App\Mcp\Tools\AttachFeaturedImageTool;
use App\Mcp\Tools\DeletePostTool;
use App\Mcp\Tools\GetLogTailTool;
use App\Mcp\Tools\GetSiteHealthTool;
use App\Mcp\Tools\ListMediaTool;
use App\Mcp\Tools\UploadMediaTool;
use App\Mcp\Tools\ListMcpToolsTool;
use App\Mcp\Tools\ListModulesTool;
use App\Mcp\Tools\ListTermsTool;
use App\Models\Media;
use App\Models\Post;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Term;
use App\Models\User;
use App\Services\Mcp\McpRegistryService;
use App\Services\Mcp\McpTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

function mcpFeatureTestPngBytes(): string
{
    $decoded = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
        true,
    );

    return is_string($decoded) ? $decoded : '';
}

beforeEach(function () {
    foreach ([
        'post.view', 'post.create', 'post.edit', 'post.delete',
        'term.view', 'media.view', 'media.create', 'settings.edit', 'dashboard.view', 'module.view',
    ] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => Role::ADMIN, 'guard_name' => 'web']);
    $this->user->assignRole($role);
    $this->user->syncPermissions([
        'post.view', 'post.create', 'post.edit', 'post.delete',
        'term.view', 'media.view', 'media.create', 'settings.edit', 'dashboard.view', 'module.view',
    ]);

    app(McpTokenService::class)->createToken($this->user);
    $this->user->withAccessToken($this->user->tokens()->latest()->first());
    $this->actingAs($this->user);

    Setting::query()->updateOrCreate(
        ['option_name' => Setting::MCP_ENABLED],
        ['option_value' => '1', 'autoload' => true]
    );
    config(['settings.'.Setting::MCP_ENABLED => '1']);
});

test('new core content and discovery mcp tools are registered', function () {
    $toolClasses = app(McpRegistryService::class)->toolClasses();

    expect($toolClasses)->toContain(DeletePostTool::class)
        ->toContain(AssignPostTermsTool::class)
        ->toContain(ListTermsTool::class)
        ->toContain(ListMediaTool::class)
        ->toContain(UploadMediaTool::class)
        ->toContain(AttachFeaturedImageTool::class)
        ->toContain(GetLogTailTool::class)
        ->toContain(GetSiteHealthTool::class)
        ->toContain(ListMcpToolsTool::class)
        ->toContain(ListModulesTool::class);
});

test('delete post mcp tool removes a draft post', function () {
    $post = Post::factory()->create([
        'title' => 'Draft To Delete',
        'user_id' => $this->user->id,
        'post_type' => 'post',
    ]);

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(DeletePostTool::class, [
            'post_id' => $post->id,
            'post_type' => 'post',
        ])
        ->assertOk()
        ->assertSee('Post deleted successfully');

    expect(Post::query()->find($post->id))->toBeNull();
});

test('list terms mcp tool returns categories', function () {
    $category = Term::factory()->category()->create(['name' => 'MCP Category']);

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(ListTermsTool::class, ['taxonomy' => 'category', 'search' => 'MCP Category'])
        ->assertOk()
        ->assertSee($category->name)
        ->assertSee((string) $category->id);
});

test('assign post terms mcp tool syncs terms on a post', function () {
    $post = Post::factory()->create([
        'title' => 'Terms MCP Post',
        'user_id' => $this->user->id,
        'post_type' => 'post',
    ]);
    $category = Term::factory()->category()->create(['name' => 'Assigned Category']);
    $tag = Term::factory()->tag()->create(['name' => 'Assigned Tag']);

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(AssignPostTermsTool::class, [
            'post_id' => $post->id,
            'post_type' => 'post',
            'term_ids' => [$category->id, $tag->id],
        ])
        ->assertOk()
        ->assertSee('Terms assigned successfully');

    expect($post->fresh()->terms->pluck('id')->sort()->values()->all())
        ->toBe(collect([$category->id, $tag->id])->sort()->values()->all());
});

test('upload media mcp tool stores base64 image in the library', function () {
    $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(UploadMediaTool::class, [
            'filename' => 'mcp-upload-hero.png',
            'mime_type' => 'image/png',
            'content_base64' => $pngBase64,
            'title' => 'MCP Upload Hero',
            'alt_text' => 'Generated hero image',
        ])
        ->assertOk()
        ->assertSee('Media uploaded successfully')
        ->assertSee('mcp-upload-hero');

    $media = Media::query()->where('file_name', 'like', 'mcp-upload-hero%')->first();
    expect($media)->not->toBeNull();
    expect($media?->custom_properties['alt_text'] ?? null)->toBe('Generated hero image');
});

test('upload media mcp tool rejects token without media write ability', function () {
    $limitedUser = User::factory()->create();
    $limitedUser->syncPermissions(['media.view', 'post.view']);

    app(McpTokenService::class)->createToken($limitedUser);
    $limitedUser->withAccessToken($limitedUser->tokens()->latest()->first());

    LaraDashboardServer::actingAs($limitedUser, 'sanctum')
        ->tool(UploadMediaTool::class, [
            'filename' => 'blocked.png',
            'mime_type' => 'image/png',
            'content_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
        ])
        ->assertSee('mcp:media.write');
});

test('list media mcp tool returns library items', function () {
    $directory = storage_path('app/public/media');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/mcp-media-test.png', 'fake-image');

    $media = Media::create([
        'model_type' => '',
        'model_id' => 0,
        'uuid' => (string) Str::uuid(),
        'collection_name' => 'uploads',
        'name' => 'MCP Media Test',
        'file_name' => 'mcp-media-test.png',
        'mime_type' => 'image/png',
        'disk' => 'public',
        'conversions_disk' => 'public',
        'size' => 10,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
        'order_column' => null,
    ]);

    try {
        LaraDashboardServer::actingAs($this->user, 'sanctum')
            ->tool(ListMediaTool::class, ['search' => 'MCP Media Test'])
            ->assertOk()
            ->assertSee('MCP Media Test')
            ->assertSee((string) $media->id);
    } finally {
        File::delete($directory.'/mcp-media-test.png');
    }
});

test('attach featured image mcp tool links media to a post', function () {
    $directory = storage_path('app/public/media');
    File::ensureDirectoryExists($directory);
    $pngBytes = mcpFeatureTestPngBytes();
    File::put($directory.'/mcp-featured.png', $pngBytes);

    $media = Media::create([
        'model_type' => '',
        'model_id' => 0,
        'uuid' => (string) Str::uuid(),
        'collection_name' => 'uploads',
        'name' => 'MCP Featured',
        'file_name' => 'mcp-featured.png',
        'mime_type' => 'image/png',
        'disk' => 'public',
        'conversions_disk' => 'public',
        'size' => strlen($pngBytes),
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
        'order_column' => null,
    ]);

    $post = Post::factory()->create([
        'title' => 'Featured MCP Post',
        'user_id' => $this->user->id,
        'post_type' => 'post',
    ]);

    try {
        LaraDashboardServer::actingAs($this->user, 'sanctum')
            ->tool(AttachFeaturedImageTool::class, [
                'post_id' => $post->id,
                'post_type' => 'post',
                'media_id' => $media->id,
            ])
            ->assertOk()
            ->assertSee('Featured image attached successfully');
    } finally {
        File::delete($directory.'/mcp-featured.png');
    }
});

test('get log tail mcp tool returns last lines from a log file', function () {
    File::ensureDirectoryExists(storage_path('logs'));
    $logPath = storage_path('logs/mcp-tail-test.log');
    File::put($logPath, "line one\nline two\nline three");

    try {
        LaraDashboardServer::actingAs($this->user, 'sanctum')
            ->tool(GetLogTailTool::class, [
                'file' => 'logs/mcp-tail-test.log',
                'lines' => 2,
            ])
            ->assertOk()
            ->assertSee('line two')
            ->assertSee('line three')
            ->assertDontSee('line one');
    } finally {
        if (File::exists($logPath)) {
            File::delete($logPath);
        }
    }
});

test('get site health mcp tool returns environment snapshot', function () {
    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(GetSiteHealthTool::class, [])
        ->assertOk()
        ->assertSee(app()->version())
        ->assertSee('mcp')
        ->assertSee('modules');
});

test('list mcp tools returns tools available to the current token', function () {
    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(ListMcpToolsTool::class, [])
        ->assertOk()
        ->assertSee('delete-post')
        ->assertSee('list-modules')
        ->assertSee('get-log-tail');
});

test('list modules mcp tool returns installed modules', function () {
    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(ListModulesTool::class, [])
        ->assertOk()
        ->assertSee('data')
        ->assertSee('total');
});
