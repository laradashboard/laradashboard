<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\LanguageService;
use App\Services\TranslationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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
        $selectedLang = request()->input('lang', 'bn');
        $selectedGroup = request()->input('group', 'json');
        $search = request()->input('search', '');
        $perPage = (int) request()->input('per_page', 50);
        $page = (int) request()->input('page', 1);

        // Get base English translations for the selected group
        $allEnTranslations = $this->translationService->getTranslations('en', $selectedGroup);

        // Get translations for selected language and group
        $allTranslations = $this->translationService->getTranslations($selectedLang, $selectedGroup);

        // Get available translation files for both languages
        $availableGroups = $this->translationService->getAvailableTranslationGroups($selectedLang);

        // Get all available languages from the service
        $allLanguages = $this->languageService->getLanguageNames();

        // Calculate translation statistics (on full set)
        $translationStats = $this->translationService->calculateTranslationStats($allTranslations, $allEnTranslations, $selectedGroup);

        // Filter by search query
        $filteredEnTranslations = $allEnTranslations;
        if ($search !== '') {
            $filteredEnTranslations = array_filter($allEnTranslations, function ($value, $key) use ($search, $allTranslations) {
                $searchLower = mb_strtolower($search);

                return mb_stripos($key, $search) !== false
                    || (is_string($value) && mb_stripos($value, $search) !== false)
                    || (isset($allTranslations[$key]) && is_string($allTranslations[$key]) && mb_stripos($allTranslations[$key], $search) !== false);
            }, ARRAY_FILTER_USE_BOTH);
        }

        // Paginate
        $totalFiltered = count($filteredEnTranslations);
        $totalPages = max(1, (int) ceil($totalFiltered / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $enTranslations = array_slice($filteredEnTranslations, $offset, $perPage, true);
        $translations = $allTranslations;

        $pagination = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'total_filtered' => $totalFiltered,
            'total' => count($allEnTranslations),
        ];

        $this->setBreadcrumbTitle(__('Translations'))
           ->setBreadcrumbIcon('lucide:languages')
           ->setBreadcrumbActionClick(
               "addLanguageModalOpen = true",
               __('New Translation'),
               'feather:plus',
               'settings.view'
           );

        return $this->renderViewWithBreadcrumbs('backend.pages.translations.index', compact(
            'languages',
            'groups',
            'enTranslations',
            'translations',
            'selectedLang',
            'selectedGroup',
            'availableGroups',
            'allLanguages',
            'translationStats',
            'pagination',
            'search',
        ));
    }

    /**
     * Create a new language translation file.
     */
    public function create(Request $request): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

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

        session()->flash(__('New language :language (:group) has been added successfully.', [
            'language' => $languageName,
            'group' => $group,
        ]));

        return redirect()
            ->route('admin.translations.index', ['lang' => $lang, 'group' => $group])
            ->with('success', "New language {$languageName} ({$group}) has been added successfully.");
    }

    /**
     * Update translations.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

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

        session()->flash(__('Translations for :language (:group) have been updated successfully.', [
            'language' => $languageName,
            'group' => $group,
        ]));

        return redirect()
            ->route('admin.translations.index', ['lang' => $lang, 'group' => $group])
            ->with('success', "Translations for {$languageName} ({$group}) have been updated successfully.");
    }

    /**
     * Save translations via AJAX.
     *
     * Accepts a partial set of translations (e.g. one page) in JSON body
     * to bypass max_input_vars limits. Merges with existing translations
     * so saving one page doesn't overwrite other pages' translations.
     */
    public function saveChunk(Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $lang = $request->input('lang', 'bn');
        $group = $request->input('group', 'json');
        $submitted = $request->input('translations', []);

        // Get all existing translations to merge with
        $existing = $this->translationService->getTranslations($lang, $group);

        // Merge: submitted translations override existing ones
        if ($group === 'json') {
            $merged = array_merge($existing, $submitted);

            // Filter out empty values
            $merged = array_filter($merged, fn ($value) => $value !== null && $value !== '');
        } else {
            $merged = array_replace_recursive($existing, $submitted);
        }

        $this->translationService->saveTranslations($lang, $merged, $group);

        $languageName = $this->languages[$lang]['name'] ?? ucfirst($lang);

        $savedCount = count($submitted);

        $this->storeActionLog(ActionType::UPDATED, [
            'translations' => "Updated {$languageName} translations for group '{$group}'",
            'count' => $savedCount,
        ]);

        session()->flash('success', "Translations for {$languageName} ({$group}) have been updated successfully.");

        return response()->json([
            'status' => 'saved',
            'count' => $savedCount,
            'redirect' => route('admin.translations.index', [
                'lang' => $lang,
                'group' => $group,
                'page' => $request->input('page', 1),
                'per_page' => $request->input('per_page', 50),
                'search' => $request->input('search', ''),
            ]),
        ]);
    }
}
