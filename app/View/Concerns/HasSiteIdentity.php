<?php

declare(strict_types=1);

namespace App\View\Concerns;

trait HasSiteIdentity
{
    public string $siteName = '';

    public ?string $logoLight = null;

    public ?string $logoDark = null;

    /**
     * Load site identity from config/settings.
     */
    protected function loadSiteIdentity(): void
    {
        $this->siteName = config('app.name', 'LaraDashboard');
        $this->logoLight = config('settings.site_logo_lite') ?: null;
        $this->logoDark = config('settings.site_logo_dark') ?: null;
    }
}
