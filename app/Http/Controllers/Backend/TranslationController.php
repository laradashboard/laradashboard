<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\LanguageService;
use App\Services\TranslationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    /**
     * Available languages
     */
    protected array $languages = [];

    /**
     * Constructor
     */
    public function __construct(
        private readonly LanguageService $languageService,
        private readonly TranslationService $translationService
    ) {
        $this->languages = $this->languageService->getActiveLanguages();
    }

    /**
     * Display translation management interface.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);

        $languages = $this->languages;
        $groups = $this->translationService->getGroups();
        $selectedLang = request()->get('lang', 'bn');
        $selectedGroup = request()->get('group', 'json');

        // Get base English translations for the selected group
        $enTranslations = $this->translationService->getTranslations('en', $selectedGroup);

        // Get translations for selected language and group
        $translations = $this->translationService->getTranslations($selectedLang, $selectedGroup);

        // Get available translation files for both languages
        $availableGroups = $this->translationService->getAvailableTranslationGroups($selectedLang);

        // Get all available languages from the service
        $allLanguages = $this->languageService->getLanguageNames();

        // Calculate translation statistics
        $translationStats = $this->translationService->calculateTranslationStats($translations, $enTranslations, $selectedGroup);

        return view('backend.pages.translations.index', compact(
            'languages',
            'groups',
            'enTranslations',
            'translations',
            'selectedLang',
            'selectedGroup',
            'availableGroups',
            'allLanguages',
            'translationStats',
        ))
            ->with([
                'breadcrumbs' => [
                    'title' => __('Translations'),
                ],
            ]);
    }

    /**
     * Create a new language translation file.
     */
    public function create(Request $request): RedirectResponse
    {
        $this->authorize('update', Setting::class);

        $request->validate([
            'language_code' => 'required|string|max:10',
            'group' => 'required|string|max:30',
        ]);

        $lang = $request->input('language_code');
        $group = $request->input('group');

        // Create language file and handle errors
        $result = $this->translationService->createLanguageFile($lang, $group);

        if (! $result) {
            return redirect()
                ->route('admin.translations.index', ['lang' => $lang, 'group' => $group])
                ->with('error', "Translation file for {$lang} already exists.");
        }

        $languageName = $this->languageService->getLanguageNameByLocale($lang);

        $this->storeActionLog(ActionType::CREATED, [
            'translations' => "Created new translation file for {$languageName}, group: {$group}",
        ]);

        return redirect()
            ->route('admin.translations.index', ['lang' => $lang, 'group' => $group])
            ->with('success', "New language {$languageName} ({$group}) has been added successfully.");
    }

    /**
     * Update translations.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorize('update', Setting::class);

        $lang = $request->input('lang', 'bn');
        $group = $request->input('group', 'json');
        $translations = $request->input('translations', []);

        // Filter out empty translations for JSON files
        if ($group === 'json') {
            $translations = array_filter($translations, function ($value) {
                return $value !== null && $value !== '';
            });
        }

        // Save translations
        $this->translationService->saveTranslations($lang, $translations, $group);

        $languageName = $this->languages[$lang]['name'] ?? ucfirst($lang);

        // Count translations properly, accounting for nested arrays
        $translationCount = $group === 'json'
            ? count($translations)
            : $this->translationService->countTranslationsRecursively($translations);

        $this->storeActionLog(ActionType::UPDATED, [
            'translations' => "Updated {$languageName} translations for group '{$group}'",
            'count' => $translationCount,
        ]);

        return redirect()
            ->route('admin.translations.index', ['lang' => $lang, 'group' => $group])
            ->with('success', "Translations for {$languageName} ({$group}) have been updated successfully.");
    }
}
