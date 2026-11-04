<?php

declare(strict_types=1);

use App\Services\Modules\ModuleAutoloaderService;
use Composer\Autoload\ClassLoader;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Regression: a module bundling an older copy of a core package (e.g.
 * illuminate/database) must not shadow the core version. Composer prepends
 * every loader, which mixed two framework versions in one process and broke
 * migrations with "Relation::withConstraintsForNestedRelation does not exist".
 */
beforeEach(function () {
    $this->namespace = 'LdShadow'.Str::random(10);
    $this->root = storage_path('framework/testing/autoload-'.Str::random(8));

    // "Core" copy of the shared class, registered like the app's own Composer loader.
    $coreSrc = $this->root.'/core-src';
    File::ensureDirectoryExists($coreSrc);
    File::put($coreSrc.'/Shared.php', "<?php namespace {$this->namespace}; class Shared { public const VERSION = 'core'; }");

    $this->coreLoader = new ClassLoader();
    $this->coreLoader->addPsr4($this->namespace.'\\', $coreSrc.'/');
    $this->coreLoader->register(false);

    // Module with its own vendor/autoload.php that prepends, exactly like Composer's generated file.
    $moduleSrc = $this->root.'/modules/Fake/vendor/src';
    File::ensureDirectoryExists($moduleSrc);
    File::put($moduleSrc.'/Shared.php', "<?php namespace {$this->namespace}; class Shared { public const VERSION = 'module'; }");
    File::put($moduleSrc.'/OnlyInModule.php', "<?php namespace {$this->namespace}; class OnlyInModule { public const VERSION = 'module'; }");
    File::put($this->root.'/modules/Fake/vendor/autoload.php', <<<PHP
        <?php
        \$loader = new \\Composer\\Autoload\\ClassLoader();
        \$loader->addPsr4('{$this->namespace}\\\\', __DIR__.'/src/');
        \$loader->register(true);

        return \$loader;
        PHP);
});

afterEach(function () {
    $this->coreLoader->unregister();
    (new ModuleAutoloaderService($this->root.'/modules'))->getLoader('Fake')?->unregister();
    File::deleteDirectory($this->root);
});

test('core classes win over a module bundled copy', function () {
    $service = new ModuleAutoloaderService($this->root.'/modules');

    expect($service->register('Fake'))->toBeTrue();

    $shared = $this->namespace.'\\Shared';
    $onlyInModule = $this->namespace.'\\OnlyInModule';

    expect($shared::VERSION)->toBe('core')
        ->and($onlyInModule::VERSION)->toBe('module');

    $service->getLoader('Fake')?->unregister();
});

test('the static bootstrap registration also keeps core classes first', function () {
    $loaderCountBefore = count(spl_autoload_functions());

    ModuleAutoloaderService::registerAllStatic($this->root.'/modules');

    $functions = spl_autoload_functions();
    expect(count($functions))->toBe($loaderCountBefore + 1);

    $shared = $this->namespace.'\\Shared';
    expect($shared::VERSION)->toBe('core');

    // The module loader is the last one in the stack.
    $last = end($functions);
    expect($last[0] ?? null)->toBeInstanceOf(ClassLoader::class)
        ->and($last[0])->not->toBe($this->coreLoader);
    $last[0]->unregister();
});
