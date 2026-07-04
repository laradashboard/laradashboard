<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Vite;
use Illuminate\View\Component;

/**
 * Renders a module's compiled Vite assets (typically its scoped CSS bundle)
 * without crashing the page when that bundle has not been built yet.
 *
 * A module stylesheet is an optional enhancement: a freshly scaffolded or
 * not-yet-compiled module must never take down the whole admin panel with a
 * `ViteManifestNotFoundException`. This component therefore emits the Vite tags
 * only when they can actually be resolved:
 *
 *   1. The Vite dev server is running (a "hot" file is present), or
 *   2. A compiled manifest exists in `public/{build}/` — supporting both the
 *      Laravel-style `manifest.json` and the Vite 5+ `.vite/manifest.json`.
 *
 * Otherwise it renders nothing (plus a developer hint when `app.debug` is on).
 *
 * This is generic core infrastructure: it holds no knowledge of any specific
 * module. The caller supplies the entry points and the build directory.
 */
class ModuleStyles extends Component
{
    /** @var list<string> */
    public array $entrypoints;

    public string $build;

    public ?Htmlable $tags = null;

    public ?string $hint = null;

    /**
     * @param  array<int, string>|string  $entrypoints  Vite entry point(s), e.g. 'modules/Crm/resources/assets/css/app.css'
     * @param  string  $build  The module build directory under public/, e.g. 'build-crm'
     */
    public function __construct(array|string $entrypoints = [], string $build = '')
    {
        $this->entrypoints = array_values(array_filter((array) $entrypoints));
        $this->build = $build;

        $this->resolveTags();
    }

    /**
     * Resolve the Vite tags to render, degrading gracefully when the module
     * assets are not available.
     */
    protected function resolveTags(): void
    {
        if ($this->entrypoints === [] || $this->build === '') {
            return;
        }

        $vite = app(Vite::class);

        // Dev server running: let Vite serve entry points over HMR. The build
        // directory is irrelevant while hot, so defer to the shared instance.
        if ($vite->isRunningHot()) {
            $this->tags = $vite($this->entrypoints, $this->build);

            return;
        }

        // Production/built: only emit tags when a manifest Laravel can actually
        // read is present. A dedicated Vite instance keeps the shared singleton's
        // manifest filename untouched for other (core) @vite calls in this request,
        // while sharing its hot-file so both agree we are not running hot.
        $manifest = $this->resolveManifestFilename();

        if ($manifest !== null) {
            $this->tags = (new Vite())
                ->useHotFile($vite->hotFile())
                ->useBuildDirectory($this->build)
                ->useManifestFilename($manifest)
                ->__invoke($this->entrypoints);

            return;
        }

        $this->hint = $this->buildHint();
    }

    /**
     * Find a readable Vite manifest for this build directory, if any.
     *
     * Supports both manifest locations:
     *  - Laravel / laravel-vite-plugin: public/{build}/manifest.json
     *  - Vite 5+ default:               public/{build}/.vite/manifest.json
     */
    protected function resolveManifestFilename(): ?string
    {
        foreach (['manifest.json', '.vite/manifest.json'] as $candidate) {
            if (is_file(public_path($this->build.'/'.$candidate))) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * A non-fatal, developer-facing hint rendered only in debug mode.
     */
    protected function buildHint(): ?string
    {
        if (! config('app.debug')) {
            return null;
        }

        return sprintf(
            '<!-- [module-styles] Assets for "%s" are not built. '
            .'Run `php artisan module:compile-css <Module>` or start the Vite dev server. -->',
            e($this->build)
        );
    }

    public function render(): View|Closure|string
    {
        return view('components.module-styles');
    }
}
