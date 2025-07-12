<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\SettingResource;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends ApiController
{
    public function __construct(private readonly SettingService $settingService)
    {
    }

    /**
     * Display a listing of the settings.
     *
     * @tags Settings
     */
    public function index(Request $request): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['settings.view']);

        $group = $request->input('group');
        $settings = $this->settingService->getAllSettings($group);

        return $this->resourceResponse(
            SettingResource::collection($settings),
            'Settings retrieved successfully'
        );
    }

    /**
     * Update the specified settings.
     *
     * @tags Settings
     */
    public function update(Request $request): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['settings.edit']);

        $request->validate([
            /** @example {"site_name": "My Laravel Dashboard", "site_description": "A powerful admin dashboard"} */
            'settings' => 'required|array',
        ]);

        $settings = $request->input('settings', []);
        $updatedSettings = [];

        foreach ($settings as $key => $value) {
            $setting = $this->settingService->updateOrCreateSetting((string) $key, $value);
            $updatedSettings[] = $setting;
        }

        $this->logAction('Settings Updated', null, ['updated_keys' => array_keys($settings)]);

        return $this->resourceResponse(
            SettingResource::collection(collect($updatedSettings)),
            'Settings updated successfully'
        );
    }

    /**
     * Get a specific setting by key.
     *
     * @tags Settings
     */
    public function show(string $key): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['settings.view']);

        $setting = $this->settingService->getSettingByKey($key);

        if (! $setting) {
            return $this->errorResponse('Setting not found', 404);
        }

        return $this->resourceResponse(
            new SettingResource($setting),
            'Setting retrieved successfully'
        );
    }
}
