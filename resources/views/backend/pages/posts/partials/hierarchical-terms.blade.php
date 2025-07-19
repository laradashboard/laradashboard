<div class="flex items-start mb-2 {{ $level > 0 ? 'ml-' . ($level * 4) : '' }}">
    <input type="checkbox" name="taxonomy_{{ $taxonomy->name }}[]" id="term_{{ $term->id }}" value="{{ $term->id }}"
        class="form-checkbox mt-1 h-4 w-4 text-brand-500 border-gray-300 rounded focus:ring-brand-400 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-brand-500"
        {{ in_array($term->id, old('taxonomy_' . $taxonomy->name, $selectedTerms)) ? 'checked' : '' }}>
    <label for="term_{{ $term->id }}" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
        {{ $term->name }}
    </label>
</div>

@php
    $childTerms = App\Models\Term::where('taxonomy', $taxonomy->name)
        ->where('parent_id', $term->id)
        ->orderBy('name', 'asc')
        ->get();
@endphp

@if($childTerms->count() > 0)
    <div class="ml-6 border-l border-gray-200 dark:border-gray-700 pl-2 space-y-1" data-term-children="{{ $term->id }}">
        @foreach($childTerms as $childTerm)
            @include('backend.pages.posts.partials.hierarchical-terms', [
                'term' => $childTerm,
                'taxonomy' => $taxonomy,
                'level' => $level + 1,
                'selectedTerms' => $selectedTerms
            ])
        @endforeach
    </div>
@endif
