<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;

/**
 * Force Vite out of "hot" mode so the component takes the built/manifest path,
 * regardless of whether a local dev server left a public/hot file behind.
 */
function forceViteNotHot(): void
{
    Vite::useHotFile(base_path('storage/framework/testing/no-such-hot-'.uniqid()));
}

function renderModuleStyles(array $entrypoints, string $build): string
{
    return Blade::render(
        '<x-module-styles :entrypoints="$entrypoints" :build="$build" />',
        ['entrypoints' => $entrypoints, 'build' => $build]
    );
}

function writeModuleManifest(string $build, string $file, string $relativeManifestPath): string
{
    $dir = public_path($build);
    File::ensureDirectoryExists(dirname("{$dir}/{$relativeManifestPath}"));
    File::put("{$dir}/{$relativeManifestPath}", json_encode([
        'modules/Test/resources/assets/css/app.css' => [
            'file' => $file,
            'src' => 'modules/Test/resources/assets/css/app.css',
            'isEntry' => true,
        ],
    ]));

    return $dir;
}

it('renders nothing and does not throw when a module bundle is not built', function () {
    forceViteNotHot();
    config()->set('app.debug', false);

    $html = renderModuleStyles(
        ['modules/Ghost/resources/assets/css/app.css'],
        'build-ghost-'.uniqid()
    );

    // The reported bug: a missing manifest threw ViteManifestNotFoundException and
    // 500'd the whole admin panel. It must now degrade to an empty, harmless render.
    expect(trim($html))->toBe('');
});

it('renders the compiled stylesheet when a Laravel-style manifest exists', function () {
    forceViteNotHot();
    $build = 'build-teststyles-'.uniqid();
    $dir = writeModuleManifest($build, 'assets/app-root.css', 'manifest.json');

    try {
        $html = renderModuleStyles(['modules/Test/resources/assets/css/app.css'], $build);

        expect($html)
            ->toContain("/{$build}/assets/app-root.css")
            ->toContain('rel="stylesheet"');
    } finally {
        File::deleteDirectory($dir);
    }
});

it('renders when only a Vite 5 style .vite/manifest.json exists', function () {
    forceViteNotHot();
    $build = 'build-testvite-'.uniqid();
    $dir = writeModuleManifest($build, 'assets/app-vite.css', '.vite/manifest.json');

    try {
        $html = renderModuleStyles(['modules/Test/resources/assets/css/app.css'], $build);

        expect($html)->toContain("/{$build}/assets/app-vite.css");
    } finally {
        File::deleteDirectory($dir);
    }
});

it('emits a developer hint in debug mode when assets are missing', function () {
    forceViteNotHot();
    config()->set('app.debug', true);
    $build = 'build-missing-'.uniqid();

    $html = renderModuleStyles(['modules/Test/resources/assets/css/app.css'], $build);

    expect($html)
        ->toContain('[module-styles]')
        ->toContain($build);
});
