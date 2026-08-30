<?php

declare(strict_types=1);

use App\Http\Middleware\CheckPhpUploadLimits;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use App\Services\SvgSanitizer;
use App\Support\Helper\MediaHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Spatie\Permission\Models\Permission;

pest()->use(Illuminate\Foundation\Testing\RefreshDatabase::class);

function validSvgMarkup(): string
{
    return <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">
            <rect width="100" height="100" fill="red"/>
        </svg>
        SVG;
}

function makeUploadFile(string $filename, string $contents): UploadedFile
{
    $extension = pathinfo($filename, PATHINFO_EXTENSION) ?: 'bin';
    $path = tempnam(sys_get_temp_dir(), 'media_sec_');
    $target = $path . '.' . $extension;
    @unlink($path);
    file_put_contents($target, $contents);

    return new UploadedFile($target, $filename, null, UPLOAD_ERR_OK, true);
}

function mediaCreateUser(): User
{
    $user = User::factory()->create();
    Permission::firstOrCreate(['name' => 'media.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'media.view', 'guard_name' => 'web']);
    $user->givePermissionTo(['media.create', 'media.view']);

    return $user;
}

function storedMediaContents(?SpatieMedia $media = null): ?string
{
    $media ??= SpatieMedia::query()->latest('id')->first();

    if (! $media) {
        return null;
    }

    $path = storage_path('app/public/media/' . $media->file_name);

    return is_file($path) ? file_get_contents($path) : null;
}

beforeEach(function () {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    config(['app.demo_mode' => false]);

    $this->withoutMiddleware([
        VerifyCsrfToken::class,
        CheckPhpUploadLimits::class,
    ]);

    $this->user = mediaCreateUser();
});

afterEach(function () {
    foreach (glob(storage_path('app/public/media/*')) ?: [] as $file) {
        if (is_file($file) && ! str_ends_with($file, '.gitignore') && ! str_ends_with($file, '.htaccess')) {
            @unlink($file);
        }
    }
});

test('valid svg uploads remain functional', function () {
    $file = makeUploadFile('logo.svg', validSvgMarkup());

    $response = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$file],
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('files.0.mime_type', 'image/svg+xml');

    $this->assertDatabaseCount('media', 1);

    $media = SpatieMedia::query()->first();
    $this->assertNotNull($media);
    $stored = storedMediaContents($media);
    $this->assertNotNull($stored);
    $this->assertStringContainsString('<svg', $stored);
    $this->assertStringContainsString('<rect', $stored);
    $this->assertNotEmpty($response->json('files.0.url'));
    $this->assertFileExists(storage_path('app/public/media/' . $media->file_name));
});

test('script elements are rejected from svg uploads', function () {
    $file = makeUploadFile('xss.svg', <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg">
            <script>alert(document.domain)</script>
            <rect width="100" height="100"/>
        </svg>
        SVG);

    $response = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$file],
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonFragment(['This SVG was rejected because it contains unsafe content.']);
    $this->assertDatabaseCount('media', 0);
    $this->assertSame([], glob(storage_path('app/public/media/*.svg')) ?: []);
});

test('onload event handlers are rejected from svg uploads', function () {
    $file = makeUploadFile('onload.svg', <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg" onload="alert(document.domain)"></svg>
        SVG);

    $response = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$file],
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertSee('unsafe content', false);
    $this->assertDatabaseCount('media', 0);
});

test('other event handlers are rejected from svg uploads', function () {
    $file = makeUploadFile('events.svg', <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100"
                  onclick="alert(1)"
                  onmouseover="alert(1)"
                  onerror="alert(1)"
                  onfocus="alert(1)"
                  onbegin="alert(1)"
                  onend="alert(1)"/>
        </svg>
        SVG);

    $response = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$file],
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertSee('unsafe content', false);
    $this->assertDatabaseCount('media', 0);
});

test('javascript urls are rejected from svg uploads', function () {
    $file = makeUploadFile('js-url.svg', <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg">
            <a href="javascript:alert(document.domain)">
                <rect width="100" height="100"/>
            </a>
        </svg>
        SVG);

    $response = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$file],
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertSee('unsafe content', false);
    $this->assertDatabaseCount('media', 0);
});

test('dangerous embedded content is rejected from svg uploads', function () {
    $file = makeUploadFile('foreign.svg', <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg">
            <foreignObject width="100" height="100">
                <body xmlns="http://www.w3.org/1999/xhtml"><script>alert(1)</script></body>
            </foreignObject>
            <object data="javascript:alert(1)"></object>
            <embed src="javascript:alert(1)"/>
        </svg>
        SVG);

    $response = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$file],
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertSee('unsafe content', false);
    $this->assertDatabaseCount('media', 0);
});

test('malformed svg uploads are rejected', function () {
    $file = makeUploadFile('broken.svg', '<svg><unclosed');

    $response = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$file],
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertSee('invalid or malformed', false);
    $this->assertDatabaseCount('media', 0);
    $this->assertSame([], glob(storage_path('app/public/media/*.svg')) ?: []);
});

