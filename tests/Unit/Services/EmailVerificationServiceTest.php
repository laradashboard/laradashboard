<?php

declare(strict_types=1);

use App\Services\EmailVerificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'settings.email_verification_enabled' => '0',
        'settings.email_verification_api_key' => '',
        'settings.email_verification_monthly_limit' => '100',
    ]);

    Cache::flush();
});

test('falls back to the free local check when disabled', function () {
    $service = app(EmailVerificationService::class);

    expect($service->isEnabled())->toBeFalse();
    expect($service->isDeliverable('user@example.com'))->toBeTrue();
    expect($service->isDeliverable('user@mailinator.com'))->toBeFalse();

    Http::assertNothingSent();
});

test('falls back to the free local check when enabled but no api key is set', function () {
    config(['settings.email_verification_enabled' => '1']);

    $service = app(EmailVerificationService::class);

    expect($service->isEnabled())->toBeFalse();
    expect($service->isDeliverable('user@example.com'))->toBeTrue();
});

test('calls AbstractAPI and accepts a deliverable response', function () {
    config([
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

    $service = app(EmailVerificationService::class);

    expect($service->isDeliverable('real@example.com'))->toBeTrue();
    expect($service->usageThisMonth())->toBe(1);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'emailvalidation.abstractapi.com'));
});

test('calls AbstractAPI and rejects an undeliverable response', function () {
    config([
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

    $service = app(EmailVerificationService::class);

    expect($service->isDeliverable('fake@example.com'))->toBeFalse();
});

test('falls back to local check when the API call fails', function () {
    config([
        'settings.email_verification_enabled' => '1',
        'settings.email_verification_api_key' => 'test-key',
    ]);

    Http::fake([
        'emailvalidation.abstractapi.com/*' => Http::response([], 500),
    ]);

    $service = app(EmailVerificationService::class);

    // API errored, but the domain is a legitimate one, so the local
    // MX/disposable-domain fallback should still allow it through.
    expect($service->isDeliverable('user@example.com'))->toBeTrue();
});

test('stops calling the API once the monthly quota is exhausted and falls back locally', function () {
    config([
        'settings.email_verification_enabled' => '1',
        'settings.email_verification_api_key' => 'test-key',
        'settings.email_verification_monthly_limit' => '2',
    ]);

    Http::fake([
        'emailvalidation.abstractapi.com/*' => Http::response([
            'deliverability' => 'DELIVERABLE',
            'is_disposable_email' => ['value' => false],
            'is_mx_found' => ['value' => true],
            'is_smtp_valid' => ['value' => true],
        ], 200),
    ]);

    $service = app(EmailVerificationService::class);

    expect($service->isDeliverable('one@example.com'))->toBeTrue();
    expect($service->isDeliverable('two@example.com'))->toBeTrue();
    expect($service->usageThisMonth())->toBe(2);
    expect($service->hasQuotaRemaining())->toBeFalse();

    Http::fake(); // reset the recorder so we can assert nothing new is sent from here

    expect($service->isDeliverable('three@mailinator.com'))->toBeFalse();
    expect($service->isDeliverable('four@example.com'))->toBeTrue();

    Http::assertNothingSent();
    // Usage counter (persisted in cache, independent of Http::fake()'s recorder)
    // should not have grown past the quota — no further API calls were made.
    expect($service->usageThisMonth())->toBe(2);
});

test('isDeliverable does not call the api for disposable or invalid format emails', function () {
    config([
        'settings.email_verification_enabled' => '1',
        'settings.email_verification_api_key' => 'test-key',
    ]);

    Http::fake();

    $service = app(EmailVerificationService::class);

    expect($service->isDeliverable('user@mailinator.com'))->toBeFalse();
    expect($service->isDeliverable('not-an-email'))->toBeFalse();
    expect($service->isDeliverable('user@nodot'))->toBeFalse();

    Http::assertNothingSent();
});

test('a monthly limit of 0 means unlimited quota', function () {
    config([
        'settings.email_verification_enabled' => '1',
        'settings.email_verification_api_key' => 'test-key',
        'settings.email_verification_monthly_limit' => '0',
    ]);

    $service = app(EmailVerificationService::class);

    expect($service->getMonthlyLimit())->toBe(0);
    expect($service->hasQuotaRemaining())->toBeTrue();
});
