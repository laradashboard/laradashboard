<?php

/**
 * Mark module_statuses.json as skip-worktree so local module toggles
 * are not committed. Cross-platform replacement for protect-local-files.sh.
 *
 * Always exits 0 so composer install/update is never blocked by this helper.
 */

declare(strict_types=1);

$file = 'module_statuses.json';

$isGitRepo = static function (): bool {
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open(
        ['git', 'rev-parse', '--is-inside-work-tree'],
        $descriptors,
        $pipes,
        getcwd()
    );

    if (! is_resource($process)) {
        return false;
    }

    fclose($pipes[0]);
    $stdout = trim((string) stream_get_contents($pipes[1]));
    fclose($pipes[1]);
    fclose($pipes[2]);

    return proc_close($process) === 0 && $stdout === 'true';
};

if (! is_file($file) || ! $isGitRepo()) {
    fwrite(STDOUT, "[INFO] Skipping protection for {$file} (not found or not a Git repo)\n");
    exit(0);
}

$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$process = proc_open(
    ['git', 'update-index', '--skip-worktree', $file],
    $descriptors,
    $pipes,
    getcwd()
);

if (! is_resource($process)) {
    fwrite(STDOUT, "[WARN] Failed to start git for protecting {$file}\n");
    exit(0);
}

fclose($pipes[0]);
stream_get_contents($pipes[1]);
stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);

$code = proc_close($process);

if ($code === 0) {
    fwrite(STDOUT, "[INFO] Local protection applied to {$file}\n");
} else {
    fwrite(STDOUT, "[WARN] Could not apply skip-worktree to {$file}\n");
}

exit(0);
