<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Enums\Hooks\SettingFilterHook;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ImageService;
use App\Services\SettingService;
use App\Support\Facades\Hook;
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

        $colorPresets = $this->getColorPresets();

        $this->setBreadcrumbTitle(__('Theme'))
            ->setBreadcrumbIcon('lucide:palette');

        return $this->renderViewWithBreadcrumbs('backend.pages.theme.index', compact('tab', 'themes', 'activeTheme', 'colorPresets'));
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

    /**
     * Get the default color palette presets, filterable via Hook.
     */
    private function getColorPresets(): array
    {
        $presets = [
            [
                'name' => __('Default Indigo'),
                'colors' => [
                    'primary' => '#6366F1', 'secondary' => '#8B5CF6',
                    'navbar_bg_lite' => '#ffffff', 'sidebar_bg_lite' => '#1e1e2d',
                    'navbar_text_lite' => '#1e293b', 'sidebar_text_lite' => '#a2a3b7',
                    'navbar_bg_dark' => '#1a1a2e', 'sidebar_bg_dark' => '#151521',
                    'navbar_text_dark' => '#e2e8f0', 'sidebar_text_dark' => '#9899ac',
                ],
            ],
            [
                'name' => __('Ocean Blue'),
                'colors' => [
                    'primary' => '#2563EB', 'secondary' => '#0EA5E9',
                    'navbar_bg_lite' => '#ffffff', 'sidebar_bg_lite' => '#1b2e4b',
                    'navbar_text_lite' => '#1e293b', 'sidebar_text_lite' => '#a0aec0',
                    'navbar_bg_dark' => '#0f172a', 'sidebar_bg_dark' => '#0d1b2a',
                    'navbar_text_dark' => '#e2e8f0', 'sidebar_text_dark' => '#94a3b8',
                ],
            ],
            [
                'name' => __('Emerald'),
                'colors' => [
                    'primary' => '#059669', 'secondary' => '#10B981',
                    'navbar_bg_lite' => '#ffffff', 'sidebar_bg_lite' => '#1a2332',
                    'navbar_text_lite' => '#1e293b', 'sidebar_text_lite' => '#a0aec0',
                    'navbar_bg_dark' => '#0f172a', 'sidebar_bg_dark' => '#111827',
                    'navbar_text_dark' => '#e2e8f0', 'sidebar_text_dark' => '#94a3b8',
                ],
            ],
            [
                'name' => __('Sunset Orange'),
                'colors' => [
                    'primary' => '#EA580C', 'secondary' => '#F59E0B',
                    'navbar_bg_lite' => '#ffffff', 'sidebar_bg_lite' => '#27201a',
                    'navbar_text_lite' => '#1e293b', 'sidebar_text_lite' => '#b0a99e',
                    'navbar_bg_dark' => '#1c1712', 'sidebar_bg_dark' => '#171310',
                    'navbar_text_dark' => '#e2e8f0', 'sidebar_text_dark' => '#a09890',
                ],
            ],
            [
                'name' => __('Rose'),
                'colors' => [
                    'primary' => '#E11D48', 'secondary' => '#F43F5E',
                    'navbar_bg_lite' => '#ffffff', 'sidebar_bg_lite' => '#1e1e2d',
                    'navbar_text_lite' => '#1e293b', 'sidebar_text_lite' => '#a2a3b7',
                    'navbar_bg_dark' => '#1a1a2e', 'sidebar_bg_dark' => '#151521',
                    'navbar_text_dark' => '#e2e8f0', 'sidebar_text_dark' => '#9899ac',
                ],
            ],
            [
                'name' => __('Purple Reign'),
                'colors' => [
                    'primary' => '#7C3AED', 'secondary' => '#A855F7',
                    'navbar_bg_lite' => '#ffffff', 'sidebar_bg_lite' => '#1e1b2e',
                    'navbar_text_lite' => '#1e293b', 'sidebar_text_lite' => '#a8a3c0',
                    'navbar_bg_dark' => '#16132a', 'sidebar_bg_dark' => '#110e21',
                    'navbar_text_dark' => '#e2e8f0', 'sidebar_text_dark' => '#9b96b0',
                ],
            ],
            [
                'name' => __('Teal Cyan'),
                'colors' => [
                    'primary' => '#0D9488', 'secondary' => '#06B6D4',
                    'navbar_bg_lite' => '#ffffff', 'sidebar_bg_lite' => '#1a2830',
                    'navbar_text_lite' => '#1e293b', 'sidebar_text_lite' => '#a0b4bc',
                    'navbar_bg_dark' => '#0f1e26', 'sidebar_bg_dark' => '#0b171e',
                    'navbar_text_dark' => '#e2e8f0', 'sidebar_text_dark' => '#94a8b0',
                ],
            ],
            [
                'name' => __('Slate Professional'),
                'colors' => [
                    'primary' => '#475569', 'secondary' => '#64748B',
                    'navbar_bg_lite' => '#ffffff', 'sidebar_bg_lite' => '#1e293b',
                    'navbar_text_lite' => '#1e293b', 'sidebar_text_lite' => '#94a3b8',
                    'navbar_bg_dark' => '#0f172a', 'sidebar_bg_dark' => '#0f172a',
                    'navbar_text_dark' => '#e2e8f0', 'sidebar_text_dark' => '#94a3b8',
                ],
            ],
            [
                'name' => __('Amber Gold'),
                'colors' => [
                    'primary' => '#D97706', 'secondary' => '#FBBF24',
                    'navbar_bg_lite' => '#ffffff', 'sidebar_bg_lite' => '#262118',
                    'navbar_text_lite' => '#1e293b', 'sidebar_text_lite' => '#b5ab98',
                    'navbar_bg_dark' => '#1a1710', 'sidebar_bg_dark' => '#15120d',
                    'navbar_text_dark' => '#e2e8f0', 'sidebar_text_dark' => '#a59e8c',
                ],
            ],
            [
                'name' => __('Midnight Navy'),
                'colors' => [
                    'primary' => '#1E3A5F', 'secondary' => '#3B82F6',
                    'navbar_bg_lite' => '#ffffff', 'sidebar_bg_lite' => '#152238',
                    'navbar_text_lite' => '#1e293b', 'sidebar_text_lite' => '#8fabc4',
                    'navbar_bg_dark' => '#0a1628', 'sidebar_bg_dark' => '#070f1c',
                    'navbar_text_dark' => '#e2e8f0', 'sidebar_text_dark' => '#8098b4',
                ],
            ],
            [
                'name' => __('Light Clean'),
                'colors' => [
                    'primary' => '#6366F1', 'secondary' => '#8B5CF6',
                    'navbar_bg_lite' => '#ffffff', 'sidebar_bg_lite' => '#f8fafc',
                    'navbar_text_lite' => '#1e293b', 'sidebar_text_lite' => '#475569',
                    'navbar_bg_dark' => '#1e1e2d', 'sidebar_bg_dark' => '#151521',
                    'navbar_text_dark' => '#e2e8f0', 'sidebar_text_dark' => '#a2a3b7',
                ],
            ],
            [
                'name' => __('Light Warm'),
                'colors' => [
                    'primary' => '#EA580C', 'secondary' => '#F59E0B',
                    'navbar_bg_lite' => '#ffffff', 'sidebar_bg_lite' => '#fafaf9',
                    'navbar_text_lite' => '#292524', 'sidebar_text_lite' => '#57534e',
                    'navbar_bg_dark' => '#1c1917', 'sidebar_bg_dark' => '#151412',
                    'navbar_text_dark' => '#e7e5e4', 'sidebar_text_dark' => '#a8a29e',
                ],
            ],
        ];

        return Hook::applyFilters(SettingFilterHook::THEME_COLOR_PRESETS, $presets);
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
