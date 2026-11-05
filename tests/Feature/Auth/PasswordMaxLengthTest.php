<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Setting::query()->updateOrCreate(
        ['option_name' => 'auth_enable_public_registration'],
        ['option_value' => '1']
    );

    config([
        'settings.auth_enable_public_registration' => '1',
        'settings.auth_registration_honeypot_enabled' => '0',
        'settings.auth_registration_ip_limit_enabled' => '0',
        'settings.auth_registration_email_domain_check_enabled' => '0',
        'settings.recaptcha_site_key' => '',
        'settings.recaptcha_secret_key' => '',
        'settings.recaptcha_enabled_pages' => '[]',
        'mail.from.address' => 'noreply@example.com',
    ]);

    Cache::flush();
    Notification::fake();
});

test('registration rejects a password longer than the maximum', function () {
    $password = str_repeat('a', User::PASSWORD_MAX_LENGTH + 1);

    $this->from('/register')->post('/register', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane.doe@example.com',
        'password' => $password,
        'password_confirmation' => $password,
        'company_website' => '',
    ])->assertSessionHasErrors('password');

    expect(User::where('email', 'jane.doe@example.com')->exists())->toBeFalse();
});

test('registration still accepts a password at the maximum length', function () {
    $password = str_repeat('a', User::PASSWORD_MAX_LENGTH);

    $this->post('/register', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane.doe@example.com',
        'password' => $password,
        'password_confirmation' => $password,
        'company_website' => '',
    ])->assertSessionDoesntHaveErrors('password');
});

test('login rejects an oversized password before hashing it', function () {
    $this->from('/login')->post('/login', [
        'email' => 'someone@example.com',
        'password' => str_repeat('a', User::PASSWORD_INPUT_MAX_LENGTH + 1),
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('the api login rejects an oversized password', function () {
    $this->postJson('/api/auth/login', [
        'email' => 'someone@example.com',
        'password' => str_repeat('a', User::PASSWORD_INPUT_MAX_LENGTH + 1),
    ])->assertJsonValidationErrors('password');
});
