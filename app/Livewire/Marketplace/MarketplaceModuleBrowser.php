<?php

declare(strict_types=1);

namespace App\Livewire\Marketplace;

use App\Services\Modules\MarketplaceService;
use App\Services\Modules\ModuleService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class MarketplaceModuleBrowser extends Component
{
    public string $search = '';

    public string $typeFilter = '';

    public int $page = 1;

    public bool $loaded = false;

    public ?string $installingSlug = null;

    public ?string $apiError = null;

    protected $queryString = [
        'search' => ['except' => '', 'as' => 'marketplace_search'],
        'typeFilter' => ['except' => '', 'as' => 'marketplace_type'],
        'page' => ['except' => 1, 'as' => 'marketplace_page'],
    ];

    public function loadModules(): void
    {
        $this->loaded = true;
    }

    public function updatingSearch(): void
    {
        $this->page = 1;
    }

    public function updatingTypeFilter(): void
    {
        $this->page = 1;
    }

    public function setTypeFilter(string $filter): void
    {
        $this->typeFilter = $filter === 'all' ? '' : $filter;
        $this->page = 1;
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function goToPage(int $page): void
    {
        $this->page = max(1, $page);
    }

    public function retry(): void
    {
        $this->apiError = null;
        $this->loaded = true;
    }

    public function installModule(string $slug, string $version): void
    {
        if (config('app.demo_mode', false)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Restricted'),
                'message' => __('Module installation is restricted in demo mode.'),
            ]);

            return;
        }

        $this->installingSlug = $slug;

        /** @var MarketplaceService $service */
        $service = app(MarketplaceService::class);

        $result = $service->downloadAndInstall($slug, $version);

        if (! $result['success']) {
            $this->installingSlug = null;
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Installation Failed'),
                'message' => $result['message'],
            ]);
            $this->dispatch('module-install-failed');

            return;
        }

        $originalName = $result['original_name'] ?? $result['module_name'];

        /** @var ModuleService $moduleService */
        $moduleService = app(ModuleService::class);

        try {
            // Run migrations using path-based approach (works without nwidart discovery)
            $moduleService->runModuleMigrations($originalName);

            // Enable module by writing directly to modules_statuses.json
            // We can't use artisan module:enable here because nwidart's module scanner
            // caches the module list at boot time and won't find newly installed modules
            // in the same request. The module will be fully active on next page load.
            $moduleService->setModuleStatus($originalName, true);

            $this->installingSlug = null;
            $this->dispatch('notify', [
                'variant' => 'success',
                'title' => __('Installed & Activated'),
                'message' => __('Module ":name" has been installed and activated successfully.', ['name' => $originalName]),
            ]);
            $this->dispatch('module-installed');
        } catch (\Throwable $e) {
            Log::warning("Marketplace module post-install failed for {$originalName}: " . $e->getMessage());
            $this->installingSlug = null;
            $this->dispatch('notify', [
                'variant' => 'warning',
                'title' => __('Installed'),
                'message' => __('Module installed but activation failed: :error. Please activate manually from the Installed tab.', ['error' => $e->getMessage()]),
            ]);
            $this->dispatch('module-install-failed');
        }

        // Refresh the installed modules datatable
        $this->dispatch('module-status-changed');
    }

    public function render()
    {
        $modules = [];
        $meta = ['current_page' => 1, 'last_page' => 1, 'per_page' => 12, 'total' => 0];
        $installedSlugs = [];

        if ($this->loaded) {
            /** @var MarketplaceService $service */
            $service = app(MarketplaceService::class);

            $result = $service->fetchModules(
                search: $this->search,
                type: $this->typeFilter,
                page: $this->page,
                perPage: (int) config('settings.posts_per_page', 12)
            );

            if ($result['success']) {
                $modules = $result['data'];
                $meta = $result['meta'];
                $this->apiError = null;
            } else {
                $this->apiError = $result['error'];
            }

            $installedSlugs = $service->getInstalledModuleSlugs();
        }

        $marketplaceUrl = rtrim(config('laradashboard.marketplace.url', 'https://laradashboard.com'), '/');

        return view('backend.pages.modules.marketplace.browse', [
            'modules' => $modules,
            'meta' => $meta,
            'installedSlugs' => $installedSlugs,
            'marketplaceUrl' => $marketplaceUrl,
        ]);
    }
}
