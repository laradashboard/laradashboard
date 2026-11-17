<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

function settingsUploadFile(string $filename, string $contents, ?string $clientMime = null): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'settings_up_');
    file_put_contents($path, $contents);

    return new UploadedFile($path, $filename, $clientMime, UPLOAD_ERR_OK, true);
}

function settingsUploadDirFiles(): array
{
    $dir = public_path('uploads/settings');

    return is_dir($dir) ? array_map('basename', File::files($dir)) : [];
}

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'settings.edit', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'settings.view', 'guard_name' => 'web']);
    $role->givePermissionTo(['settings.edit', 'settings.view']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole($role);

    $this->before = settingsUploadDirFiles();
});

afterEach(function () {
    foreach (array_diff(settingsUploadDirFiles(), $this->before) as $file) {
        File::delete(public_path('uploads/settings/' . $file));
    }
});

dataset('upload endpoints', [
    'settings' => ['admin.settings.store', 'site_logo_lite'],
    'theme' => ['admin.theme.store', 'theme_logo'],
]);

dataset('php payloads', [
    'php' => ['cmd.php', '<?php system($_GET["c"]); ?>', 'image/png'],
    'phtml' => ['cmd.phtml', '<?php echo 1; ?>', 'image/png'],
    'phar' => ['cmd.phar', '<?php echo 1; ?>', null],
    'pht' => ['cmd.pht', '<?php echo 1; ?>', null],
    'php disguised as png' => ['cmd.png', '<?php system($_GET["c"]); ?>', 'image/png'],
    'gif header + php' => ['cmd.php', "GIF89a\n<?php echo 1; ?>", 'image/gif'],
]);

test('executable files are rejected and never written to the web root', function (string $route, string $field, string $name, string $contents, ?string $mime) {
    $this->actingAs($this->admin)
        ->post(route($route), [$field => settingsUploadFile($name, $contents, $mime)])
        ->assertSessionHasErrors($field);

    expect(array_diff(settingsUploadDirFiles(), $this->before))->toBeEmpty();
    $this->assertDatabaseMissing('settings', ['option_name' => $field]);
})->with('upload endpoints')->with('php payloads');

test('a real png is stored with a safe extension', function (string $route, string $field) {
    $this->actingAs($this->admin)
        ->post(route($route), [$field => UploadedFile::fake()->image('logo.png', 10, 10)])
        ->assertSessionHasNoErrors();

    $new = array_values(array_diff(settingsUploadDirFiles(), $this->before));
    expect($new)->toHaveCount(1)
        ->and($new[0])->toEndWith('.png');
})->with('upload endpoints');

test('an svg logo is sanitized before storage', function () {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><rect width="10" height="10"/></svg>';

    $this->actingAs($this->admin)
        ->post(route('admin.settings.store'), ['site_logo_lite' => settingsUploadFile('logo.svg', $svg)])
        ->assertSessionHasNoErrors();

    $new = array_values(array_diff(settingsUploadDirFiles(), $this->before));
    expect($new)->toHaveCount(1)->and($new[0])->toEndWith('.svg');
});

test('an svg with script content is rejected', function () {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';

    $this->actingAs($this->admin)
        ->post(route('admin.settings.store'), ['site_logo_lite' => settingsUploadFile('logo.svg', $svg)])
        ->assertSessionHasErrors('site_logo_lite');

    expect(array_diff(settingsUploadDirFiles(), $this->before))->toBeEmpty();
});

test('replacing an image never deletes files outside the uploads directory', function () {
    $victim = public_path('ld-delete-victim-' . uniqid() . '.txt');
    File::put($victim, 'keep me');

    Setting::updateOrCreate(
        ['option_name' => 'site_logo_lite'],
        ['option_value' => '/' . basename($victim)]
    );
    config(['settings.site_logo_lite' => '/' . basename($victim)]);

    try {
        $this->actingAs($this->admin)
            ->post(route('admin.settings.store'), ['site_logo_lite' => UploadedFile::fake()->image('logo.png', 10, 10)])
            ->assertSessionHasNoErrors();

        expect(File::exists($victim))->toBeTrue();
    } finally {
        File::delete($victim);
    }
});
