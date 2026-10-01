<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\User;
use App\Support\Settings\SensitiveSettingValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

function settingsViewer(): User
{
    $user = User::factory()->create();
    Permission::firstOrCreate(['name' => 'settings.view', 'guard_name' => 'web']);
    $user->givePermissionTo('settings.view');

    return $user;
}

beforeEach(function () {
    Setting::updateOrCreate(
        ['option_name' => 'site_name'],
        ['option_value' => 'Public Site Name']
    );

    Setting::updateOrCreate(
        ['option_name' => Setting::EMAIL_VERIFICATION_API_KEY],
        ['option_value' => SensitiveSettingValue::prepareForStorage(
            Setting::EMAIL_VERIFICATION_API_KEY,
            '21ab34cd-secret-abstract-key'
        ) ?? '']
    );

    Setting::updateOrCreate(
        ['option_name' => Setting::MAIL_PASSWORD],
        ['option_value' => SensitiveSettingValue::prepareForStorage(
            Setting::MAIL_PASSWORD,
            'S3cr3tMailPass'
        ) ?? '']
    );

    Setting::updateOrCreate(
        ['option_name' => 'module_licenses'],
        ['option_value' => SensitiveSettingValue::prepareForStorage(
            'module_licenses',
            json_encode(['site.test' => ['license_key' => 'LKEY-SECRET', 'purchase_code' => 'PCODE']])
        ) ?? '']
    );
});

test('settings api masks credential fields for settings.view users', function () {
    Sanctum::actingAs(settingsViewer());

    $response = $this->getJson('/api/v1/settings');

    $response->assertOk();

    $payload = collect($response->json('data'));

    expect($payload->firstWhere('option_name', 'site_name')['option_value'])->toBe('Public Site Name')
        ->and($payload->firstWhere('option_name', Setting::EMAIL_VERIFICATION_API_KEY)['option_value'])
        ->toBe(SensitiveSettingValue::MASK)
        ->and($payload->firstWhere('option_name', Setting::MAIL_PASSWORD)['option_value'])
        ->toBe(SensitiveSettingValue::MASK);

    expect($payload->pluck('option_name'))->not->toContain('module_licenses');
});

test('settings api show masks individual secret settings', function () {
    Sanctum::actingAs(settingsViewer());

    $this->getJson('/api/v1/settings/'.Setting::MAIL_PASSWORD)
        ->assertOk()
        ->assertJsonPath('data.option_value', SensitiveSettingValue::MASK);
});

test('module licenses are not readable via settings api show', function () {
    Sanctum::actingAs(settingsViewer());

    $this->getJson('/api/v1/settings/module_licenses')
        ->assertNotFound();
});

test('settings api update does not overwrite secrets with mask placeholders', function () {
    $manager = User::factory()->create();
    Permission::firstOrCreate(['name' => 'settings.edit', 'guard_name' => 'web']);
    $manager->givePermissionTo('settings.edit');

    Sanctum::actingAs($manager);

    $this->putJson('/api/v1/settings', [
        'settings' => [
            Setting::MAIL_PASSWORD => SensitiveSettingValue::MASK,
        ],
    ])->assertOk();

    $stored = Setting::where('option_name', Setting::MAIL_PASSWORD)->value('option_value');

    expect(SensitiveSettingValue::resolveStoredValue(Setting::MAIL_PASSWORD, $stored))
        ->toBe('S3cr3tMailPass');
});

test('settings api update cannot modify hidden module license blob', function () {
    $manager = User::factory()->create();
    Permission::firstOrCreate(['name' => 'settings.edit', 'guard_name' => 'web']);
    $manager->givePermissionTo('settings.edit');

    Sanctum::actingAs($manager);

    $this->putJson('/api/v1/settings', [
        'settings' => [
            'module_licenses' => '{"evil":true}',
        ],
    ])->assertOk();

    $json = SensitiveSettingValue::resolveStoredValue(
        'module_licenses',
        Setting::where('option_name', 'module_licenses')->value('option_value')
    );

    expect($json)->toContain('LKEY-SECRET')
        ->and($json)->not->toContain('evil');
});
