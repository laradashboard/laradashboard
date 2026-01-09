<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Services\CoreUpgradeService;
use Livewire\Component;

class CoreUpgradeNotification extends Component
{
    public bool $hasUpdate = false;

    public ?string $latestVersion = null;

    public bool $isCritical = false;

    public function mount(): void
    {
        $this->checkForUpdates();
    }

    public function checkForUpdates(): void
    {
        $upgradeService = app(CoreUpgradeService::class);
        $updateInfo = $upgradeService->getStoredUpdateInfo();

        if ($updateInfo !== null && ($updateInfo['has_update'] ?? false)) {
            $this->hasUpdate = true;
            $this->latestVersion = $updateInfo['latest_version'] ?? null;
            $this->isCritical = $updateInfo['has_critical'] ?? false;
        }
    }

    public function render()
    {
        return view('livewire.components.core-upgrade-notification');
    }
}
