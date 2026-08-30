<?php

declare(strict_types=1);

use App\Services\UpgradeSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

pest()->use(RefreshDatabase::class);

test('core snapshot command writes a json file', function () {
    $output = storage_path('app/upgrade-snapshots/test-snapshot.json');

    if (File::exists($output)) {
        File::delete($output);
    }

    $this->artisan('core:snapshot', ['--output' => $output])
        ->assertSuccessful();

    expect(File::exists($output))->toBeTrue();

    $snapshot = json_decode(File::get($output), true);

    expect($snapshot)
        ->toHaveKeys(['captured_at', 'core_version', 'modules', 'migration_count', 'demo_mode']);

    File::delete($output);
});

test('core snapshot command can print json to stdout', function () {
    $this->artisan('core:snapshot', ['--json' => true])
        ->assertSuccessful();
});

test('upgrade snapshot service verifies module preservation', function () {
    $service = app(UpgradeSnapshotService::class);

    $before = [
        'core_version' => '1.2.2',
        'modules' => [
            'crm' => [
                'name' => 'CRM',
                'folder' => 'CRM',
                'version' => '1.0.0',
                'enabled' => true,
            ],
        ],
        'migration_count' => 10,
    ];

    $report = $service->verify($before, '1.2.2');

    expect($report)->toHaveKeys(['passed', 'checks', 'snapshot']);
    expect(collect($report['checks'])->pluck('name'))->toContain('core_version');
});
