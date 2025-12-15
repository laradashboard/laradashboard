<div class="flex">
    @if ($taxonomyModel->show_featured_image)
        <div class="mt-2">
            <x-media-selector
                name="featured_image"
                label="{{ __('Featured Image') }}"
                :multiple="false"
                allowedTypes="images"
                :existingMedia="($term && $term->hasFeaturedImage()) ? $term->getFeaturedImageUrl() : null"
                :existingAltText="$term ? $term->featured_image_alt_text : ''"
                removeCheckboxName="remove_featured_image"
                removeCheckboxLabel="{{ __('Remove featured image') }}"
                :showPreview="true"
            />
        </div>
    @endif
</div>

@if ($taxonomyModel->hierarchical)
    <div class="mt-2">
        <x-posts.term-selector name="parent_id" :taxonomyModel="$taxonomyModel" :term="$term" :parentTerms="$parentTerms"
            :placeholder="__('Select Parent ' . $taxonomyModel->label_singular)" :label='__("Parent {$taxonomyModel->label_singular}")' searchable="false" />
    </div>
@endif