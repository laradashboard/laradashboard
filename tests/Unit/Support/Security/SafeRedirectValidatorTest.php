<?php

declare(strict_types=1);

use App\Support\Security\SafeRedirectValidator;

describe('SafeRedirectValidator', function () {
    beforeEach(function () {
        config(['app.url' => 'http://localhost']);
        $this->validator = new SafeRedirectValidator();
    });

    test('allows relative application paths', function () {
        expect($this->validator->sanitize('/admin/modules'))->toBe('/admin/modules');
    });

    test('rejects protocol-relative URLs', function () {
        expect($this->validator->sanitize('//evil.test/steal'))->toBeNull();
    });

    test('rejects external absolute URLs', function () {
        expect($this->validator->sanitize('https://evil.test/steal'))->toBeNull();
    });

    test('allows same-host http URLs outside production', function () {
        expect($this->validator->sanitize('http://localhost/admin/settings'))->toBe('http://localhost/admin/settings');
    });

    test('allows same-host https URLs', function () {
        expect($this->validator->sanitize('https://localhost/dashboard'))->toBe('https://localhost/dashboard');
    });

    test('requires https for same-host URLs in production', function () {
        $previous = app()->environment();
        app()->detectEnvironment(fn () => 'production');

        try {
            expect($this->validator->sanitize('http://localhost/admin'))->toBeNull();
            expect($this->validator->sanitize('https://localhost/admin'))->toBe('https://localhost/admin');
        } finally {
            app()->detectEnvironment(fn () => $previous);
        }
    });

    test('rejects dangerous schemes', function () {
        expect($this->validator->sanitize('javascript:alert(1)'))->toBeNull();
        expect($this->validator->sanitize('data:text/html,test'))->toBeNull();
    });

    test('returns null for blank input', function () {
        expect($this->validator->sanitize(null))->toBeNull();
        expect($this->validator->sanitize('   '))->toBeNull();
    });
});
