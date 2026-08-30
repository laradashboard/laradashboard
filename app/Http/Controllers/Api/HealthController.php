<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CoreUpgradeService;
use App\Services\UpgradeSnapshotService;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(
        CoreUpgradeService $coreUpgradeService,
        UpgradeSnapshotService $snapshotService,
    ): JsonResponse {
        $version = $coreUpgradeService->getCurrentVersion();

        return response()->json([
            'status' => 'ok',
            'version' => $version['version'] ?? '0.0.0',
            'release_date' => $version['release_date'] ?? null,
            'name' => $version['name'] ?? config('app.name'),
            'demo_mode' => (bool) config('app.demo_mode', false),
            'environment' => config('app.env'),
            'module_count' => count($snapshotService->captureModuleState()),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
