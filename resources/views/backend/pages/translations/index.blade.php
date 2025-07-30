@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

        {!! ld_apply_filters('translation_after_breadcrumbs', '') !!}

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

                <div class="place-items-end mt-4 sm:mt-0">
                    @if(auth()->user()->can('translations.edit'))
                        <button data-modal-target="add-language-modal" data-modal-toggle="add-language-modal" class="btn-primary" id="add-language-button">
                            <iconify-icon icon="lucide:plus-circle" class="mr-2"></iconify-icon>{{ __('New Language') }}
                        </button>
                    @endif
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

            @if($selectedLang !== 'en' || ($selectedLang === 'en' && $selectedGroup !== 'json'))
                <form action="{{ route('admin.translations.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="lang" value="{{ $selectedLang }}">
                    <input type="hidden" name="group" value="{{ $selectedGroup }}">

                    <div class="mb-4 flex justify-end">
                        <button type="submit" class="btn-primary">
                            <iconify-icon icon="lucide:save" class="mr-2"></iconify-icon> {{ __('Save Translations') }}
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border divide-y divide-gray-200 dark:divide-gray-700 dark:border-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                        {{ __('Key') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                        {{ __('English Text') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                        {{ $languages[$selectedLang]['name'] ?? ucfirst($selectedLang) }} {{ __('Translation') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                @foreach($enTranslations as $key => $value)
                                    @if(!is_array($value))
                                        <tr class="{{ !isset($translations[$key]) ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-700 dark:text-white">
                                                {{ $key }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                                {{ $value }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                                <textarea name="translations[{{ $key }}]" rows="1"
                                                    class="w-full rounded-md border border-gray-300 p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                    placeholder="">{{ $translations[$key] ?? '' }}</textarea>
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="bg-gray-100 dark:bg-gray-800">
                                            <td colspan="3" class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-white">
                                                <strong>{{ $key }}</strong>
                                            </td>
                                        </tr>
                                        @foreach($value as $nestedKey => $nestedValue)
                                            @if(!is_array($nestedValue))
                                                <tr class="{{ !isset($translations[$key][$nestedKey]) ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                                    <td class="px-6 py-4 text-sm font-medium text-gray-700 dark:text-white pl-12">
                                                        {{ $nestedKey }}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $nestedValue }}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                                        <textarea name="translations[{{ $key }}][{{ $nestedKey }}]" rows="1"
                                                            class="w-full rounded-md border border-gray-300 p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                            placeholder="">{{ $translations[$key][$nestedKey] ?? '' }}</textarea>
                                                    </td>
                                                </tr>
                                            @else
                                                <tr class="bg-gray-50 dark:bg-gray-700">
                                                    <td colspan="3" class="px-6 py-1 text-sm font-medium text-gray-700 dark:text-white pl-12">
                                                        <strong>{{ $key }}.{{ $nestedKey }}</strong>
                                                    </td>
                                                </tr>
                                                @foreach($nestedValue as $deepKey => $deepValue)
                                                    <tr class="{{ !isset($translations[$key][$nestedKey][$deepKey]) ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                                        <td class="px-6 py-4 text-sm font-medium text-gray-700 dark:text-white pl-16">
                                                            {{ $deepKey }}
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                                            {{ $deepValue }}
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
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

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn-primary">
                            <iconify-icon icon="lucide:save" class="mr-2"></iconify-icon> {{ __('Save Translations') }}
                        </button>
                    </div>
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

                // Initialize height
                textarea.style.height = 'auto';
                textarea.style.height = (textarea.scrollHeight) + 'px';
            });
        });

        function updateLocation() {
            const lang = document.getElementById('language-select').value;
            const group = document.getElementById('group-select').value;
            window.location.href = '{{ route('admin.translations.index') }}?lang=' + lang + '&group=' + group;
        }
    </script>
    @endpush
@endsection
