<?php

declare(strict_types=1);

use App\Exceptions\ModuleException;
use App\Services\Modules\MarketplaceService;
use App\Services\Modules\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * GHSA-75q6-qrv2-96rx: the install folder name is derived from untrusted
 * module metadata and must never escape the modules directory.
 */
beforeEach(function () {
    $this->setUpSecurityUsers();

    $this->marker = 'poc_'.Str::random(10);
    $this->workDir = storage_path('app/testing-traversal/'.$this->marker);
    File::ensureDirectoryExists($this->workDir);
});

afterEach(function () {
    // Nothing may ever be installed by these tests.
    expect(File::isDirectory(base_path('modules/InnocuousModule')))->toBeFalse();

    File::deleteDirectory(storage_path('app/testing-traversal'));
    File::deleteDirectory(public_path($this->marker));
    File::deleteDirectory('/tmp/'.$this->marker);
    File::delete(storage_path('app/modules/module.zip'));
});

/**
 * @param  array<string, string>  $files
 */
function traversalZip(string $workDir, array $files): UploadedFile
{
    $zipPath = $workDir.'/module.zip';
    $zip = new ZipArchive();
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    foreach ($files as $name => $contents) {
        $zip->addFromString($name, $contents);
    }
    $zip->close();

    return new UploadedFile($zipPath, 'module.zip', 'application/zip', null, true);
}

function uploadTraversalModule(UploadedFile $file): ?Throwable
{
    $request = Request::create('/admin/modules/upload', 'POST');
    $request->files->set('module', $file);

    try {
        app(ModuleService::class)->uploadModule($request);
    } catch (Throwable $e) {
        return $e;
    }

    return null;
}

test('module.json providers cannot steer the install outside modules/', function (string $prefix) {
    $traversal = $prefix.$this->marker;

    $error = uploadTraversalModule(traversalZip($this->workDir, [
        'module.json' => json_encode([
            'name' => 'innocuous-module',
            'providers' => ['Modules\\'.$traversal.'\\FakeServiceProvider'],
        ], JSON_UNESCAPED_SLASHES),
        'marker.php' => '<?php // inert marker',
    ]));

    expect($error)->toBeInstanceOf(ModuleException::class)
        ->and(File::isDirectory(public_path($this->marker)))->toBeFalse()
        ->and(File::isDirectory('/tmp/'.$this->marker))->toBeFalse()
        ->and(File::isDirectory(base_path('modules/'.$traversal)))->toBeFalse();
})->with([
    'into public/' => '../public/',
    'out of the app' => str_repeat('../', 12).'tmp/',
]);

test('composer.json psr-4 keys cannot steer the install outside modules/', function () {
    $error = uploadTraversalModule(traversalZip($this->workDir, [
        'module.json' => json_encode(['name' => 'innocuous-module']),
        'composer.json' => json_encode([
            'autoload' => ['psr-4' => ['Modules\\../public/'.$this->marker.'\\' => 'app/']],
        ], JSON_UNESCAPED_SLASHES),
        'marker.php' => '<?php // inert marker',
    ]));

    expect($error)->toBeInstanceOf(ModuleException::class)
        ->and(File::isDirectory(public_path($this->marker)))->toBeFalse();
});

test('the module name fallback cannot steer the install outside modules/', function () {
    $error = uploadTraversalModule(traversalZip($this->workDir, [
        'module.json' => json_encode(['name' => '../public/'.$this->marker], JSON_UNESCAPED_SLASHES),
        'marker.php' => '<?php // inert marker',
    ]));

    expect($error)->toBeInstanceOf(ModuleException::class)
        ->and(File::isDirectory(public_path($this->marker)))->toBeFalse();
});

