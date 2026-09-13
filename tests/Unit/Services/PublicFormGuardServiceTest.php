<?php

declare(strict_types=1);

use App\Services\PublicFormGuardService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    config()->set('settings.auth_registration_honeypot_enabled', '1');
    config()->set('settings.auth_registration_email_domain_check_enabled', '1');
});

test('merge rules includes honeypot field when enabled', function () {
    $guard = app(PublicFormGuardService::class);

    $rules = $guard->mergeRules(['email' => ['required', 'email']], emailFields: ['email']);

    expect($rules)->toHaveKey('company_website');
    expect($rules)->toHaveKey('email');
});

test('honeypot rule rejects filled value', function () {
    $guard = app(PublicFormGuardService::class);
    $rules = $guard->honeypotRules();

    $validator = validator(['company_website' => 'http://spam.test'], $rules);

    expect($validator->fails())->toBeTrue();
});

test('spam text rule rejects graph org payloads', function () {
    $guard = app(PublicFormGuardService::class);
    $rules = $guard->appendSpamTextRules([], ['message']);

    $validator = validator(['message' => 'Claim BTC at graph.org/scam'], $rules);

    expect($validator->fails())->toBeTrue();
});

test('assert recaptcha passes when page is disabled', function () {
    config()->set('settings.recaptcha_site_key', '');
    config()->set('settings.recaptcha_secret_key', '');

    $guard = app(PublicFormGuardService::class);

    $guard->assertRecaptcha(null, 'public_form');

    expect(true)->toBeTrue();
});

test('append email rule rejects disposable domains when validation is enabled', function () {
    $guard = app(PublicFormGuardService::class);
    $rules = $guard->appendEmailRule(['email' => ['required', 'email']], 'email');

    $validator = validator(['email' => 'user@mailinator.com'], $rules);

    expect($validator->fails())->toBeTrue();
});

test('emails fail advanced validation for disposable domains', function () {
    $guard = app(PublicFormGuardService::class);

    expect($guard->emailsFailAdvancedValidation(['user@mailinator.com']))->toBeTrue();
    expect($guard->emailsFailAdvancedValidation(['user@example.com']))->toBeFalse();
});

test('merge rules can skip advanced email validation', function () {
    $guard = app(PublicFormGuardService::class);

    $withAdvanced = $guard->mergeRules(['email' => ['required', 'email']], emailFields: ['email']);
    $withoutAdvanced = $guard->mergeRules(
        ['email' => ['required', 'email']],
        emailFields: ['email'],
        includeAdvancedEmailValidation: false,
    );

    $validatorWith = validator(['email' => 'user@mailinator.com'], $withAdvanced);
    $validatorWithout = validator(['email' => 'user@mailinator.com'], $withoutAdvanced);

    expect($validatorWith->fails())->toBeTrue();
    expect($validatorWithout->fails())->toBeFalse();
});

test('append email rule skips extra checks when validation is disabled', function () {
    config(['settings.auth_registration_email_domain_check_enabled' => '0']);

    $guard = app(PublicFormGuardService::class);
    $rules = $guard->appendEmailRule(['email' => ['required', 'email']], 'email');

    $validator = validator(['email' => 'user@mailinator.com'], $rules);

    expect($validator->fails())->toBeFalse();
});

test('assert recaptcha throws when enabled and token missing', function () {
    config()->set('settings.recaptcha_site_key', 'site-key');
    config()->set('settings.recaptcha_secret_key', 'secret-key');
    config()->set('settings.recaptcha_enabled_pages', json_encode(['public_form']));

    $guard = app(PublicFormGuardService::class);

    $guard->assertRecaptcha(null, 'public_form');
})->throws(ValidationException::class);
