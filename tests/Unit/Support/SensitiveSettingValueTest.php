<?php

declare(strict_types=1);

use App\Support\Settings\SensitiveSettingValue;
use Illuminate\Support\Facades\Crypt;

test('credential-shaped option names are treated as sensitive', function () {
    expect(SensitiveSettingValue::isSensitive('email_verification_api_key'))->toBeTrue()
        ->and(SensitiveSettingValue::isSensitive('mail_password'))->toBeTrue()
        ->and(SensitiveSettingValue::isSensitive('recaptcha_secret_key'))->toBeTrue()
        ->and(SensitiveSettingValue::isSensitive('site_name'))->toBeFalse();
});

test('sensitive values are encrypted for storage and decrypted for use', function () {
    $stored = SensitiveSettingValue::prepareForStorage('mail_password', 'S3cr3tP@ss');

    expect($stored)->not->toBe('S3cr3tP@ss')
        ->and(SensitiveSettingValue::resolveStoredValue('mail_password', $stored))->toBe('S3cr3tP@ss');
});

test('masked submissions are skipped for storage', function () {
    expect(SensitiveSettingValue::prepareForStorage('mail_password', SensitiveSettingValue::MASK))->toBeNull()
        ->and(SensitiveSettingValue::prepareForStorage('mail_password', '********'))->toBeNull();
});

test('legacy plaintext secrets still resolve after encryption rollout', function () {
    expect(SensitiveSettingValue::resolveStoredValue('ai_openai_api_key', 'plain-legacy-key'))
        ->toBe('plain-legacy-key');
});

test('module licenses are hidden from the settings API surface', function () {
    expect(SensitiveSettingValue::isHiddenFromApi('module_licenses'))->toBeTrue();
});

test('api exposure masks non-empty secrets', function () {
    $encrypted = Crypt::encryptString('super-secret-key');

    expect(SensitiveSettingValue::exposeForApi('email_verification_api_key', $encrypted))
        ->toBe(SensitiveSettingValue::MASK);
});
