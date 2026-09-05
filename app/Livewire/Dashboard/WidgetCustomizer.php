<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardWidgetService;
use Livewire\Component;

class WidgetCustomizer extends Component
{
    public bool $showPanel = false;

    /** @var array<int, string> */
    public array $selectedVisibleIds = [];

    public function mount(): void
    {
        $this->syncSelectedVisibleIds();
    }

    public function togglePanel(): void
    {
        $this->showPanel = ! $this->showPanel;

        if ($this->showPanel) {
            $this->syncSelectedVisibleIds();
        }
    }

    public function savePreferences(): void
    {
        app(DashboardWidgetService::class)->setUserVisibleWidgetIds($this->selectedVisibleIds);
        $this->showPanel = false;

        $this->redirectRoute('admin.dashboard', navigate: true);
    }

    public function resetPreferences(): void
    {
        app(DashboardWidgetService::class)->resetUserWidgetPreferences();
        $this->showPanel = false;

        $this->redirectRoute('admin.dashboard', navigate: true);
    }

    public function render()
    {
        $service = app(DashboardWidgetService::class);
        $groups = $service->getManageableWidgetGroups();

        return view('livewire.dashboard.widget-customizer', [
            'widgetGroups' => $groups,
            'hasWidgets' => collect($groups)->flatten(1)->isNotEmpty(),
        ]);
    }

    private function syncSelectedVisibleIds(): void
    {
        $hiddenIds = app(DashboardWidgetService::class)->getHiddenWidgetIds();

        $this->selectedVisibleIds = collect(
            app(DashboardWidgetService::class)->getManageableWidgets()
        )
            ->reject(fn (array $widget) => in_array($widget['id'], $hiddenIds, true))
            ->pluck('id')
            ->values()
            ->all();
    }
}
