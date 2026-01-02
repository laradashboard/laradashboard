<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RemoveLicenseRequest;
use App\Http\Requests\Api\ShowLicenseRequest;
use App\Http\Requests\Api\StoreLicenseRequest;
use App\Services\LicenseVerificationService;
use Illuminate\Http\JsonResponse;

/**
 * Handles local license storage after successful marketplace API calls.
 *
 * The JavaScript calls the marketplace API directly to activate/deactivate,
 * then calls these endpoints to persist the license locally in the database.
 */
class LocalLicenseController extends Controller
{
    public function __construct(
        protected LicenseVerificationService $licenseService
    ) {
    }

    /**
     * Store a license locally after successful marketplace activation.
     */
    public function store(StoreLicenseRequest $request): JsonResponse
    {
        $this->licenseService->storeLicenseLocally(
            $request->module_slug,
            $request->license_key,
            $request->module_name
        );

        return response()->json([
            'success' => true,
            'message' => 'License stored locally.',
        ]);
    }

    /**
     * Remove a license locally after successful marketplace deactivation.
     */
    public function destroy(RemoveLicenseRequest $request): JsonResponse
    {
        $this->licenseService->removeLicenseLocally($request->module_slug);

        return response()->json([
            'success' => true,
            'message' => 'License removed locally.',
        ]);
    }

    /**
     * Get the stored license for a module.
     */
    public function show(ShowLicenseRequest $request): JsonResponse
    {
        $license = $this->licenseService->getStoredLicense($request->module_slug);

        return response()->json([
            'success' => true,
            'data' => $license,
        ]);
    }
}
