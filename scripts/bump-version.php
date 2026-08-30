#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Bump version.json and package.json for a LaraDashboard release.
 *
 * Usage:
 *   php scripts/bump-version.php patch [YYYY-MM-DD]
 *   php scripts/bump-version.php minor
 *   php scripts/bump-version.php major
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php scripts/bump-version.php {patch|minor|major} [release-date]\n");
    exit(1);
}

$root = dirname(__DIR__);
$bump = strtolower($argv[1]);
$releaseDate = $argv[2] ?? date('Y-m-d');

if (! in_array($bump, ['patch', 'minor', 'major'], true)) {
    fwrite(STDERR, "Invalid bump type: {$bump}\n");
    exit(1);
}

$versionFile = $root.'/version.json';
$packageFile = $root.'/package.json';

if (! file_exists($versionFile)) {
    fwrite(STDERR, "Missing version.json\n");
    exit(1);
}

$versionData = json_decode(file_get_contents($versionFile), true, 512, JSON_THROW_ON_ERROR);
$current = $versionData['version'] ?? '0.0.0';

if (! preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $current, $matches)) {
    fwrite(STDERR, "Invalid current version: {$current}\n");
    exit(1);
}

[$major, $minor, $patch] = [(int) $matches[1], (int) $matches[2], (int) $matches[3]];

$newVersion = match ($bump) {
    'major' => ($major + 1).'.0.0',
    'minor' => $major.'.'.($minor + 1).'.0',
    'patch' => $major.'.'.$minor.'.'.($patch + 1),
};

$versionData['version'] = $newVersion;
$versionData['release_date'] = $releaseDate;

file_put_contents(
    $versionFile,
    json_encode($versionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
);

if (file_exists($packageFile)) {
    $packageData = json_decode(file_get_contents($packageFile), true, 512, JSON_THROW_ON_ERROR);
    $packageData['version'] = $newVersion;
    file_put_contents(
        $packageFile,
        json_encode($packageData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
    );
}

updateChangelog($root, $newVersion, $releaseDate);
updateReadmeReleaseLink($root, $newVersion);

echo "version={$newVersion}\n";
echo "tag=v{$newVersion}\n";

function updateChangelog(string $root, string $version, string $releaseDate): void
{
    $changelog = $root.'/CHANGELOG.md';

    if (! file_exists($changelog)) {
        return;
    }

    $content = file_get_contents($changelog);
    $heading = "## v{$version} — {$releaseDate}";

    if (str_contains($content, $heading)) {
        return;
    }

    $insert = "{$heading}\n\n### Added\n- \n\n### Changed\n- \n\n### Fixed\n- \n\n";

    if (preg_match('/^(# .+\R+\R)/', $content, $matches)) {
        $content = $matches[1].$insert.$content;
    } else {
        $content = $insert.$content;
    }

    file_put_contents($changelog, $content);
}

function updateReadmeReleaseLink(string $root, string $version): void
{
    $readme = $root.'/README.md';

    if (! file_exists($readme)) {
        return;
    }

    $content = file_get_contents($readme);
    $content = preg_replace(
        '/(\[release-shield\]\]\()https:\/\/github\.com\/[^)]+\/releases\/tag\/v[^)]+(\))/',
        '$1https://github.com/laradashboard/laradashboard/releases/tag/v'.$version.'$2',
        $content,
        1
    ) ?? $content;

    file_put_contents($readme, $content);
}
