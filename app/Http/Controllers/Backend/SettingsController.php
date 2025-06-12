<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Http\Controllers\Controller;
use App\Services\CacheService;
use App\Services\EnvWriter;
use App\Services\SettingService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService,
        private readonly EnvWriter $envWriter,
        private readonly CacheService $cacheService
    ) {
        // The cacheService is used in the EnvWriter for cache clearing operations
    }

    public function index($tab = null): Renderable
    {
        $this->checkAuthorization(Auth::user(), ['settings.edit']);

        $tab = $tab ?? request()->input('tab', 'general');

        return view('backend.pages.settings.index', compact('tab'))
            ->with([
                'breadcrumbs' => [
                    'title' => __('Settings'),
                ],
            ]);
    }

    public function store(Request $request)
    {
        // Restrict specific fields in demo mode.
        if (config('app.demo_mode', false)) {
            $restrictedFields = ld_apply_filters('settings_restricted_fields', ['app_name', 'google_analytics_script']);
            $fields = $request->except($restrictedFields);
        } else {
            $fields = $request->all();
        }

        $this->checkAuthorization(Auth::user(), ['settings.edit']);

        $uploadPath = 'uploads/settings';

        foreach ($fields as $fieldName => $fieldValue) {
            if ($request->hasFile($fieldName)) {
                deleteImageFromPublic((string) config($fieldName));
                $fileUrl = storeImageAndGetUrl($request, $fieldName, $uploadPath);
                $this->settingService->addSetting($fieldName, $fileUrl);
            } else {
                $this->settingService->addSetting($fieldName, $fieldValue);
            }
        }

        $this->envWriter->batchWriteKeysToEnvFile($fields);

        $this->storeActionLog(ActionType::UPDATED, [
            'settings' => $fields,
        ]);

        return redirect()->back()->with('success', 'Settings saved successfully.');
    }
}
