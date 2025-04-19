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

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService,
        private readonly EnvWriter $envWriter,
        private readonly CacheService $cacheService
    ) {
    }

    public function index($tab = null): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['settings.edit']);

        $tab = $tab ?? request()->input('tab', 'general');
        return view('backend.pages.settings.index', compact("tab"));
    }

    public function store(Request $request)
    {
        $this->checkAuthorization(auth()->user(), ['settings.edit']);

        $fields = $request->all();
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

        $this->maybeWriteKeysToEnvFile($request);

        $this->storeActionLog(ActionType::UPDATED, [
            'settings' => $fields,
        ]);

        $this->cacheService->clearCache();

        return redirect()->back()->with('success', 'Settings saved successfully.');
    }

    private function maybeWriteKeysToEnvFile(Request $request)
    {
        try {
            $this->envWriter->maybeWriteKeysToEnvFile($request->all());
        } catch (\Throwable $th) {
            $this->storeActionLog(ActionType::EXCEPTION, [
                'settings' => $th->getMessage(),
            ]);
        }
    }
}