test('marketplace installs cannot steer the install outside modules/', function (array $files) {
    config(['laradashboard.marketplace.url' => 'https://marketplace.test']);
    $zip = traversalZip($this->workDir, $files);
    Http::fake(['marketplace.test/*' => Http::response(File::get($zip->getPathname()))]);

    $result = app(MarketplaceService::class)->downloadAndInstall('innocuous-module', '1.0.0');

    expect($result['success'])->toBeFalse()
        ->and(File::isDirectory(public_path($this->marker)))->toBeFalse();
})->with([
    'module.json providers' => fn () => [
        'module.json' => json_encode(['name' => 'innocuous-module', 'providers' => ['Modules\\../public/'.$this->marker.'\\P']], JSON_UNESCAPED_SLASHES),
        'marker.php' => '<?php // inert marker',
    ],
    'composer.json psr-4' => fn () => [
        'module.json' => json_encode(['name' => 'innocuous-module']),
        'composer.json' => json_encode(['autoload' => ['psr-4' => ['Modules\\../public/'.$this->marker.'\\' => 'app/']]], JSON_UNESCAPED_SLASHES),
        'marker.php' => '<?php // inert marker',
    ],
]);

test('rejected uploads leave no extracted files behind', function () {
    $before = File::isDirectory(storage_path('app/modules_temp'))
        ? count(File::directories(storage_path('app/modules_temp')))
        : 0;

    uploadTraversalModule(traversalZip($this->workDir, [
        'module.json' => json_encode(['providers' => ['Modules\\../x\\P']]),
    ]));

    expect(count(File::directories(storage_path('app/modules_temp'))))->toBe($before);
});

test('namespace extraction only accepts a plain namespace segment', function () {
    $service = app(ModuleService::class);

    File::put($this->workDir.'/module.json', json_encode(['providers' => ['Modules\\Acme\\Providers\\AcmeServiceProvider']]));
    expect($service->extractNamespaceFolderFromPath($this->workDir))->toBe('Acme');

    // A traversal segment is returned as-is so moduleTargetPath() rejects the whole module.
    File::put($this->workDir.'/module.json', json_encode(['name' => 'x', 'providers' => [['nested'], 'Modules\\../evil\\P']]));
    $folder = $service->extractNamespaceFolderFromPath($this->workDir);
    expect($service->isValidModuleFolderName($folder))->toBeFalse();
    expect(fn () => $service->moduleTargetPath($folder))->toThrow(ModuleException::class);
});

test('module folder names are validated', function (string $name, bool $valid) {
    expect(app(ModuleService::class)->isValidModuleFolderName($name))->toBe($valid);
})->with([
    ['Acme', true],
    ['AcmeTools', true],
    ['Custom_Form2', true],
    ['../public/pwn', false],
    ['..', false],
    ['a/b', false],
    ['a\\b', false],
    ['.hidden', false],
    ['', false],
    ['1Module', false],
]);

test('temp paths are resolved before being trusted', function () {
    $service = app(ModuleService::class);
    $real = storage_path('app/modules_temp/'.uniqid('module_', true));
    File::ensureDirectoryExists($real);

    expect($service->isModuleTempPath($real))->toBeTrue()
        ->and($service->isModuleTempPath(storage_path('app/modules_temp/../../')))->toBeFalse()
        ->and($service->isModuleTempPath(storage_path('app/modules_temp/')))->toBeFalse()
        ->and($service->isModuleTempPath('/etc'))->toBeFalse()
        ->and($service->isModuleTempPath(null))->toBeFalse();

    File::deleteDirectory($real);
});

test('replace and cancel endpoints reject a traversing temp_path', function () {
    $victim = storage_path('app/testing-traversal/'.$this->marker.'/victim');
    File::ensureDirectoryExists($victim);
    File::put($victim.'/keep.txt', 'do not delete');

    $traversingPath = storage_path('app/modules_temp/../testing-traversal/'.$this->marker.'/victim');

    $this->actingAs($this->superadminUser)
        ->postJson(route('admin.modules.replace'), ['temp_path' => $traversingPath, 'existing_module_name' => 'acme'])
        ->assertStatus(400);

    $this->actingAs($this->superadminUser)
        ->postJson(route('admin.modules.cancel-replacement'), ['temp_path' => $traversingPath])
        ->assertOk();

    expect(File::exists($victim.'/keep.txt'))->toBeTrue();
});
