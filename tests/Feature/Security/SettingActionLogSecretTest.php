<?php

declare(strict_types=1);

use App\Models\ActionLog;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\User;
use App\Support\Settings\SensitiveSettingValue;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

/*
 * GHSA-29v7-6x3j-r97q: setting changes were written to the action log with their raw
 * values, so actionlog.view alone exposed API keys, passwords and OAuth secrets.
 */

const LEAK_TEST_SECRET = 'sk-live-SUPER-SECRET-OPENAI-KEY-abc123xyz';

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'actionlog.view', 'guard_name' => 'web']);

    $this->auditor = User::factory()->create();
    $this->auditor->givePermissionTo('actionlog.view');
});

test('saving a secret setting never writes its value to the action log', function (string $key) {
    Setting::updateOrCreate(['option_name' => $key], ['option_value' => LEAK_TEST_SECRET]);
    Setting::updateOrCreate(['option_name' => $key], ['option_value' => LEAK_TEST_SECRET . '-rotated']);
    Setting::where('option_name', $key)->first()->delete();

    $logs = ActionLog::where('data', 'like', '%' . $key . '%')->get();

    expect($logs)->toHaveCount(3);
    expect(ActionLog::where('data', 'like', '%sk-live%')->exists())->toBeFalse();
    expect(json_decode($logs->first()->data, true)['setting'])
        ->toMatchArray(['option_name' => $key, 'option_value' => SensitiveSettingValue::MASK]);
})->with([
    'openai key' => 'ai_openai_api_key',
    'mail password' => 'mail_password',
    'oauth client secret' => 'auth_social_github_client_secret',
    'verification api key' => 'email_verification_api_key',
]);

test('ordinary setting changes keep their value in the action log', function () {
    Setting::updateOrCreate(['option_name' => 'site_name'], ['option_value' => 'My Site']);

    $log = ActionLog::where('data', 'like', '%site_name%')->latest('id')->firstOrFail();

    expect(json_decode($log->data, true)['setting'])->toMatchArray(['option_name' => 'site_name', 'option_value' => 'My Site']);
});

test('an auditor with actionlog.view cannot read a secret through the action log api', function () {
    Setting::updateOrCreate(['option_name' => 'ai_openai_api_key'], ['option_value' => LEAK_TEST_SECRET]);
    $log = ActionLog::where('data', 'like', '%ai_openai_api_key%')->latest('id')->firstOrFail();

    Sanctum::actingAs($this->auditor);

    $this->getJson("/api/v1/action-logs/{$log->id}")->assertOk()->assertDontSee('sk-live', false);
    $this->getJson('/api/v1/action-logs')->assertOk()->assertDontSee('sk-live', false);
});

test('the upgrade migration redacts secrets already in the action log', function () {
    $leaked = ActionLog::create([
        'type' => 'updated',
        'title' => 'Setting updated by admin',
        'action_by' => $this->auditor->id,
        'data' => json_encode(['setting' => ['option_name' => 'ai_claude_api_key', 'option_value' => LEAK_TEST_SECRET]]),
    ]);
    $ordinary = ActionLog::create([
        'type' => 'updated',
        'title' => 'Setting updated by admin',
        'action_by' => $this->auditor->id,
        'data' => json_encode(['setting' => ['option_name' => 'site_name', 'option_value' => 'Keep me']]),
    ]);

    (require database_path('migrations/2026_11_08_000001_redact_secret_settings_in_action_logs.php'))->up();

    expect(DB::table('action_logs')->where('data', 'like', '%sk-live%')->exists())->toBeFalse()
        ->and(json_decode($leaked->fresh()->data, true)['setting']['option_value'])->toBe(SensitiveSettingValue::MASK)
        ->and(json_decode($ordinary->fresh()->data, true)['setting']['option_value'])->toBe('Keep me');
});
