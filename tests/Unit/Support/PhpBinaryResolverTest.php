<?php

declare(strict_types=1);

use App\Support\PhpBinaryResolver;

beforeEach(fn () => PhpBinaryResolver::flush());
afterEach(fn () => PhpBinaryResolver::flush());

it('uses the configured php cli binary when set', function () {
    config(['laradashboard.php_cli_binary' => '/opt/alt/php84/usr/bin/php']);

    expect((new PhpBinaryResolver())->resolve())->toBe('/opt/alt/php84/usr/bin/php');
});

it('uses PHP_BINARY when running under the cli sapi', function () {
    config(['laradashboard.php_cli_binary' => null]);

    expect((new PhpBinaryResolver())->resolve())->toBe(PHP_BINARY);
});

it('includes version-specific host paths in candidates', function () {
    $compact = PHP_MAJOR_VERSION.PHP_MINOR_VERSION;
    $dotted = PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;

    expect((new PhpBinaryResolver())->candidates())
        ->toContain("/opt/alt/php{$compact}/usr/bin/php")
        ->toContain("/opt/cpanel/ea-php{$compact}/root/usr/bin/php")
        ->toContain("/usr/bin/php{$dotted}");
});

it('only accepts binaries matching the running php version', function () {
    $resolver = new class () extends PhpBinaryResolver {
        public function check(string $binary): bool
        {
            return $this->matchesCurrentVersion($binary);
        }
    };

    expect($resolver->check(PHP_BINARY))->toBeTrue()
        ->and($resolver->check('/nonexistent/php'))->toBeFalse();
});
