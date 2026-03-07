<div x-data="{ addLanguageModalOpen: false }">
    <x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
        {!! Hook::applyFilters(CommonFilterHook::TRANSLATION_AFTER_BREADCRUMBS, '') !!}

        <div class="bg-white p-6 rounded-md shadow-md mb-6 dark:bg-gray-800">
            <div class="flex flex-col sm:flex-row mb-6 gap-4 justify-between">
                <div class="flex items-start sm:items-center gap-4">
                    <div class="flex items-center">
                        <label for="language-select" class="mr-4 text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Language:') }}
                        </label>
                        <select id="language-select"
                                class="h-11 rounded-md border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                onchange="updateLocation()">
                            @foreach($languages as $code => $language)
                                <option value="{{ $code }}" {{ $selectedLang === $code ? 'selected' : '' }}>{{ $language['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center">
                        <label for="group-select" class="mr-4 text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Translation Group') }}:
                        </label>
                        <select id="group-select"
                                class="h-11 rounded-md border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                onchange="updateLocation()">
                            @foreach($availableGroups as $group)
                                <option value="{{ $group }}" {{ $selectedGroup === $group ? 'selected' : '' }}>
                                    {{ $groups[$group] ?? ucfirst($group) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                    {{ __('Total Keys:') }} <span class="font-medium">{{ $translationStats['totalKeys'] }}</span> |
                    {{ __('Translated') }}: <span class="font-medium">{{ $translationStats['translated'] }}</span> |
                    {{ __('Missing:') }} <span class="font-medium">{{ $translationStats['missing'] }}</span>
                </p>
                <div class="h-3 w-full bg-gray-200 rounded-full dark:bg-gray-700">
                    <div class="h-3 bg-blue-600 rounded-full" style="width: {{ $translationStats['percentage'] }}%"></div>
                </div>
            </div>

            <div class="mb-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <div class="flex items-center gap-2">
                    <input type="text"
                           id="translation-search"
                           value="{{ $search }}"
                           placeholder="{{ __('Search translations...') }}"
                           class="h-11 rounded-md border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 w-64"
                    />
                    <button type="button" onclick="applySearch()" class="btn-default h-11">
                        <iconify-icon icon="lucide:search" class="text-base"></iconify-icon>
                    </button>
                    @if($search)
                        <button type="button" onclick="clearSearch()" class="btn-default h-11">
                            <iconify-icon icon="lucide:x" class="text-base"></iconify-icon>
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <label for="per-page-select" class="text-sm text-gray-600 dark:text-gray-300">{{ __('Per page:') }}</label>
                    <select id="per-page-select"
                            onchange="updateLocation()"
                            class="h-11 rounded-md border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @foreach([50, 100, 200] as $option)
                            <option value="{{ $option }}" {{ $pagination['per_page'] === $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Showing') }} {{ number_format(($pagination['current_page'] - 1) * $pagination['per_page'] + 1) }}-{{ number_format(min($pagination['current_page'] * $pagination['per_page'], $pagination['total_filtered'])) }}
                        {{ __('of') }} {{ number_format($pagination['total_filtered']) }}
                        @if($search)
                            ({{ __('filtered from') }} {{ number_format($pagination['total']) }})
                        @endif
                    </span>
                </div>
            </div>

            @if($selectedLang !== 'en' || ($selectedLang === 'en' && $selectedGroup !== 'json'))
                <form
                    id="translations-form"
                    data-prevent-unsaved-changes
                    data-chunk-url="{{ route('admin.translations.save-chunk') }}"
                    data-lang="{{ $selectedLang }}"
                    data-group="{{ $selectedGroup }}"
                >
                    <div class="mb-4 flex justify-end">
                        <button type="submit" class="btn-primary" id="save-translations-btn">
                            <iconify-icon icon="lucide:save" class="mr-2"></iconify-icon> {{ __('Save Translations') }}
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table min-w-full border divide-y divide-gray-200 dark:divide-gray-700 dark:border-gray-700">
                            <thead class="table-thead">
                                <tr>
                                    <th scope="col" class="table-thead-th">
                                        {{ __('Key') }}
                                    </th>
                                    <th scope="col" class="table-thead-th">
                                        {{ __('English Text') }}
                                    </th>
                                    <th scope="col" class="table-thead-th table-thead-th-last">
                                        {{ $languages[$selectedLang]['name'] ?? ucfirst($selectedLang) }} {{ __('Translation') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                @foreach($enTranslations as $key => $value)
                                    @if(!is_array($value))
                                        <tr class="{{ !isset($translations[$key]) ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                            <td class="table-td font-medium text-gray-700 dark:text-white">
                                                {{ $key }}
                                            </td>
                                            <td class="table-td text-gray-700 dark:text-gray-300">
                                                {{ $value }}
                                            </td>
                                            <td class="table-td text-gray-700 dark:text-gray-300">
                                                <textarea name="translations[{{ $key }}]" rows="1"
                                                    class="w-full rounded-md border border-gray-300 p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                    placeholder="">{{ $translations[$key] ?? '' }}</textarea>
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="bg-gray-100 dark:bg-gray-800">
                                            <td colspan="3" class="table-td font-medium text-gray-700 dark:text-white">
                                                <strong>{{ $key }}</strong>
                                            </td>
                                        </tr>
                                        @foreach($value as $nestedKey => $nestedValue)
                                            @if(!is_array($nestedValue))
                                                <tr class="{{ !isset($translations[$key][$nestedKey]) ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                                    <td class="table-td font-medium text-gray-700 dark:text-white pl-12">
                                                        {{ $nestedKey }}
                                                    </td>
                                                    <td class="table-td text-gray-500 dark:text-gray-300">
                                                        {{ $nestedValue }}
                                                    </td>
                                                    <td class="table-td text-gray-500 dark:text-gray-300">
                                                        <textarea name="translations[{{ $key }}][{{ $nestedKey }}]" rows="1"
                                                            class="w-full rounded-md border border-gray-300 p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                            placeholder="">{{ $translations[$key][$nestedKey] ?? '' }}</textarea>
                                                    </td>
                                                </tr>
                                            @else
                                                <tr class="bg-gray-50 dark:bg-gray-700">
                                                    <td colspan="3" class="table-td font-medium text-gray-700 dark:text-white pl-12">
                                                        <strong>{{ $key }}.{{ $nestedKey }}</strong>
                                                    </td>
                                                </tr>
                                                @foreach($nestedValue as $deepKey => $deepValue)
                                                    <tr class="{{ !isset($translations[$key][$nestedKey][$deepKey]) ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                                        <td class="table-td font-medium text-gray-700 dark:text-white pl-16">
                                                            {{ $deepKey }}
                                                        </td>
                                                        <td class="table-td text-gray-500 dark:text-gray-300">
                                                            {{ $deepValue }}
                                                        </td>
                                                        <td class="table-td text-gray-500 dark:text-gray-300">
                                                            <textarea name="translations[{{ $key }}][{{ $nestedKey }}][{{ $deepKey }}]" rows="1"
                                                                class="w-full rounded-md border border-gray-300 p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                                placeholder="">{{ $translations[$key][$nestedKey][$deepKey] ?? '' }}</textarea>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($pagination['total_pages'] > 1)
                        <div class="mt-6 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if($pagination['current_page'] > 1)
                                    <button type="button" onclick="goToPage(1)" class="btn-default text-sm" aria-label="{{ __('First page') }}">
                                        <iconify-icon icon="lucide:chevrons-left"></iconify-icon>
                                    </button>
                                    <button type="button" onclick="goToPage({{ $pagination['current_page'] - 1 }})" class="btn-default text-sm" aria-label="{{ __('Previous page') }}">
                                        <iconify-icon icon="lucide:chevron-left"></iconify-icon>
                                    </button>
                                @endif

                                <span class="text-sm text-gray-600 dark:text-gray-300 px-3">
                                    {{ __('Page') }} {{ $pagination['current_page'] }} {{ __('of') }} {{ $pagination['total_pages'] }}
                                </span>

                                @if($pagination['current_page'] < $pagination['total_pages'])
                                    <button type="button" onclick="goToPage({{ $pagination['current_page'] + 1 }})" class="btn-default text-sm" aria-label="{{ __('Next page') }}">
                                        <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                                    </button>
                                    <button type="button" onclick="goToPage({{ $pagination['total_pages'] }})" class="btn-default text-sm" aria-label="{{ __('Last page') }}">
                                        <iconify-icon icon="lucide:chevrons-right"></iconify-icon>
                                    </button>
                                @endif
                            </div>

                            <button type="submit" class="btn-primary">
                                <iconify-icon icon="lucide:save" class="mr-2"></iconify-icon> {{ __('Save Translations') }}
                            </button>
                        </div>
                    @else
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn-primary">
                                <iconify-icon icon="lucide:save" class="mr-2"></iconify-icon> {{ __('Save Translations') }}
                            </button>
                        </div>
                    @endif
                </form>
            @elseif($selectedLang === 'en' && $selectedGroup === 'json')
                <div class="bg-blue-50 p-4 rounded-md dark:bg-blue-900/20">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <iconify-icon icon="lucide:info" class="text-primary"></iconify-icon>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700 dark:text-primary">
                                {{ __('The base JSON translations for English cannot be edited. Please select another language or group to translate.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @include('backend.pages.translations.create')

        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Auto-resize textareas based on content
                const textareas = document.querySelectorAll('textarea');
                textareas.forEach(textarea => {
                    textarea.addEventListener('input', function() {
                        this.style.height = 'auto';
                        this.style.height = (this.scrollHeight) + 'px';
                    });

                    textarea.style.height = 'auto';
                    textarea.style.height = (textarea.scrollHeight) + 'px';
                });

                // Chunked form submission
                const form = document.getElementById('translations-form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        saveTranslations();
                    });
                }

                // Search on Enter key
                const searchInput = document.getElementById('translation-search');
                if (searchInput) {
                    searchInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            applySearch();
                        }
                    });
                }
            });

            async function saveTranslations() {
                const form = document.getElementById('translations-form');
                const btn = document.getElementById('save-translations-btn');
                const chunkUrl = form.dataset.chunkUrl;
                const lang = form.dataset.lang;
                const group = form.dataset.group;

                // Collect all translation values from textareas on current page
                const translations = {};
                const textareas = form.querySelectorAll('textarea[name^="translations"]');
                textareas.forEach(textarea => {
                    const name = textarea.getAttribute('name');
                    const value = textarea.value;

                    const keys = [];
                    const matches = name.matchAll(/\[([^\]]*)\]/g);
                    for (const match of matches) {
                        keys.push(match[1]);
                    }

                    if (keys.length === 1) {
                        translations[keys[0]] = value;
                    } else if (keys.length === 2) {
                        if (!translations[keys[0]]) translations[keys[0]] = {};
                        translations[keys[0]][keys[1]] = value;
                    } else if (keys.length === 3) {
                        if (!translations[keys[0]]) translations[keys[0]] = {};
                        if (!translations[keys[0]][keys[1]]) translations[keys[0]][keys[1]] = {};
                        translations[keys[0]][keys[1]][keys[2]] = value;
                    }
                });

                btn.disabled = true;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<iconify-icon icon="lucide:loader" class="mr-2 animate-spin"></iconify-icon> {{ __("Saving") }}...';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || '{{ csrf_token() }}';

                try {
                    const response = await fetch(chunkUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            lang: lang,
                            group: group,
                            translations: translations,
                            page: '{{ $pagination["current_page"] }}',
                            per_page: '{{ $pagination["per_page"] }}',
                            search: '{{ $search }}',
                        }),
                    });

                    if (!response.ok) {
                        throw new Error('Server returned ' + response.status);
                    }

                    const data = await response.json();

                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                } catch (error) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert('{{ __("Failed to save translations. Please try again.") }}\n' + error.message);
                }
            }

            function buildUrl(params) {
                const url = new URL('{{ route("admin.translations.index") }}', window.location.origin);
                Object.entries(params).forEach(([key, value]) => {
                    if (value !== null && value !== '') {
                        url.searchParams.set(key, value);
                    }
                });
                return url.toString();
            }

            function updateLocation() {
                const lang = document.getElementById('language-select').value;
                const group = document.getElementById('group-select').value;
                const perPage = document.getElementById('per-page-select')?.value || '50';
                const search = document.getElementById('translation-search')?.value || '';
                window.location.href = buildUrl({ lang, group, per_page: perPage, search });
            }

            function applySearch() {
                const search = document.getElementById('translation-search').value;
                const lang = document.getElementById('language-select').value;
                const group = document.getElementById('group-select').value;
                const perPage = document.getElementById('per-page-select')?.value || '50';
                window.location.href = buildUrl({ lang, group, per_page: perPage, search, page: 1 });
            }

            function clearSearch() {
                const lang = document.getElementById('language-select').value;
                const group = document.getElementById('group-select').value;
                const perPage = document.getElementById('per-page-select')?.value || '50';
                window.location.href = buildUrl({ lang, group, per_page: perPage });
            }

            function goToPage(page) {
                const lang = document.getElementById('language-select').value;
                const group = document.getElementById('group-select').value;
                const perPage = document.getElementById('per-page-select')?.value || '50';
                const search = document.getElementById('translation-search')?.value || '';
                window.location.href = buildUrl({ lang, group, per_page: perPage, search, page });
            }
        </script>
        @endpush
    </x-layouts.backend-layout>
</div>