<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActionType;
use App\Traits\HasActionLogTrait;
use Illuminate\Support\Facades\Artisan;

class CacheService
{
    use HasActionLogTrait;

    public function clearCache(): void
    {
        try {
            $this->clearConfigCache();
            $this->clearRouteCache();
            $this->clearViewCache();
            $this->clearApplicationCache();
        } catch (\Throwable $th) {
            $this->storeActionLog(ActionType::EXCEPTION, [
                'cache' => $th->getMessage(),
            ]);
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