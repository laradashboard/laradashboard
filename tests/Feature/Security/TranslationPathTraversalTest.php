<?php

declare(strict_types=1);

test('translation endpoint returns only the requested language file', function () {
    $response = $this->getJson(route('api.translations.show', ['lang' => 'en']));

    $response->assertOk();
    expect($response->json())->toBeArray()->not->toHaveKey('release_date');
});

test('translation endpoint rejects path traversal', function (string $lang) {
    $response = $this->getJson('/api/translations/'.$lang);

    $response->assertNotFound();
    expect($response->getContent())->not->toContain('laradashboard/laradashboard');
    expect($response->json('release_date'))->toBeNull();
})->with([
    '..%2F..%2Fversion',
    '..%2F..%2Fcomposer',
    '%2e%2e%2f%2e%2e%2fversion',
    '..%5C..%5Cversion',
    '..%5C..%5Ccomposer',
    '%2e%2e%5c%2e%2e%5cversion',
    '..%252F..%252Fversion',
]);
