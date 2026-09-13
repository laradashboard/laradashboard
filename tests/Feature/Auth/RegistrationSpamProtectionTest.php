<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Setting;
use App\Notifications\RegistrationWelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Setting::query()->updateOrCreate(
        ['option_name' => 'auth_enable_public_registration'],
        ['option_value' => '1']
    );

    config([
        'settings.auth_enable_public_registration' => '1',
        'settings.auth_enable_email_verification' => '1',
        'settings.auth_registration_honeypot_enabled' => '1',
        'settings.auth_registration_ip_limit_enabled' => '1',
        'settings.auth_registration_max_per_ip_per_day' => '3',
        'settings.auth_registration_email_domain_check_enabled' => '1',
        'settings.auth_defer_welcome_email_until_verified' => '1',
        'settings.recaptcha_site_key' => '',
        'settings.recaptcha_secret_key' => '',
        'settings.recaptcha_enabled_pages' => '[]',
        'mail.from.address' => 'noreply@example.com',
    ]);

    Cache::flush();
    Notification::fake();
});

function validRegistrationPayload(array $overrides = []): array
{
    return array_merge([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane.doe@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'company_website' => '',
    ], $overrides);
}

test('registration rejects spam names in first or last name', function () {
    $response = $this->post('/register', validRegistrationPayload([
        'first_name' => '+2.84567197 BTC. GET -> graph.org/test',
        'last_name' => 'Doe',
    ]));

    expect($response->status())->toBeIn([302, 403]);
    $this->assertGuest();
    expect(User::where('email', 'jane.doe@example.com')->exists())->toBeFalse();
});

test('registration rejects filled honeypot field', function () {
    $response = $this->from('/register')->post('/register', validRegistrationPayload([
        'company_website' => 'https://bot-filled-this.example',
    ]));

    $response->assertRedirect('/register');
    $response->assertSessionHasErrors('company_website');
    $this->assertGuest();
});

test('registration enforces per ip daily limit', function () {
    config(['settings.auth_registration_max_per_ip_per_day' => '1']);

    $this->post('/register', validRegistrationPayload([
        'email' => 'first@example.com',
    ]))->assertRedirect();

    auth()->logout();

    $response = $this->from('/register')->post('/register', validRegistrationPayload([
        'email' => 'second@example.com',
    ]));

    $response->assertRedirect('/register');
    $response->assertSessionHasErrors('email');
    expect(User::where('email', 'second@example.com')->exists())->toBeFalse();
});

test('legitimate registration succeeds and skips welcome email when verification is required', function () {
    $response = $this->post('/register', validRegistrationPayload());

    $response->assertRedirect();
    $this->assertAuthenticated();

    $user = User::where('email', 'jane.doe@example.com')->first();
    expect($user)->not->toBeNull();

    Notification::assertNotSentTo($user, RegistrationWelcomeNotification::class);
});

test('recaptcha register alias is enabled when legacy registration key is stored', function () {
    Config::set('settings.recaptcha_site_key', 'site-key');
    Config::set('settings.recaptcha_secret_key', 'secret-key');
    Config::set('settings.recaptcha_enabled_pages', json_encode(['registration']));

    $service = app(\App\Services\RecaptchaService::class);

    expect($service->isEnabledForPage('register'))->toBeTrue();
    expect($service->isEnabledForPage('registration'))->toBeTrue();
});

test('registration rejects disposable email domains', function () {
    $response = $this->from('/register')->post('/register', validRegistrationPayload([
        'email' => 'spammer@mailinator.com',
    ]));

    $response->assertRedirect('/register');
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
    expect(User::where('email', 'spammer@mailinator.com')->exists())->toBeFalse();
});

test('disposable email domains are allowed when the check is disabled', function () {
    config(['settings.auth_registration_email_domain_check_enabled' => '0']);

    $response = $this->post('/register', validRegistrationPayload([
        'email' => 'spammer@mailinator.com',
    ]));

    $response->assertRedirect();
    expect(User::where('email', 'spammer@mailinator.com')->exists())->toBeTrue();
});

test('registration guard service flags disposable domains and known hook-added domains', function () {
    $service = app(\App\Services\Auth\RegistrationGuardService::class);

    expect($service->isDisposableEmailDomain('user@mailinator.com'))->toBeTrue();
    expect($service->isDisposableEmailDomain('user@example.com'))->toBeFalse();
    expect($service->isDisposableEmailDomain('not-an-email'))->toBeFalse();
});

test('registration emails are queued instead of sent inline', function () {
    // Guards against a suspended/unreachable mail account blocking registration:
    // both notifications must implement ShouldQueue so delivery happens off-request.
    expect(new \App\Notifications\RegistrationWelcomeNotification())
        ->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);

    expect(new \App\Notifications\CustomVerifyEmailNotification())
        ->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
});

test('welcome email is sent when verification is disabled', function () {
    config([
        'settings.auth_enable_email_verification' => '0',
        'settings.auth_defer_welcome_email_until_verified' => '1',
    ]);

    $this->post('/register', validRegistrationPayload([
        'email' => 'welcome@example.com',
    ]))->assertRedirect();

    Notification::assertSentTo(
        User::where('email', 'welcome@example.com')->first(),
        RegistrationWelcomeNotification::class
    );
});
