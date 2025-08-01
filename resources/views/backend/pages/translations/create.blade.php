<div
    x-cloak
    x-show="addLanguageModalOpen"
    x-transition.opacity.duration.200ms
    x-trap.inert.noscroll="addLanguageModalOpen"
    x-on:keydown.esc.window="addLanguageModalOpen = false"
    x-on:click.self="addLanguageModalOpen = false"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 p-4 backdrop-blur-md"
    role="dialog"
    aria-modal="true"
    aria-labelledby="add-language-modal-title"
>
    <div
        x-show="addLanguageModalOpen"
        x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
        x-transition:enter-start="opacity-0 scale-50"
        x-transition:enter-end="opacity-100 scale-100"
        class="flex max-w-md flex-col gap-4 overflow-hidden rounded-md border border-outline bg-white text-on-surface dark:border-outline-dark dark:bg-gray-700 dark:text-gray-300"
    >
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2 dark:border-gray-800">
            <h3 id="add-language-modal-title" class="font-semibold tracking-wide text-gray-700 dark:text-white">
                {{ __('Add New Language') }}
            </h3>
            <button
                x-on:click="addLanguageModalOpen = false"
                aria-label="close modal"
                class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-md p-1 dark:hover:bg-gray-600 dark:hover:text-white"
            >
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6 space-y-6">
            <form action="{{ route('admin.translations.create') }}" method="POST" id="add-language-form">
                @csrf
                <div class="mb-4">
                    <label for="language-code" class="block mb-2 text-sm font-medium text-gray-700 dark:text-white">
                        {{ __('Select Language') }}
                    </label>
                    <select id="language-code" name="language_code" class="h-11 w-full rounded-md border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" required>
                        <option value="">{{ __('Select a language') }}</option>
                        @foreach($allLanguages as $code => $languageName)
                            @if(!array_key_exists($code, $languages))
                                <option value="{{ $code }}">{{ $languageName }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="translation-group" class="block mb-2 text-sm font-medium text-gray-700 dark:text-white">
                        {{ __('Translation Group') }}
                    </label>
                    <select id="translation-group" name="group" class="h-11 w-full rounded-md border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" required>
                        <option value="json" selected>{{ __('General') }}</option>
                        @foreach($groups as $key => $name)
                            @if($key !== 'json')
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button type="button" x-on:click="addLanguageModalOpen = false" class="btn-default">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn-primary">
                        <iconify-icon icon="lucide:plus-circle" class="mr-2"></iconify-icon>{{ __('Add Language') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>