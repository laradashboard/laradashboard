<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Enums\Hooks\SettingFilterHook;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\CacheService;
use App\Services\EnvWriter;
use App\Services\ImageService;
use App\Services\RecaptchaService;
use App\Services\SettingService;
use App\Support\Facades\Hook;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService,
        private readonly EnvWriter $envWriter,
        private readonly CacheService $cacheService,
        private readonly ImageService $imageService,
        private readonly RecaptchaService $recaptchaService,
    ) {
    }

    public function index($tab = null): Renderable
    {
        $this->authorize('manage', Setting::class);

        $tab = $tab ?? request()->input('tab', 'general');

        $this->setBreadcrumbTitle(__('Settings'))
            ->setBreadcrumbIcon('lucide:settings');

        return $this->renderViewWithBreadcrumbs('backend.pages.settings.index', compact('tab'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage', Setting::class);

        // Restrict specific fields in demo mode.
        if (config('app.demo_mode', false)) {
            $restrictedFields = Hook::applyFilters(SettingFilterHook::SETTINGS_RESTRICTED_FIELDS, [
                'app_name',
                'google_analytics_script',
                'recaptcha_site_key',
                'recaptcha_secret_key',
                'recaptcha_enabled_pages',
                'recaptcha_score_threshold',
                'hide_admin_url',
                'custom_login_route',
                'hide_default_login_url',
            ]);
            $fields = $request->except($restrictedFields);
        } else {
            $fields = $request->all();
        }

        // Validate custom login route if provided
        if ($request->filled('custom_login_route')) {
            $request->validate([
                'custom_login_route' => 'regex:/^[a-zA-Z0-9\-\_\/]+$/|min:3|max:50',
            ], [
                'custom_login_route.regex' => __('The custom login route can only contain letters, numbers, hyphens, underscores and forward slashes.'),
            ]);
        }

        $uploadPath = 'uploads/settings';

        // Handle checkbox fields that might not be present when unchecked
        $checkboxFields = ['hide_admin_url', 'hide_default_login_url'];
        foreach ($checkboxFields as $checkboxField) {
            // Skip restricted fields in demo mode
            if (config('app.demo_mode', false) && in_array($checkboxField, $restrictedFields ?? [])) {
                continue;
            }

            if (! isset($fields[$checkboxField]) && $request->has('_token')) {
                // If the form was submitted but checkbox wasn't checked, set to 0
                $fields[$checkboxField] = '0';
            }
        }

        foreach ($fields as $fieldName => $fieldValue) {
            if ($request->hasFile($fieldName)) {
                $this->imageService->deleteImageFromPublic((string) config($fieldName));
                $fileUrl = $this->imageService->storeImageAndGetUrl($request, $fieldName, $uploadPath);
                $this->settingService->addSetting($fieldName, $fileUrl);
            } elseif ($fieldName === 'recaptcha_enabled_pages') {
                // Validate enabled pages against allowed list.
                $enabledPages = $request->input('recaptcha_enabled_pages', []);
                $validPages = array_keys($this->recaptchaService::getAvailablePages());
                $enabledPages = array_intersect($enabledPages, $validPages);
                $this->settingService->addSetting($fieldName, json_encode(array_values($enabledPages)));
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
