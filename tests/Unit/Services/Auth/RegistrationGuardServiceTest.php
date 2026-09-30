<?php

declare(strict_types=1);

use App\Services\Auth\RegistrationGuardService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config([
        'settings.auth_registration_honeypot_enabled' => '1',
        'settings.auth_registration_ip_limit_enabled' => '1',
        'settings.auth_registration_max_per_ip_per_day' => '2',
        'settings.auth_defer_welcome_email_until_verified' => '1',
    ]);

    Cache::flush();
});

test('detects crypto spam names', function () {
    $guard = app(RegistrationGuardService::class);

    expect($guard->looksLikeSpamName('+2.84567197 BTC. GET -> graph.org/foo'))->toBeTrue();
    expect($guard->looksLikeSpamName('Transfer № L7104 from Coinbase'))->toBeTrue();
    expect($guard->looksLikeSpamName('Jane Doe'))->toBeFalse();
});

test('honeypot validation rule rejects filled honeypot field', function () {
    $guard = app(RegistrationGuardService::class);
    $rules = $guard->additionalValidationRules();

    $validator = validator(
        ['company_website' => 'https://spam.example'],
        $rules
    );

    expect($validator->fails())->toBeTrue();
});

test('ip limit blocks after threshold is reached', function () {
    $guard = app(RegistrationGuardService::class);

    expect($guard->hasExceededIpLimit('203.0.113.10'))->toBeFalse();

    $guard->recordRegistration('203.0.113.10');
    $guard->recordRegistration('203.0.113.10');

    expect($guard->hasExceededIpLimit('203.0.113.10'))->toBeTrue();
});

test('ip limit can be disabled via settings', function () {
    config(['settings.auth_registration_ip_limit_enabled' => '0']);

    $guard = app(RegistrationGuardService::class);

    $guard->recordRegistration('203.0.113.11');
    $guard->recordRegistration('203.0.113.11');
    $guard->recordRegistration('203.0.113.11');

    expect($guard->hasExceededIpLimit('203.0.113.11'))->toBeFalse();
});

test('ip limit reserve rejects when threshold would be exceeded', function () {
    $guard = app(RegistrationGuardService::class);

    expect($guard->reserveIpRegistrationSlot('203.0.113.12'))->toBeTrue();
    expect($guard->reserveIpRegistrationSlot('203.0.113.12'))->toBeTrue();
    expect($guard->reserveIpRegistrationSlot('203.0.113.12'))->toBeFalse();
    expect($guard->hasExceededIpLimit('203.0.113.12'))->toBeTrue();
});
