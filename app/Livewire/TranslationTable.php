<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Concerns\HasDataTableFeatures;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

class TranslationTable extends PowerGridComponent
{
    use HasDataTableFeatures, WithExport;

    public string $tableName = 'translation-table';

    public function datasource(): Builder
    {
        // Note: This assumes translations are stored in JSON files or database
        // We'll create a mock structure that can be adapted to your translation system
        $translations = collect($this->getTranslationsData());

        // Convert to a Builder-like structure for PowerGrid compatibility
        return $this->createBuilderFromCollection($translations);
    }

    public function columns(): array
    {
        return $this->getExtensibleColumns();
    }

    public function filters(): array
    {
        return $this->getExtensibleFilters();
    }

    public function actions(): array
    {
        return $this->getExtensibleActions();
    }

    // Implementation of abstract methods from HasDataTableFeatures

    protected function getBaseColumns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->hidden(),

            Column::make('Key', 'key')
                ->sortable()
                ->searchable()
                ->format(function ($value) {
                    return '<span class="font-mono text-sm text-gray-900 dark:text-white">' . $value . '</span>';
                }),

            Column::make('Default (EN)', 'en_value')
                ->sortable()
                ->searchable()
                ->format(function ($value) {
                    return '<span class="text-sm text-gray-600 dark:text-gray-300">' . \Str::limit($value, 50) . '</span>';
                }),

            Column::make('Languages', 'languages')
                ->format(function ($value, $row) {
                    $languages = $row->available_languages ?? [];
                    $badges = [];
                    
                    foreach ($languages as $lang => $isTranslated) {
                        $colorClass = $isTranslated 
                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                        
                        $badges[] = '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ' . $colorClass . '">' . strtoupper($lang) . '</span>';
                    }
                    
                    return implode(' ', $badges);
                }),

