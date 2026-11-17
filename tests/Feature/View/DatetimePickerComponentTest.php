<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;

test('the datetime picker prefills today when empty by default', function () {
    Carbon::setTestNow('2026-11-16 10:00:00');

    $html = (string) $this->blade('<x-inputs.datetime-picker name="due" :enableTime="false" dateFormat="Y-m-d" />');

    expect($html)->toContain('value="2026-11-16"');
});

test('optional dates stay empty when defaultToNow is off', function () {
    Carbon::setTestNow('2026-11-16 10:00:00');

    $html = (string) $this->blade('<x-inputs.datetime-picker name="dob" :enableTime="false" dateFormat="Y-m-d" :defaultToNow="false" />');

    expect($html)->not->toContain('value="2026-11-16"')
        ->and($html)->toContain('name="dob"');
});
