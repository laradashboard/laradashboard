<?php

declare(strict_types=1);

use App\Services\EmailSubmissionValidator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'settings.auth_registration_email_domain_check_enabled' => '0',
        'settings.email_verification_enabled' => '0',
        'settings.email_verification_api_key' => '',
        'settings.email_verification_monthly_limit' => '100',
    ]);

    Cache::flush();
});

test('should validate is false when both local and api checks are disabled', function () {
    $validator = app(EmailSubmissionValidator::class);

    expect($validator->shouldValidate())->toBeFalse();
    expect($validator->validate('user@example.com'))->toBeNull();
});

test('should validate when local domain check is enabled', function () {
    config(['settings.auth_registration_email_domain_check_enabled' => '1']);

    $validator = app(EmailSubmissionValidator::class);

    expect($validator->shouldValidate())->toBeTrue();
});

test('should validate when api verification is enabled even if local setting is off', function () {
    config([
        'settings.auth_registration_email_domain_check_enabled' => '0',
        'settings.email_verification_enabled' => '1',
        'settings.email_verification_api_key' => 'test-key',
    ]);

    $validator = app(EmailSubmissionValidator::class);

    expect($validator->shouldValidate())->toBeTrue();
});

test('rejects invalid format without calling the api', function () {
    config([
        'settings.auth_registration_email_domain_check_enabled' => '1',
        'settings.email_verification_enabled' => '1',
        'settings.email_verification_api_key' => 'test-key',
    ]);

    Http::fake();

    $validator = app(EmailSubmissionValidator::class);

    expect($validator->validate('not-an-email'))->not->toBeNull();
    expect($validator->validate('user@nodot'))->not->toBeNull();

    Http::assertNothingSent();
});

test('rejects disposable domains without calling the api', function () {
    config([
        'settings.auth_registration_email_domain_check_enabled' => '1',
        'settings.email_verification_enabled' => '1',
        'settings.email_verification_api_key' => 'test-key',
    ]);

    Http::fake();

    $validator = app(EmailSubmissionValidator::class);

    expect($validator->validate('user@mailinator.com'))->not->toBeNull();

    Http::assertNothingSent();
});

test('calls api only after local checks pass', function () {
    config([
        'settings.auth_registration_email_domain_check_enabled' => '1',
        'settings.email_verification_enabled' => '1',
        'settings.email_verification_api_key' => 'test-key',
    ]);

    Http::fake([
        'emailvalidation.abstractapi.com/*' => Http::response([
            'deliverability' => 'DELIVERABLE',
            'is_disposable_email' => ['value' => false],
            'is_mx_found' => ['value' => true],
            'is_smtp_valid' => ['value' => true],
        ], 200),
    ]);

    $validator = app(EmailSubmissionValidator::class);

    expect($validator->validate('real@example.com'))->toBeNull();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'emailvalidation.abstractapi.com'));
});

test('rejects undeliverable api response after local checks pass', function () {
    config([
        'settings.auth_registration_email_domain_check_enabled' => '1',
        'settings.email_verification_enabled' => '1',
        'settings.email_verification_api_key' => 'test-key',
    ]);

    Http::fake([
        'emailvalidation.abstractapi.com/*' => Http::response([
            'deliverability' => 'UNDELIVERABLE',
            'is_disposable_email' => ['value' => false],
            'is_mx_found' => ['value' => true],
            'is_smtp_valid' => ['value' => false],
        ], 200),
    ]);

    $validator = app(EmailSubmissionValidator::class);

    expect($validator->validate('fake@example.com'))->not->toBeNull();
});

test('skips api when monthly quota is exhausted', function () {
    config([
        'settings.auth_registration_email_domain_check_enabled' => '1',
        'settings.email_verification_enabled' => '1',
        'settings.email_verification_api_key' => 'test-key',
        'settings.email_verification_monthly_limit' => '1',
    ]);

    Http::fake([
        'emailvalidation.abstractapi.com/*' => Http::response([
            'deliverability' => 'DELIVERABLE',
            'is_disposable_email' => ['value' => false],
            'is_mx_found' => ['value' => true],
            'is_smtp_valid' => ['value' => true],
        ], 200),
    ]);

    $validator = app(EmailSubmissionValidator::class);

    expect($validator->validate('one@example.com'))->toBeNull();

    Http::fake();

    expect($validator->validate('two@example.com'))->toBeNull();

    Http::assertNothingSent();
});

test('validation closure fails validator for disposable email', function () {
    config(['settings.auth_registration_email_domain_check_enabled' => '1']);

    $validator = app(EmailSubmissionValidator::class);
    $rules = ['email' => ['required', 'email', $validator->validationClosure()]];

    $result = validator(['email' => 'user@mailinator.com'], $rules);

    expect($result->fails())->toBeTrue();
});
