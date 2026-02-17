<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class CacheManager extends Component
{
    public bool $isClearing = false;

    public ?string $lastCleared = null;

    public ?string $successMessage = null;

    /**
     * Clear application cache.
     */
    public function clearApplicationCache(): void
    {
        $this->isClearing = true;

        try {
            Artisan::call('cache:clear');
            $this->successMessage = __('Application cache cleared successfully.');
            $this->lastCleared = now()->format('M d, Y H:i:s');
        } catch (\Exception $e) {
            session()->flash('error', __('Failed to clear application cache: :error', ['error' => $e->getMessage()]));
        }

        $this->isClearing = false;
    }

    /**
     * Clear configuration cache.
     */
    public function clearConfigCache(): void
    {
        $this->isClearing = true;

        try {
            Artisan::call('config:clear');
            $this->successMessage = __('Configuration cache cleared successfully.');
            $this->lastCleared = now()->format('M d, Y H:i:s');
        } catch (\Exception $e) {
            session()->flash('error', __('Failed to clear config cache: :error', ['error' => $e->getMessage()]));
        }

        $this->isClearing = false;
    }

    /**
     * Clear route cache.
     */
    public function clearRouteCache(): void
    {
        $this->isClearing = true;

        try {
            Artisan::call('route:clear');
            $this->successMessage = __('Route cache cleared successfully.');
            $this->lastCleared = now()->format('M d, Y H:i:s');
        } catch (\Exception $e) {
            session()->flash('error', __('Failed to clear route cache: :error', ['error' => $e->getMessage()]));
        }

        $this->isClearing = false;
    }

    /**
     * Clear view cache.
     */
    public function clearViewCache(): void
    {
        $this->isClearing = true;

        try {
            Artisan::call('view:clear');
            $this->successMessage = __('View cache cleared successfully.');
            $this->lastCleared = now()->format('M d, Y H:i:s');
        } catch (\Exception $e) {
            session()->flash('error', __('Failed to clear view cache: :error', ['error' => $e->getMessage()]));
        }

        $this->isClearing = false;
    }

    /**
     * Clear all caches at once.
     */
    public function clearAllCaches(): void
    {
        $this->isClearing = true;

        try {
            Artisan::call('optimize:clear');
            $this->successMessage = __('All caches cleared successfully.');
            $this->lastCleared = now()->format('M d, Y H:i:s');
        } catch (\Exception $e) {
            session()->flash('error', __('Failed to clear caches: :error', ['error' => $e->getMessage()]));
        }

        $this->isClearing = false;
    }

    /**
     * Optimize the application (cache config, routes, views).
     */
    public function optimizeApplication(): void
    {
        $this->isClearing = true;

        try {
            Artisan::call('optimize');
            $this->successMessage = __('Application optimized successfully. Config, routes, and views are now cached.');
            $this->lastCleared = now()->format('M d, Y H:i:s');
        } catch (\Exception $e) {
            session()->flash('error', __('Failed to optimize application: :error', ['error' => $e->getMessage()]));
        }

        $this->isClearing = false;
    }

    public function render()
    {
        return view('livewire.components.cache-manager');
    }
}
