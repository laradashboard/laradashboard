<?php

declare(strict_types=1);

use App\Services\Emails\EmailVariable;

it('appends UTM parameters to regular links', function () {
    $emailVariable = new EmailVariable();
    $content = '<a href="https://example.com/page">Click here</a>';

    $result = $emailVariable->appendUtmParametersToLinks($content, 'newsletter', 'email');

    expect($result)->toContain('utm_source=newsletter');
    expect($result)->toContain('utm_medium=email');
});

it('does not append UTM parameters to signed URLs', function () {
    $emailVariable = new EmailVariable();
    $signedUrl = 'https://example.com/email/verify/1/abc123?expires=1234567890&signature=somehashvalue';
    $content = '<a href="' . $signedUrl . '">Verify Email</a>';

    $result = $emailVariable->appendUtmParametersToLinks($content, 'newsletter', 'email');

    expect($result)->not->toContain('utm_source');
    expect($result)->toContain($signedUrl);
});

it('appends UTM to regular links but skips signed URLs in same content', function () {
    $emailVariable = new EmailVariable();
    $signedUrl = 'https://example.com/email/verify/1/abc?expires=123&signature=hash';
    $content = '<a href="' . $signedUrl . '">Verify</a> <a href="https://example.com/home">Home</a>';

    $result = $emailVariable->appendUtmParametersToLinks($content, 'newsletter', 'email');

    expect($result)->toContain($signedUrl);
    expect($result)->toContain('https://example.com/home?utm_source=newsletter');
});
