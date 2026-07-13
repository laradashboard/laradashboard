<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\CheckPhpUploadLimits;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->withoutMiddleware([
            VerifyCsrfToken::class,
            CheckPhpUploadLimits::class,
        ]);

        $this->user = User::factory()->create();
        Permission::firstOrCreate(['name' => 'media.create', 'guard_name' => 'web']);
        $this->user->givePermissionTo('media.create');
    }

    protected function tearDown(): void
    {
        foreach (glob(storage_path('app/public/media/uploaded-image*.png')) ?: [] as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    public function test_media_upload_returns_formatted_files_with_urls(): void
    {
        $file = UploadedFile::fake()->image('uploaded-image.png', 120, 80);

        $response = $this->actingAs($this->user)->post(route('admin.media.store'), [
            'files' => [$file],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'files' => [
                    [
                        'id',
                        'name',
                        'url',
                        'thumbnail_url',
                        'mime_type',
                        'created_at',
                    ],
                ],
            ]);

        $uploadedFile = $response->json('files.0');
        $this->assertNotEmpty($uploadedFile['url']);
        $this->assertNotEmpty($uploadedFile['thumbnail_url']);
        $this->assertSame('image/png', $uploadedFile['mime_type']);
    }
}