test('php8 uploads are rejected', function () {
    $file = makeUploadFile('shell.php8', '<?php echo "pwned";');

    $response = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$file],
    ]);

    $response->assertUnprocessable();
    $this->assertDatabaseCount('media', 0);
    $this->assertSame([], glob(storage_path('app/public/media/*.php8')) ?: []);
});

test('phar uploads are rejected', function () {
    $file = makeUploadFile('shell.phar', '<?php echo "pwned";');

    $response = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$file],
    ]);

    $response->assertUnprocessable();
    $this->assertDatabaseCount('media', 0);
    $this->assertSame([], glob(storage_path('app/public/media/*.phar')) ?: []);
});

test('phtml uploads are rejected', function () {
    $file = makeUploadFile('shell.phtml', '<?php echo "pwned";');

    $response = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$file],
    ]);

    $response->assertUnprocessable();
    $this->assertDatabaseCount('media', 0);
    $this->assertSame([], glob(storage_path('app/public/media/*.phtml')) ?: []);
});

test('extension and mime mismatches are rejected', function () {
    $file = makeUploadFile('image.jpg', '<?php echo "pwned";');

    $response = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$file],
    ]);

    $response->assertUnprocessable();
    $this->assertDatabaseCount('media', 0);
});

test('uppercase legitimate extensions still upload', function () {
    $jpg = UploadedFile::fake()->image('IMAGE.JPG', 20, 20);
    $png = UploadedFile::fake()->image('IMAGE.PNG', 20, 20);
    $svg = makeUploadFile('IMAGE.SVG', validSvgMarkup());

    $jpgResponse = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$jpg],
    ]);
    $pngResponse = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$png],
    ]);
    $svgResponse = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$svg],
    ]);

    $jpgResponse->assertOk()->assertJsonPath('success', true);
    $pngResponse->assertOk()->assertJsonPath('success', true);
    $svgResponse->assertOk()->assertJsonPath('success', true);
    $this->assertDatabaseCount('media', 3);
});

test('mime restrictions are enforced when demo mode is off', function () {
    config(['app.demo_mode' => false]);

    $file = makeUploadFile('shell.php8', '<?php echo "pwned";');

    $response = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$file],
    ]);

    $response->assertUnprocessable();
    $this->assertDatabaseCount('media', 0);
    expect(MediaHelper::isAllowedMimeType('text/x-php'))->toBeFalse();
    expect(MediaHelper::isAllowedInDemoMode('text/x-php'))->toBeFalse();
});

test('low privilege media.create user can upload valid svg but not unsafe files', function () {
    expect($this->user->hasRole('Superadmin'))->toBeFalse();
    expect($this->user->can('media.create'))->toBeTrue();
    expect($this->user->can('media.view'))->toBeTrue();

    $valid = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [makeUploadFile('ok.svg', validSvgMarkup())],
    ]);
    $valid->assertOk()->assertJsonPath('success', true);

    $malicious = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [makeUploadFile('bad.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><script>alert(1)</script></svg>')],
    ]);
    $malicious->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertSee('unsafe content', false);
    $this->assertDatabaseCount('media', 1);

    $php = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [makeUploadFile('shell.php8', '<?php echo 1;')],
    ]);
    $php->assertUnprocessable();
});

test('sanitized svg is served with nosniff and svg content type', function () {
    $upload = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [makeUploadFile('served.svg', validSvgMarkup())],
    ]);

    $upload->assertOk();
    $media = SpatieMedia::query()->first();
    $this->assertNotNull($media);

    $response = $this->get('/storage/media/' . $media->file_name);

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    expect(strtolower((string) $response->headers->get('Content-Type')))->toStartWith('image/svg+xml');
    expect((string) $response->headers->get('Content-Security-Policy'))->toContain('sandbox');

    $stored = storedMediaContents($media);
    expect($stored)->toContain('<svg')
        ->and($stored)->not->toMatch('/<script\b/i');
});

test('generated filenames cannot traverse directories', function () {
    $file = makeUploadFile('../../etc/passwd.svg', validSvgMarkup());

    $response = $this->actingAs($this->user)->postJson(route('admin.media.store'), [
        'files' => [$file],
    ]);

    $response->assertOk();
    $media = SpatieMedia::query()->first();
    $this->assertNotNull($media);
    $this->assertStringNotContainsString('..', $media->file_name);
    $this->assertStringNotContainsString('/', $media->file_name);
    $this->assertStringNotContainsString('\\', $media->file_name);
    $this->assertFileExists(storage_path('app/public/media/' . $media->file_name));
    $this->assertTrue(Storage::disk('public')->exists('media/' . $media->file_name));
});

test('svg sanitizer rejects executable constructs instead of storing them', function () {
    $sanitizer = app(SvgSanitizer::class);

    expect(fn () => $sanitizer->sanitize(<<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)">
            <script>alert(1)</script>
            <rect width="10" height="10"/>
        </svg>
        SVG))->toThrow(RuntimeException::class, 'unsafe content');
});
