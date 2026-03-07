<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->langPath = resource_path('lang');
    $this->testLangFile = $this->langPath . '/test_lang.json';
});

afterEach(function () {
    if (File::exists($this->testLangFile)) {
        File::delete($this->testLangFile);
    }
});

test('translations sync adds missing keys to language files', function () {
    $enTranslations = json_decode(File::get($this->langPath . '/en.json'), true);

    // Create a test language file with only a few keys
    $partial = array_slice($enTranslations, 0, 5, true);
    File::put($this->testLangFile, json_encode($partial, JSON_PRETTY_PRINT));

    $this->artisan('translations:sync', ['--lang' => 'test_lang'])
        ->assertSuccessful();

    $synced = json_decode(File::get($this->testLangFile), true);
    expect(count($synced))->toBe(count($enTranslations));
});

test('translations sync removes stale keys when flag is passed', function () {
    $enTranslations = json_decode(File::get($this->langPath . '/en.json'), true);

    // Create a test file with all en keys plus a stale one
    $withStale = $enTranslations;
    $withStale['__stale_test_key_that_does_not_exist__'] = 'stale value';
    File::put($this->testLangFile, json_encode($withStale, JSON_PRETTY_PRINT));

    $this->artisan('translations:sync', ['--lang' => 'test_lang', '--remove-stale' => true])
        ->assertSuccessful();

    $synced = json_decode(File::get($this->testLangFile), true);
    expect($synced)->not->toHaveKey('__stale_test_key_that_does_not_exist__');
    expect(count($synced))->toBe(count($enTranslations));
});

test('translations sync dry run does not modify files', function () {
    $enTranslations = json_decode(File::get($this->langPath . '/en.json'), true);

    $partial = array_slice($enTranslations, 0, 3, true);
    File::put($this->testLangFile, json_encode($partial, JSON_PRETTY_PRINT));

    $this->artisan('translations:sync', ['--lang' => 'test_lang', '--dry-run' => true])
        ->assertSuccessful();

    $afterDryRun = json_decode(File::get($this->testLangFile), true);
    expect(count($afterDryRun))->toBe(3);
});

test('translations sync sorts keys alphabetically', function () {
    $enTranslations = json_decode(File::get($this->langPath . '/en.json'), true);

    // Create unsorted file
    $unsorted = ['Zebra' => 'Zebra', 'Apple' => 'Apple'];
    $unsorted = array_merge($unsorted, $enTranslations);
    File::put($this->testLangFile, json_encode($unsorted, JSON_PRETTY_PRINT));

    $this->artisan('translations:sync', ['--lang' => 'test_lang', '--remove-stale' => true])
        ->assertSuccessful();

    $synced = json_decode(File::get($this->testLangFile), true);
    $keys = array_keys($synced);
    $sorted = $keys;
    sort($sorted);
    expect($keys)->toBe($sorted);
});

test('all language files have same key count as en.json', function () {
    $enCount = count(json_decode(File::get($this->langPath . '/en.json'), true));

    $langFiles = collect(File::glob($this->langPath . '/*.json'))
        ->filter(fn ($f) => pathinfo($f, PATHINFO_FILENAME) !== 'en');

    foreach ($langFiles as $file) {
        $code = pathinfo($file, PATHINFO_FILENAME);
        $count = count(json_decode(File::get($file), true));
        expect($count)->toBe($enCount, "Expected {$code}.json to have {$enCount} keys, got {$count}");
    }
});
