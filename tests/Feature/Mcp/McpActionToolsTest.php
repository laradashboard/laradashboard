<?php

declare(strict_types=1);

use App\Enums\PostStatus;
use App\Mcp\Servers\LaraDashboardServer;
use App\Mcp\Tools\ClearCacheTool;
use App\Mcp\Tools\ListLogsTool;
use App\Mcp\Tools\UpdatePostTool;
use App\Models\Post;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\Mcp\McpRegistryService;
use App\Services\Mcp\McpTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    foreach (['post.view', 'post.create', 'post.edit', 'post.publish', 'settings.edit', 'dashboard.view'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => Role::ADMIN, 'guard_name' => 'web']);
    $this->user->assignRole($role);
    $this->user->syncPermissions(['post.view', 'post.create', 'post.edit', 'post.publish', 'settings.edit', 'dashboard.view']);

    app(McpTokenService::class)->createToken($this->user);
    $this->user->withAccessToken($this->user->tokens()->latest()->first());
    $this->actingAs($this->user);

    Setting::query()->updateOrCreate(
        ['option_name' => Setting::MCP_ENABLED],
        ['option_value' => '1', 'autoload' => true]
    );
    config(['settings.'.Setting::MCP_ENABLED => '1']);
});

test('core action mcp tools are registered in the registry', function () {
    $toolClasses = app(McpRegistryService::class)->toolClasses();

    expect($toolClasses)->toContain(UpdatePostTool::class)
        ->toContain(ClearCacheTool::class)
        ->toContain(ListLogsTool::class);
});

test('update post mcp tool publishes a pending post', function () {
    $post = Post::factory()->create([
        'title' => 'Pending MCP Post',
        'user_id' => $this->user->id,
        'status' => PostStatus::PENDING->value,
        'post_type' => 'post',
    ]);

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(UpdatePostTool::class, [
            'post_id' => $post->id,
            'post_type' => 'post',
            'status' => PostStatus::PUBLISHED->value,
        ])
        ->assertOk()
        ->assertSee(PostStatus::PUBLISHED->value);

    expect($post->fresh()->status)->toBe(PostStatus::PUBLISHED->value)
        ->and($post->fresh()->published_at)->not->toBeNull();
});

test('clear cache mcp tool clears caches', function () {
    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(ClearCacheTool::class, ['target' => 'all'])
        ->assertOk()
        ->assertSee('Cache cleared successfully');
});

test('list logs mcp tool returns storage log files', function () {
    File::ensureDirectoryExists(storage_path('logs'));
    $logPath = storage_path('logs/mcp-action-tool-test.log');
    File::put($logPath, 'test log entry');

    try {
        LaraDashboardServer::actingAs($this->user, 'sanctum')
            ->tool(ListLogsTool::class, ['search' => 'mcp-action-tool-test'])
            ->assertOk()
            ->assertSee('mcp-action-tool-test.log');
    } finally {
        if (File::exists($logPath)) {
            File::delete($logPath);
        }
    }
});
