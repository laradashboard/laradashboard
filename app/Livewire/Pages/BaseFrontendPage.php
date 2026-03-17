<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Services\Frontend\SeoHelper;
use App\Services\FrontendQueryService;
use App\Support\Facades\Theme;
use Illuminate\View\View;
use Livewire\Component;

abstract class BaseFrontendPage extends Component
{
    /**
     * The layout view to use. Override in subclass or it defaults to the active theme's layout.
     */
    protected ?string $layoutView = null;

    /**
     * Get the frontend query service.
     */
    protected function query(): FrontendQueryService
    {
        return app(FrontendQueryService::class);
    }

    /**
     * Get the SEO helper.
     */
    protected function seo(): SeoHelper
    {
        return app(SeoHelper::class);
    }

    /**
     * Resolve the layout view path.
     */
    protected function resolveLayout(): string
    {
        if ($this->layoutView) {
            return $this->layoutView;
        }

        $active = Theme::active();
        if ($active) {
            return $active . '::layouts.app';
        }

        // Infer from the component's namespace (e.g. Modules\Starter26\... => starter26)
        $class = static::class;
        if (preg_match('/^Modules\\\\(\w+)\\\\/', $class, $matches)) {
            return strtolower($matches[1]) . '::layouts.app';
        }

        return 'layouts.app';
    }

    /**
     * Render a view with the theme layout and merged SEO params.
     */
    protected function renderWithLayout(string $view, array $seoParams = [], array $viewData = []): View
    {
        return view($view, $viewData)
            ->layout($this->resolveLayout(), $seoParams);
    }
}
