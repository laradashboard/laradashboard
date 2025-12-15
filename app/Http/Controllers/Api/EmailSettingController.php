<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Setting\UpdateSettingsRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailSettingController extends ApiController
{
    public function __construct(private readonly SettingService $settingService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        $search = $request->input('search', 'mail');
        $settings = $this->settingService->getAllSettings($search);

        return $this->resourceResponse(SettingResource::collection($settings), 'Email settings retrieved successfully');
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $this->authorize('update', Setting::class);

        $settings = $request->input('settings', []);
        $updatedSettings = [];

        foreach ($settings as $key => $value) {
            $updatedSettings[] = $this->settingService->updateOrCreateSetting((string) $key, $value);
        }

        $this->logAction('Email Settings Updated', null, ['updated_keys' => array_keys($settings)]);

        return $this->resourceResponse(SettingResource::collection(collect($updatedSettings)), 'Email settings updated successfully');
    }
}
