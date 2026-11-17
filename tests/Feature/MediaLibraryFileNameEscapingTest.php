<?php

declare(strict_types=1);

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->user = User::factory()->create();
    Permission::firstOrCreate(['name' => 'media.view', 'guard_name' => 'web']);
    $this->user->givePermissionTo('media.view');
});

function mediaNamed(string $name, string $fileName, string $mime): Media
{
    return Media::query()->create([
        'model_type' => '', 'model_id' => 0, 'uuid' => (string) Str::uuid(), 'collection_name' => 'uploads',
        'name' => $name, 'file_name' => $fileName, 'mime_type' => $mime, 'disk' => 'public', 'conversions_disk' => 'public',
        'size' => 10, 'manipulations' => [], 'custom_properties' => [], 'generated_conversions' => [], 'responsive_images' => [],
    ]);
}

test('file names cannot break out of the media library click handlers', function () {
    mediaNamed("x');alert(1);//", 'evil.png', 'image/png');
    mediaNamed("O'Brien brief", 'brief.pdf', 'application/pdf');

    $html = $this->actingAs($this->user)->get(route('admin.media.index'))->assertOk()->getContent();

    // The quote inside the name must stay escaped for JavaScript, not decoded back into the handler.
    expect($html)->not->toMatch('/onclick="[^"]*x(&#039;|\')\);alert/')
        ->and($html)->toContain('x'.chr(92).'u0027);alert(1);')
        ->and($html)->toContain('O'.chr(92).'u0027Brien brief');
});

test('the media picker escapes file fields it puts into html strings', function () {
    $source = file_get_contents(resource_path('views/components/media-modal.blade.php'));

    expect($source)->toContain('window.ldEscHtml')
        ->and($source)->not->toMatch('/\$\{file\.(name|url|mime_type)\}/')
        ->and($source)->not->toContain("openImageModal('\${file.url}'");
});

test('image thumbnails show the whole image', function () {
    mediaNamed('Wide logo', 'wide.png', 'image/png');

    $this->actingAs($this->user)->get(route('admin.media.index'))
        ->assertOk()
        ->assertSee('object-contain p-2', false);
});
