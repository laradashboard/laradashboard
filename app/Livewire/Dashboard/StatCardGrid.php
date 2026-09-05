<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardWidgetService;
use Livewire\Attributes\On;
use Livewire\Component;

class StatCardGrid extends Component
{
    /** @var array<string, mixed> */
    public array $context = [];

    public function mount(array $context = []): void
    {
        $this->context = $context;
    }

    public function hideWidget(string $widgetId): void
    {
        app(DashboardWidgetService::class)->hideWidget($widgetId);
    }

    #[On('dashboard-widgets-updated')]
    public function refreshWidgets(): void
    {
        // Re-render after widget preferences change elsewhere.
    }

    public function render()
    {
        return view('livewire.dashboard.stat-card-grid', [
            'visibleCards' => app(DashboardWidgetService::class)->getVisibleStatWidgets($this->context),
        ]);
    }
}
