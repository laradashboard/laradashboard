<?php

declare(strict_types=1);

use App\Services\SvgSanitizer;

test('sanitizer keeps a legitimate svg', function () {
    $sanitizer = new SvgSanitizer();

    $clean = $sanitizer->sanitize(<<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">
            <rect width="100" height="100" fill="red"/>
        </svg>
        SVG);

    expect($clean)->toContain('<svg')
        ->and($clean)->toContain('<rect')
        ->and($sanitizer->containsDangerousContent($clean))->toBeFalse();
});

test('sanitizer rejects malformed svg', function () {
    $sanitizer = new SvgSanitizer();

    $sanitizer->sanitize('<svg><unclosed');
})->throws(RuntimeException::class, 'malformed');

test('sanitizer rejects javascript urls', function () {
    $sanitizer = new SvgSanitizer();

    expect(fn () => $sanitizer->sanitize(<<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg">
            <a href="javascript:alert(1)"><rect width="10" height="10"/></a>
        </svg>
        SVG))->toThrow(RuntimeException::class, 'unsafe content');
});

test('sanitizer rejects embedded html objects', function () {
    $sanitizer = new SvgSanitizer();

    expect(fn () => $sanitizer->sanitize(<<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg">
            <foreignObject width="100" height="100">
                <body xmlns="http://www.w3.org/1999/xhtml"><script>alert(1)</script></body>
            </foreignObject>
        </svg>
        SVG))->toThrow(RuntimeException::class, 'unsafe content');
});
