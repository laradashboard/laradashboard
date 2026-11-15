<?php

declare(strict_types=1);

namespace App\Support;

use Symfony\Component\Process\Process;

/**
 * Resolve a PHP CLI binary that matches the PHP version serving the current request.
 *
 * Under FPM/LiteSpeed (e.g. Hostinger, cPanel/CloudLinux), PHP_BINARY often points to
 * the SAPI binary or the server's *default* PHP — not the version selected for the site.
 * Spawning artisan with it can run an older PHP and trip Composer's platform check.
 */
class PhpBinaryResolver
{
    protected static ?string $resolved = null;

    public function resolve(): string
    {
        if (static::$resolved !== null) {
            return static::$resolved;
        }

        $configured = config('laradashboard.php_cli_binary');
        if (is_string($configured) && $configured !== '') {
            return static::$resolved = $configured;
        }

        if (PHP_SAPI === 'cli' && PHP_BINARY !== '') {
            return static::$resolved = PHP_BINARY;
        }

        foreach ($this->candidates() as $candidate) {
            if ($this->matchesCurrentVersion($candidate)) {
                return static::$resolved = $candidate;
            }
        }

        return static::$resolved = (PHP_BINARY !== '' ? PHP_BINARY : 'php');
    }

    public static function flush(): void
    {
        static::$resolved = null;
    }

    /**
     * @return array<int, string>
     */
    public function candidates(): array
    {
        $major = PHP_MAJOR_VERSION;
        $minor = PHP_MINOR_VERSION;
        $compact = $major.$minor;
        $dotted = $major.'.'.$minor;

        $candidates = [];

        if (PHP_BINARY !== '') {
            $dir = dirname(PHP_BINARY);
            $candidates[] = $dir.'/php';
            $candidates[] = $dir.'/php'.$dotted;
            $candidates[] = $dir.'/php'.$compact;
        }

        $candidates = array_merge($candidates, [
            // CloudLinux alt-php (Hostinger, many shared hosts)
            "/opt/alt/php{$compact}/usr/bin/php",
            // cPanel EasyApache
            "/opt/cpanel/ea-php{$compact}/root/usr/bin/php",
            // Plesk
            "/opt/plesk/php/{$dotted}/bin/php",
            // Debian/Ubuntu (ondrej PPA)
            "/usr/bin/php{$dotted}",
            "/usr/local/bin/php{$dotted}",
            "/usr/local/bin/php{$compact}",
            // Homebrew
            "/opt/homebrew/opt/php@{$dotted}/bin/php",
            "/usr/local/opt/php@{$dotted}/bin/php",
            // Generic fallbacks (validated by version check)
            '/usr/local/bin/php',
            '/usr/bin/php',
            'php',
        ]);

        return array_values(array_unique($candidates));
    }

    protected function matchesCurrentVersion(string $binary): bool
    {
        if (str_contains($binary, '/') && ! is_executable($binary)) {
            return false;
        }

        try {
            $process = new Process([$binary, '-r', 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;']);
            $process->setTimeout(10);
            $process->run();
        } catch (\Throwable) {
            return false;
        }

        return $process->isSuccessful()
            && trim($process->getOutput()) === PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;
    }
}