            Column::make('Status', 'status')
                ->sortable()
                ->format(function ($value, $row) {
                    $completedCount = count(array_filter($row->available_languages ?? []));
                    $totalCount = count($row->available_languages ?? []);
                    $percentage = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;
                    
                    $colorClass = $percentage >= 100 
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                        : ($percentage >= 50 
                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200');
                    
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colorClass . '">' . $percentage . '% translated</span>';
                }),

            Column::make('Group', 'group')
                ->sortable()
                ->format(function ($value) {
                    return $value ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">' . $value . '</span>' : '<span class="text-gray-500 dark:text-gray-400">General</span>';
                }),

            Column::make('Last Updated', 'updated_at')
                ->sortable()
                ->format(function ($value) {
                    return $value ? '<span class="text-sm text-gray-600 dark:text-gray-300">' . $value->format('M j, Y H:i') . '</span>' : '<span class="text-gray-500 dark:text-gray-400">Never</span>';
                }),
        ];
    }

    protected function getBaseActions(): array
    {
        $actions = [];

        if ($this->canPerformAction('edit')) {
            $actions[] = Button::make('edit', 'Edit')
                ->class('btn-sm btn-secondary inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500')
                ->icon('heroicon-o-pencil')
                ->route('admin.translations.edit', fn ($row) => $row->id);
        }

        if ($this->canPerformAction('delete')) {
            $actions[] = Button::make('delete', 'Delete')
                ->class('btn-sm btn-danger inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-300 rounded-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500')
                ->icon('heroicon-o-trash')
                ->confirm('Are you sure you want to delete this translation key?')
                ->method('deleteTranslation');
        }

        return $actions;
    }

    protected function getBaseFilters(): array
    {
        $availableLanguages = config('app.available_locales', ['en', 'es', 'fr', 'de']);
        $languageOptions = array_map(fn ($lang) => ['name' => strtoupper($lang), 'value' => $lang], $availableLanguages);

        $groups = $this->getTranslationGroups();
        $groupOptions = array_map(fn ($group) => ['name' => ucfirst($group), 'value' => $group], $groups);

        return [
            Filter::inputText('search')
                ->placeholder('Search translations...'),

            Filter::select('language')
                ->dataSource($languageOptions)
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by language'),

            Filter::select('group')
                ->dataSource($groupOptions)
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by group'),

            Filter::select('status')
                ->dataSource([
                    ['name' => 'Complete', 'value' => 'complete'],
                    ['name' => 'Incomplete', 'value' => 'incomplete'],
                    ['name' => 'Missing', 'value' => 'missing'],
                ])
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by status'),

            Filter::datepicker('updated_at')
                ->label('Last Updated'),
        ];
    }

    protected function applyBaseQueryFilters($query)
    {
        // Note: This would need to be adapted based on your translation storage system
        $translations = $this->getTranslationsData();

        // Apply search filter
        if ($search = request('search')) {
            $translations = array_filter($translations, function ($translation) use ($search) {
                return stripos($translation['key'], $search) !== false ||
                       stripos($translation['en_value'], $search) !== false;
            });
        }

        // Apply language filter
        if ($language = request('language')) {
            $translations = array_filter($translations, function ($translation) use ($language) {
                return isset($translation['available_languages'][$language]);
            });
        }

        // Apply group filter
        if ($group = request('group')) {
            $translations = array_filter($translations, function ($translation) use ($group) {
                return $translation['group'] === $group;
            });
        }

        // Apply status filter
        if ($status = request('status')) {
            $translations = array_filter($translations, function ($translation) use ($status) {
                $completedCount = count(array_filter($translation['available_languages'] ?? []));
                $totalCount = count($translation['available_languages'] ?? []);
                $percentage = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;

                return match ($status) {
                    'complete' => $percentage >= 100,
                    'incomplete' => $percentage > 0 && $percentage < 100,
                    'missing' => $percentage === 0,
                    default => true,
                };
            });
        }

        return $this->createBuilderFromCollection(collect($translations));
    }

    protected function getHookPrefix(): string
    {
        return 'translation';
    }

    protected function getRouteName(): string
    {
        return 'translations';
    }

    protected function getModelClass(): string
    {
        return 'Translation'; // This would be your translation model/class
    }

    protected function getPermissionPrefix(): string
    {
        return 'translation';
    }

    /**
     * Get translations data from your translation system
     * This is a mock implementation - adapt to your actual translation storage
     */
    private function getTranslationsData(): array
    {
        // Mock data - replace with actual translation loading logic
        $locales = config('app.available_locales', ['en', 'es', 'fr', 'de']);
        $translations = [];

        // Example: Load from language files
        $enTranslations = [];
        $langPath = resource_path('lang/en.json');
        if (file_exists($langPath)) {
            $enTranslations = json_decode(file_get_contents($langPath), true) ?? [];
        }

        foreach ($enTranslations as $key => $value) {
            $availableLanguages = [];
            
            foreach ($locales as $locale) {
                $localePath = resource_path("lang/{$locale}.json");
                $localeTranslations = [];
                
                if (file_exists($localePath)) {
                    $localeTranslations = json_decode(file_get_contents($localePath), true) ?? [];
                }
                
                $availableLanguages[$locale] = isset($localeTranslations[$key]) && !empty($localeTranslations[$key]);
            }

            $translations[] = [
                'id' => md5($key),
                'key' => $key,
                'en_value' => $value,
                'available_languages' => $availableLanguages,
                'group' => $this->getTranslationGroup($key),
                'status' => $this->getTranslationStatus($availableLanguages),
                'updated_at' => now(),
            ];
        }

        return $translations;
    }

    /**
     * Get translation group from key
     */
    private function getTranslationGroup(string $key): string
    {
        if (str_contains($key, '.')) {
            return explode('.', $key)[0];
        }
        
        return 'general';
    }

    /**
     * Get translation groups
     */
    private function getTranslationGroups(): array
    {
        $translations = $this->getTranslationsData();
        $groups = array_unique(array_column($translations, 'group'));
        
        return array_values($groups);
    }

    /**
     * Get translation status
     */
    private function getTranslationStatus(array $availableLanguages): string
    {
        $completedCount = count(array_filter($availableLanguages));
        $totalCount = count($availableLanguages);
        
        if ($completedCount === 0) return 'missing';
        if ($completedCount === $totalCount) return 'complete';
        
        return 'incomplete';
    }

    /**
     * Create a Builder-like object from collection for PowerGrid compatibility
     */
    private function createBuilderFromCollection($collection)
    {
        // This is a simplified approach - you might need to use a proper Builder
        // or implement pagination for large translation sets
        return $collection;
    }

    /**
     * Custom delete method for translations
     */
    public function deleteTranslation(string $id): void
    {
        // Find translation by ID and delete from all language files
        $translations = $this->getTranslationsData();
        $translation = collect($translations)->firstWhere('id', $id);

        if (!$translation) {
            $this->dispatchBrowserEvent('show-message', [
                'type' => 'error',
                'message' => __('Translation not found.')
            ]);
            return;
        }

        // Apply hooks
        $translation = ld_apply_filters('translation_delete_before', $translation);
        
        // Delete from all language files
        $this->deleteTranslationFromFiles($translation['key']);
        
        ld_do_action('translation_delete_after', $translation);

        $this->dispatchBrowserEvent('show-message', [
            'type' => 'success',
            'message' => __('Translation deleted successfully.')
        ]);

        $this->fillData();
    }

    /**
     * Delete translation from all language files
     */
    private function deleteTranslationFromFiles(string $key): void
    {
        $locales = config('app.available_locales', ['en', 'es', 'fr', 'de']);
        
        foreach ($locales as $locale) {
            $langPath = resource_path("lang/{$locale}.json");
            
            if (file_exists($langPath)) {
                $translations = json_decode(file_get_contents($langPath), true) ?? [];
                
                if (isset($translations[$key])) {
                    unset($translations[$key]);
                    file_put_contents($langPath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            }
        }
    }
}