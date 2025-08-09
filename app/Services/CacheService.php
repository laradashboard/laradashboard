<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActionType;
use App\Concerns\HasActionLogTrait;
use Illuminate\Support\Facades\Artisan;

class CacheService
{
    use HasActionLogTrait;

    public function clearCache(): void
    {
        try {
            $this->clearConfigCache();
        } catch (\Throwable $th) {
            $this->storeActionLog(ActionType::EXCEPTION, ['config_cache_error' => $th->getMessage()]);
        }

        try {
            $this->clearRouteCache();
        } catch (\Throwable $th) {
            $this->storeActionLog(ActionType::EXCEPTION, ['route_cache_error' => $th->getMessage()]);
        }

        try {
            $this->clearViewCache();
        } catch (\Throwable $th) {
            $this->storeActionLog(ActionType::EXCEPTION, ['view_cache_error' => $th->getMessage()]);
        }

        try {
            $this->clearApplicationCache();
        } catch (\Throwable $th) {
            $this->storeActionLog(ActionType::EXCEPTION, ['application_cache_error' => $th->getMessage()]);
        }
    }

    public function clearNecessaryCaches(): void
    {
        try {
            $this->clearConfigCache();
        } catch (\Throwable $th) {
            $this->storeActionLog(ActionType::EXCEPTION, ['config_cache_error' => $th->getMessage()]);
        }

        try {
            $this->clearRouteCache();
        } catch (\Throwable $th) {
            $this->storeActionLog(ActionType::EXCEPTION, ['route_cache_error' => $th->getMessage()]);
        }
    }

    private function clearConfigCache(): void
    {
        Artisan::call('config:clear');
    }

    private function clearRouteCache(): void
    {
        Artisan::call('route:cache');
    }

    private function clearViewCache(): void
    {
        Artisan::call('view:clear');
    }

    private function clearApplicationCache(): void
    {
        Artisan::call('cache:clear');
    }
}
