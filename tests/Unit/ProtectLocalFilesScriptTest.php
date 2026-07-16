<?php

declare(strict_types=1);

test('protect local files composer script uses php not bash', function () {
    $composer = json_decode(
        (string) file_get_contents(base_path('composer.json')),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $scripts = $composer['scripts']['protect-local-files'] ?? [];

    expect($scripts)->toContain('@php scripts/protect-local-files.php')
        ->and($scripts)->not->toContain('bash scripts/protect-local-files.sh');
});

test('protect local files php script exists and exits successfully', function () {
    $script = base_path('scripts/protect-local-files.php');

    expect(file_exists($script))->toBeTrue();

    $process = new Symfony\Component\Process\Process(
        [PHP_BINARY, $script],
        base_path()
    );
    $process->run();

    expect($process->isSuccessful())->toBeTrue()
        ->and($process->getOutput())->toContain('[INFO]');
});
