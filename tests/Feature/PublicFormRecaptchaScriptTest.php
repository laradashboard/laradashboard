<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeAll(function () {
    $statusFile = dirname(__DIR__, 2) . '/modules_statuses.json';
    $GLOBALS['recaptchaScriptTestOriginalStatuses'] = file_get_contents($statusFile);
    $statuses = json_decode(file_get_contents($statusFile), true, 512, JSON_THROW_ON_ERROR);
    $statuses['laradashboard'] = true;
    file_put_contents($statusFile, json_encode($statuses, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

afterAll(function () {
    if (isset($GLOBALS['recaptchaScriptTestOriginalStatuses'])) {
        file_put_contents(
            dirname(__DIR__, 2) . '/modules_statuses.json',
            $GLOBALS['recaptchaScriptTestOriginalStatuses']
        );
    }
});

beforeEach(function () {
    $this->withoutVite();
});

test('frontend layout renders recaptcha script for livewire public forms', function () {
    Setting::query()->updateOrCreate(
        ['option_name' => 'recaptcha_site_key'],
        ['option_value' => '6Ltest-site-key', 'autoload' => true]
    );
    Setting::query()->updateOrCreate(
        ['option_name' => 'recaptcha_secret_key'],
        ['option_value' => '6Ltest-secret-key', 'autoload' => true]
    );
    Setting::query()->updateOrCreate(
        ['option_name' => 'recaptcha_enabled_pages'],
        ['option_value' => json_encode(['newsletter']), 'autoload' => true]
    );

    config([
        'settings.recaptcha_site_key' => '6Ltest-site-key',
        'settings.recaptcha_secret_key' => '6Ltest-secret-key',
        'settings.recaptcha_enabled_pages' => json_encode(['newsletter']),
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('https://www.google.com/recaptcha/api.js?render=6Ltest-site-key', false);
    $response->assertSee('ldExecuteLivewireRecaptcha', false);
});
