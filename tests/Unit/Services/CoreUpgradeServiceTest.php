<?php

declare(strict_types=1);

use App\Services\BackupService;
use App\Services\CoreUpgradeService;
use Illuminate\Support\Facades\File;

class TestableCoreUpgradeService extends CoreUpgradeService
{
    public function __construct(
        BackupService $backupService,
        private readonly string $rootPath,
    ) {
        parent::__construct($backupService);
    }

    public function callValidateVendorDirectory(string $vendorPath): bool
    {
        return $this->validateVendorDirectory($vendorPath);
    }

    public function callSwapVendorDirectory(string $sourceVendorPath): bool
    {
        return $this->swapVendorDirectory($sourceVendorPath);
    }

    protected function getVendorPath(): string
    {
        return $this->rootPath.'/vendor';
    }

    protected function getVendorStagingPath(): string
    {
        return $this->rootPath.'/vendor-staging';
    }

    protected function getVendorBackupPath(): string
    {
        return $this->rootPath.'/vendor-backup-test';
    }

    protected function getVendorBackupGlobPattern(): string
    {
        return $this->rootPath.'/vendor-backup-*';
    }
}

function createMinimalVendorTree(string $root): void
{
    File::ensureDirectoryExists($root.'/composer');
    File::put($root.'/autoload.php', '<?php');
    File::put($root.'/composer/autoload_classmap.php', '<?php');
    File::ensureDirectoryExists($root.'/livewire/livewire/src/Mechanisms/ExtendBlade');
    File::put(
        $root.'/livewire/livewire/src/Mechanisms/ExtendBlade/ExtendedCompilerEngine.php',
        '<?php'
    );
}

afterEach(function () {
    if (isset($this->tempRoot) && File::isDirectory($this->tempRoot)) {
        File::deleteDirectory($this->tempRoot);
    }
});

test('validate vendor directory rejects incomplete vendor trees', function () {
    $this->tempRoot = sys_get_temp_dir().'/ld-upgrade-test-'.uniqid();
    File::ensureDirectoryExists($this->tempRoot.'/incomplete-vendor');
    File::put($this->tempRoot.'/incomplete-vendor/autoload.php', '<?php');

    $service = new TestableCoreUpgradeService(app(BackupService::class), $this->tempRoot);

    expect($service->callValidateVendorDirectory($this->tempRoot.'/incomplete-vendor'))->toBeFalse();
});

test('validate vendor directory accepts a complete vendor tree', function () {
    $this->tempRoot = sys_get_temp_dir().'/ld-upgrade-test-'.uniqid();
    $vendorPath = $this->tempRoot.'/complete-vendor';
    createMinimalVendorTree($vendorPath);

    $service = new TestableCoreUpgradeService(app(BackupService::class), $this->tempRoot);

    expect($service->callValidateVendorDirectory($vendorPath))->toBeTrue();
});

test('swap vendor directory stages validates and replaces the live vendor folder', function () {
    $this->tempRoot = sys_get_temp_dir().'/ld-upgrade-test-'.uniqid();

    $currentVendor = $this->tempRoot.'/vendor';
    $sourceVendor = $this->tempRoot.'/vendor-source';

    createMinimalVendorTree($currentVendor);
    createMinimalVendorTree($sourceVendor);
    File::put($currentVendor.'/marker-old.txt', 'old');
    File::put($sourceVendor.'/marker-new.txt', 'new');

    $service = new TestableCoreUpgradeService(app(BackupService::class), $this->tempRoot);

    expect($service->callSwapVendorDirectory($sourceVendor))->toBeTrue()
        ->and(File::exists($currentVendor.'/marker-new.txt'))->toBeTrue()
        ->and(File::exists($currentVendor.'/marker-old.txt'))->toBeFalse()
        ->and(File::isDirectory($this->tempRoot.'/vendor-staging'))->toBeFalse()
        ->and(glob($this->tempRoot.'/vendor-backup-*'))->toBeEmpty();
});

test('swap vendor directory rejects invalid source vendor without touching live vendor', function () {
    $this->tempRoot = sys_get_temp_dir().'/ld-upgrade-test-'.uniqid();

    $currentVendor = $this->tempRoot.'/vendor';
    $invalidSource = $this->tempRoot.'/vendor-source';

    createMinimalVendorTree($currentVendor);
    File::ensureDirectoryExists($invalidSource);
    File::put($invalidSource.'/autoload.php', '<?php');
    File::put($currentVendor.'/marker-old.txt', 'old');

    $service = new TestableCoreUpgradeService(app(BackupService::class), $this->tempRoot);

    expect($service->callSwapVendorDirectory($invalidSource))->toBeFalse()
        ->and(File::exists($currentVendor.'/marker-old.txt'))->toBeTrue()
        ->and(File::isDirectory($this->tempRoot.'/vendor-staging'))->toBeFalse();
});

test('create backup includes vendor for upgrade safety', function () {
    $backupService = Mockery::mock(BackupService::class)->makePartial();
    $backupService->shouldReceive('createBackupWithOptions')
        ->once()
        ->with('core_with_modules', false, true)
        ->andReturn('/tmp/backup.zip');

    expect($backupService->createBackup())->toBe('/tmp/backup.zip');
});

test('core upgrade service create backup delegates with vendor included', function () {
    $backupService = Mockery::mock(BackupService::class)->makePartial();
    $backupService->shouldReceive('createBackupWithOptions')
        ->once()
        ->with('core_with_modules', false, true)
        ->andReturn('/tmp/fake-backup.zip');

    $service = new CoreUpgradeService($backupService);

    expect($service->createBackup())->toBe('/tmp/fake-backup.zip');
});
