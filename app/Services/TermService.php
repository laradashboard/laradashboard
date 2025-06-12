<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Term;
use App\Services\Content\ContentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TermService
{
    public function __construct(
        private readonly ContentService $contentService
    ) {
    }

    /**
     * Get terms with filters.
     *
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTerms(array $filters = [])
    {
        // Set default taxonomy if not provided.
        if (! isset($filters['taxonomy'])) {
            $filters['taxonomy'] = 'category';
        }

        // Create base query with taxonomy filter.
        $query = Term::where('taxonomy', $filters['taxonomy']);
        $query = $query->applyFilters($filters);

        return $query->paginateData([
            'per_page' => config('settings.default_pagination') ?? 20,
        ]);
    }

    /**
     * Get a term by ID.
     */
    public function getTermById(int $id, ?string $taxonomy = null): ?Term
    {
        $query = Term::query();

        if ($taxonomy) {
            $query->where('taxonomy', $taxonomy);
        }

        return $query->findOrFail($id);
    }

    /**
     * Get terms for dropdown.
     */
    public function getTermsDropdown(string $taxonomy)
    {
        return Term::where('taxonomy', $taxonomy)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get taxonomy model by name.
     */
    public function getTaxonomy(string $taxonomy)
    {
        return $this->contentService->getTaxonomies()->where('name', $taxonomy)->first();
    }

    /**
     * Create a new term.
     */
    public function createTerm(array $data, string $taxonomy): Term
    {
        $term = new Term();
        $term->name = $data['name'];
        $term->slug = $term->generateSlugFromString($data['slug'] ?? $data['name'] ?? '');
        $term->taxonomy = $taxonomy;
        $term->description = $data['description'] ?? null;
        $term->parent_id = $data['parent_id'] ?? null;

        // Handle featured image if provided
        if (isset($data['featured_image']) && $data['featured_image'] instanceof UploadedFile) {
            $term->featured_image = $this->handleImageUpload($data['featured_image']);
        }

        $term->save();

        return $term;
    }

    /**
     * Update an existing term.
     */
    public function updateTerm(Term $term, array $data): Term
    {
        $term->name = $data['name'];

        // Generate slug if needed
        $slug = $data['slug'] ?? '';
        if ($term->slug !== $slug) {
            $slugSource = !empty($slug) ? $slug : $data['name'];
            $term->slug = $term->generateSlugFromString($slugSource, $term->getKey());
        }

        $term->description = $data['description'] ?? null;
        $term->parent_id = $data['parent_id'] ?? null;

        // Handle featured image upload
        if (isset($data['featured_image']) && $data['featured_image'] instanceof UploadedFile) {
            // Delete old image if exists
            if ($term->featured_image) {
                Storage::disk('public')->delete($term->featured_image);
            }
            $term->featured_image = $this->handleImageUpload($data['featured_image']);
        }

        // Handle image removal
        if (isset($data['remove_featured_image']) && $data['remove_featured_image'] && $term->featured_image) {
            Storage::disk('public')->delete($term->featured_image);
            $term->featured_image = null;
        }

        $term->save();

        return $term;
    }

    /**
     * Delete a term.
     */
    public function deleteTerm(Term $term): bool
    {
        // Check if term has posts
        if ($term->posts()->count() > 0) {
            return false;
        }

        // Check if term has children
        if ($term->children()->count() > 0) {
            return false;
        }

        // Delete featured image if exists
        if ($term->featured_image) {
            Storage::disk('public')->delete($term->featured_image);
        }

        return $term->delete();
    }

    /**
     * Check if term can be deleted.
     */
    public function canDeleteTerm(Term $term): array
    {
        $errors = [];

        if ($term->posts()->count() > 0) {
            $errors[] = 'has_posts';
        }

        if ($term->children()->count() > 0) {
            $errors[] = 'has_children';
        }

        return $errors;
    }

    /**
     * Check if slug exists.
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Term::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Handle image upload.
     */
    private function handleImageUpload(UploadedFile $file): string
    {
        return $file->store('terms', 'public');
    }

    /**
     * Get taxonomy label for messages.
     */
    public function getTaxonomyLabel(string $taxonomy, bool $singular = false): string
    {
        $taxonomyModel = $this->getTaxonomy($taxonomy);

        if ($taxonomyModel) {
            return $singular
                ? ($taxonomyModel->label_singular ?? Str::title($taxonomy))
                : ($taxonomyModel->label ?? Str::title($taxonomy));
        }

        return Str::title($taxonomy);
    }
}
