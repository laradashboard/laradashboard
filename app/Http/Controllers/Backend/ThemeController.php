<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ImageService;
use App\Services\SettingService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route as RouteFacade;
use Nwidart\Modules\Facades\Module;

class ThemeController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService,
        private readonly ImageService $imageService,
    ) {
    }

    public function index($tab = null): Renderable
    {
        $this->authorize('manage', Setting::class);

        $tab = $tab ?? request()->input('tab', 'choose-theme');

        $themes = $this->getInstalledThemes();
        $activeTheme = config('settings.active_theme', '');

        // Auto-select if only one theme is installed and no active theme is set
        if ($themes->count() === 1 && empty($activeTheme)) {
            $activeTheme = $themes->first()['alias'];
            $this->settingService->addSetting('active_theme', $activeTheme);
        }

        $this->setBreadcrumbTitle(__('Theme'))
            ->setBreadcrumbIcon('lucide:palette');

        return $this->renderViewWithBreadcrumbs('backend.pages.theme.index', compact('tab', 'themes', 'activeTheme'));
    }

    public function activate(Request $request)
    {
        $this->authorize('manage', Setting::class);

        $request->validate([
            'theme' => 'required|string',
        ]);

        $themeAlias = $request->input('theme');

        // Verify the theme exists and has "theme": true
        $themes = $this->getInstalledThemes();
        $theme = $themes->firstWhere('alias', $themeAlias);

        if (! $theme) {
            return redirect()->back()->with('error', __('Invalid theme selected.'));
        }

        $this->settingService->addSetting('active_theme', $themeAlias);

        $this->storeActionLog(ActionType::UPDATED, [
            'active_theme' => $themeAlias,
        ]);

        return redirect()->back()->with('success', __('Theme ":name" activated successfully.', ['name' => $theme['name']]));
    }

    /**
     * Get all installed modules that have "theme": true in module.json.
     */
    private function getInstalledThemes(): \Illuminate\Support\Collection
    {
        $themes = collect();
        $modules = Module::all();

        foreach ($modules as $module) {
            $moduleJsonPath = $module->getPath() . '/module.json';

            if (! File::exists($moduleJsonPath)) {
                continue;
            }

            $moduleJson = json_decode(File::get($moduleJsonPath), true);

            if (! empty($moduleJson['theme'])) {
                $screenshotPath = $module->getPath() . '/screenshot.png';
                $hasScreenshot = File::exists($screenshotPath);

                $alias = $moduleJson['alias'] ?? strtolower($module->getName());
                $homeRouteName = $alias . '.home';
                $homepageUrl = RouteFacade::has($homeRouteName) ? route($homeRouteName) : null;

                $themes->push([
                    'name' => $moduleJson['name'] ?? $module->getName(),
                    'alias' => $alias,
                    'description' => $moduleJson['description'] ?? '',
                    'version' => $moduleJson['version'] ?? '1.0.0',
                    'is_enabled' => $module->isEnabled(),
                    'has_screenshot' => $hasScreenshot,
                    'screenshot_url' => $hasScreenshot
                        ? asset('modules/' . strtolower($module->getName()) . '/screenshot.png')
                        : null,
                    'homepage_url' => $homepageUrl,
                ]);
            }
        }

        return $themes;
    }

    public function store(Request $request)
    {
        $this->authorize('manage', Setting::class);

        $fields = $request->all();
        $uploadPath = 'uploads/settings';

        // Handle checkbox fields that might not be present when unchecked
        $checkboxFields = [];
        foreach ($checkboxFields as $checkboxField) {
            if (! isset($fields[$checkboxField]) && $request->has('_token')) {
                $fields[$checkboxField] = '0';
            }
        }

        foreach ($fields as $fieldName => $fieldValue) {
            if ($fieldName === '_token') {
                continue;
            }

            if ($request->hasFile($fieldName)) {
                $this->imageService->deleteImageFromPublic((string) config($fieldName));
                $fileUrl = $this->imageService->storeImageAndGetUrl($request, $fieldName, $uploadPath);
                $this->settingService->addSetting($fieldName, $fileUrl);
            } elseif ($fieldName === 'social_links') {
                $this->settingService->addSetting($fieldName, $fieldValue);
            } else {
                $this->settingService->addSetting($fieldName, $fieldValue);
            }
        }

        $this->storeActionLog(ActionType::UPDATED, [
            'theme_settings' => $fields,
        ]);

        return redirect()->back()->with('success', __('Theme settings saved successfully.'));
    }
}
