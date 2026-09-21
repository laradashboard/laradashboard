<?php

declare(strict_types=1);

use App\Mcp\Servers\LaraDashboardServer;
use App\Mcp\Tools\AttachFeaturedImageTool;
use App\Mcp\Tools\CreateMediaUploadTool;
use App\Mcp\Tools\FinalizeMediaUploadTool;
use App\Mcp\Tools\UploadMediaTool;
use App\Models\Media;
use App\Models\Post;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\Mcp\McpTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    foreach (['post.view', 'post.create', 'post.edit', 'media.view', 'media.create', 'settings.edit'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => Role::ADMIN, 'guard_name' => 'web']);
    $this->user->assignRole($role);
    $this->user->syncPermissions(['post.view', 'post.create', 'post.edit', 'media.view', 'media.create', 'settings.edit']);

    $plainToken = app(McpTokenService::class)->createToken($this->user);
    $this->plainToken = $plainToken;
    $this->user->withAccessToken($this->user->tokens()->latest()->first());
    $this->actingAs($this->user);

    Setting::query()->updateOrCreate(
        ['option_name' => Setting::MCP_ENABLED],
        ['option_value' => '1', 'autoload' => true]
    );
    config(['settings.'.Setting::MCP_ENABLED => '1']);
});

test('upload media tool rejects base64 payloads above fallback limit', function () {
    $largePayload = base64_encode(str_repeat('a', 101 * 1024));

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(UploadMediaTool::class, [
            'filename' => 'too-large.jpg',
            'mime_type' => 'image/jpeg',
            'content_base64' => $largePayload,
        ])
        ->assertSee('create-media-upload');
});

test('multipart mcp upload stores large image with serve_ok', function () {
    $sessionResponse = LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(CreateMediaUploadTool::class, [
            'filename' => 'hero.jpg',
            'mime_type' => 'image/jpeg',
            'title' => 'Hero Image',
            'alt_text' => 'Blog hero',
        ])
        ->assertOk()
        ->assertSee('upload_token');

    // Extract upload URL from the tool response JSON embedded in MCP output.
    $reflection = new ReflectionClass($sessionResponse);
    $prop = $reflection->getProperty('response');
    $prop->setAccessible(true);
    $payload = $prop->getValue($sessionResponse)->toArray();
    $text = $payload['result']['content'][0]['text'] ?? '';
    $decoded = json_decode($text, true);
    $uploadToken = $decoded['upload']['upload_token'] ?? null;
    $uploadUrl = $decoded['upload']['upload_url'] ?? null;

    expect($uploadToken)->not->toBeNull();
    expect($uploadUrl)->not->toBeNull();

    $file = UploadedFile::fake()->image('hero.jpg', 1200, 630)->size(160);

    $uploadResponse = $this->post($uploadUrl, [
        'file' => $file,
    ]);

    $uploadResponse->assertCreated()
        ->assertJsonPath('media.serve_ok', true);

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(FinalizeMediaUploadTool::class, [
            'upload_token' => $uploadToken,
        ])
        ->assertOk()
        ->assertSee('serve_ok');
});

test('bearer multipart upload endpoint accepts mcp token', function () {
    auth()->guard('web')->logout();

    $file = UploadedFile::fake()->image('bearer-hero.jpg', 800, 420)->size(120);

    $response = $this->withHeader('Authorization', 'Bearer '.$this->plainToken)
        ->post(route('mcp.media.upload.store'), [
            'file' => $file,
        ]);

    $response->assertCreated()
        ->assertJsonPath('media.serve_ok', true);
});

test('attach featured image fails when media file is missing on disk', function () {
    $post = Post::factory()->create([
        'user_id' => $this->user->id,
        'post_type' => 'post',
    ]);

    $directory = storage_path('app/public/media');
    File::ensureDirectoryExists($directory);

    $media = Media::create([
        'model_type' => '',
        'model_id' => 0,
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'collection_name' => 'uploads',
        'name' => 'Missing File',
        'file_name' => 'missing-on-disk.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'conversions_disk' => 'public',
        'size' => 100,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
        'order_column' => null,
    ]);

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(AttachFeaturedImageTool::class, [
            'post_id' => $post->id,
            'post_type' => 'post',
            'media_id' => $media->id,
        ])
        ->assertSee('not serveable');
});

test('signed upload url expires when signature is invalid', function () {
    URL::forceRootUrl('http://laradashboard.test');

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(CreateMediaUploadTool::class, [
            'filename' => 'hero.jpg',
            'mime_type' => 'image/jpeg',
        ]);

    $response = $this->post('/mcp/media/upload/not-a-valid-token', [
        'file' => UploadedFile::fake()->image('hero.jpg'),
    ]);

    $response->assertForbidden();
});
