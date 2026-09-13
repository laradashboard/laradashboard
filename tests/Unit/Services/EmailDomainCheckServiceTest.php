<?php

declare(strict_types=1);

use App\Services\EmailDomainCheckService;

test('detects known disposable email domains', function () {
    $service = app(EmailDomainCheckService::class);

    expect($service->isDisposableEmailDomain('user@mailinator.com'))->toBeTrue();
    expect($service->isDisposableEmailDomain('user@yopmail.com'))->toBeTrue();
    expect($service->isDisposableEmailDomain('user@example.com'))->toBeFalse();
    expect($service->isDisposableEmailDomain('not-an-email'))->toBeFalse();
});

test('domain accepts mail check is skipped (assumed true) during automated tests', function () {
    $service = app(EmailDomainCheckService::class);

    // Real DNS lookups would be flaky/unavailable in CI, so this is bypassed
    // for any test run — including a made-up domain that would normally fail.
    expect($service->domainAcceptsMail('user@this-domain-does-not-exist-xyz123.test'))->toBeTrue();
});

test('isPlausiblyDeliverable rejects disposable domains regardless of MX bypass', function () {
    $service = app(EmailDomainCheckService::class);

    expect($service->isPlausiblyDeliverable('user@mailinator.com'))->toBeFalse();
    expect($service->isPlausiblyDeliverable('user@example.com'))->toBeTrue();
});

test('isValidFormat rejects obvious junk addresses', function () {
    $service = app(EmailDomainCheckService::class);

    expect($service->isValidFormat(''))->toBeFalse();
    expect($service->isValidFormat('not-an-email'))->toBeFalse();
    expect($service->isValidFormat('@example.com'))->toBeFalse();
    expect($service->isValidFormat('user@'))->toBeFalse();
    expect($service->isValidFormat('user@nodot'))->toBeFalse();
    expect($service->isValidFormat('user..name@example.com'))->toBeFalse();
    expect($service->isValidFormat('.user@example.com'))->toBeFalse();
    expect($service->isValidFormat('user@example.'))->toBeFalse();
    expect($service->isValidFormat('user@example.com'))->toBeTrue();
});

test('isPlausiblyDeliverable rejects invalid format before dns checks', function () {
    $service = app(EmailDomainCheckService::class);

    expect($service->isPlausiblyDeliverable('fghfgh@srfdsdf.cgv'))->toBeTrue(); // format ok, dns bypassed in tests
    expect($service->isPlausiblyDeliverable('user@nodot'))->toBeFalse();
});
