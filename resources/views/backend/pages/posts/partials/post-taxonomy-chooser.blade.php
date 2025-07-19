@php
$selectedTerms = [];

if (!empty($post)) {
    foreach ($post->terms as $term) {
        if (!isset($selectedTerms[$term->taxonomy])) {
            $selectedTerms[$term->taxonomy] = [];
        }
        $selectedTerms[$term->taxonomy][] = $term->id;
    }
}
@endphp
<div id="taxonomy-{{ $taxonomy->name }}">
    <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="px-4 py-3 sm:px-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
            <h3 class="text-base font-medium text-gray-700 dark:text-white">{{ __($taxonomy->label) }}</h3>
            <x-term-drawer :taxonomy="$taxonomy" :taxonomyName="$taxonomy->name" :post_id="$post->id ?? null" :post_type="$post_type" />
        </div>
        <div class="p-3 space-y-2 sm:p-4" data-taxonomy="{{ $taxonomy->name }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Select " . strtolower($taxonomy->label)) . ":" }}</label>
                <div class="mt-2 max-h-60 overflow-y-auto terms-list">
                    @php
                        if ($taxonomy->hierarchical) {
                            // For hierarchical taxonomies, get parent terms first (terms with no parent)
                            $parentTerms = App\Models\Term::where('taxonomy', $taxonomy->name)
                                ->whereNull('parent_id')
                                ->orderBy('name', 'asc')
                                ->get();

                            // Check if we have any terms
                            $hasTerms = $parentTerms->count() > 0 ||
                                App\Models\Term::where('taxonomy', $taxonomy->name)->count() > 0;
                        } else {
                            // For flat taxonomies, get all terms
                            $terms = App\Models\Term::where('taxonomy', $taxonomy->name)
                                ->orderBy('name', 'asc')
                                ->get();
                            $hasTerms = $terms->count() > 0;
                        }
                    @endphp

                    @if($hasTerms)
                        @if($taxonomy->hierarchical)
                            <div class="space-y-1 px-1">
                                @foreach($parentTerms as $parentTerm)
                                    @include('backend.pages.posts.partials.hierarchical-terms', [
                                        'term' => $parentTerm,
                                        'taxonomy' => $taxonomy,
                                        'level' => 0,
                                        'selectedTerms' => $selectedTerms[$taxonomy->name] ?? []
                                    ])
                                @endforeach
                            </div>
                        @else
                            @foreach($terms as $term)
                                <div class="flex items-start mb-2">
                                    <input type="checkbox" name="taxonomy_{{ $taxonomy->name }}[]" id="term_{{ $term->id }}" value="{{ $term->id }}"
                                        class="form-checkbox mt-1 h-4 w-4 text-brand-500 border-gray-300 rounded focus:ring-brand-400 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-brand-500"
                                        {{ in_array($term->id, old('taxonomy_' . $taxonomy->name, $selectedTerms[$taxonomy->name] ?? [])) ? 'checked' : '' }}>
                                    <label for="term_{{ $term->id }}" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                        {{ __($term->name) }}
                                    </label>
                                </div>
                            @endforeach
                        @endif
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-300 no-terms-message">{{ __('No') }} {{ strtolower($taxonomy->label) }} {{ __('found.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
{!! ld_apply_filters('post_form_after_taxonomy_' . $taxonomy->name, '') !!}
