<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Term;
use App\Services\Content\ContentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TermService
{
    public function __construct(
        private readonly ContentService $contentService,
        private readonly MediaLibraryService $mediaLibraryService
    ) {
    }

    public function getTerms(array $filters = []): LengthAwarePaginator
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

    public function getTermById(int|string $id, ?string $taxonomy = null): ?Term
    {
        $query = Term::query();

        if (is_numeric($id)) {
            $query->where('id', (int) $id);
        } else {
            $query->where('slug', $id);
        }

        if ($taxonomy) {
            $query->where('taxonomy', $taxonomy);
        }

        return $query->first();
    }

    public function getTermsDropdown(string $taxonomy)
    {
        return Term::where('taxonomy', $taxonomy)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getTaxonomy(string $taxonomy)
    {
        return $this->contentService->getTaxonomies()->where('name', $taxonomy)->first();
    }

    public function createTerm(array $data, string $taxonomy): Term
    {
        $term = new Term();
        $term->name = $data['name'];
        $term->slug = $term->generateSlugFromString($data['slug'] ?? $data['name'] ?? '', 'terms');
        $term->taxonomy = $taxonomy;
        $term->description = $data['description'] ?? null;
        $term->parent_id = $data['parent_id'] ?? null;
        $term->save();

        if (isset($data['featured_image']) && ! empty($data['featured_image'])) {
            if ($data['featured_image'] instanceof UploadedFile) {
                $term->addMedia($data['featured_image'])->toMediaCollection('featured');
            } elseif (is_string($data['featured_image'])) {
                $this->mediaLibraryService->associateExistingMedia(
                    $term,
                    $data['featured_image'],
                    'featured'
                );
            }
        }

        return $term;
    }

    public function updateTerm(Term $term, array $data): Term
    {
        $term->name = $data['name'];

        // Generate slug if needed
        $slug = $data['slug'] ?? '';
        if ($term->slug !== $slug) {
            $slugSource = ! empty($slug) ? $slug : $data['name'];
            $term->slug = $term->generateSlugFromString($slugSource, 'terms');
        }

        $term->description = $data['description'] ?? null;
        $term->parent_id = $data['parent_id'] ?? null;
        $term->save();

        if (isset($data['remove_featured_image']) && $data['remove_featured_image']) {
            $term->clearMediaCollection('featured');
        } elseif (isset($data['featured_image']) && ! empty($data['featured_image'])) {
            $term->clearMediaCollection('featured');

            if ($data['featured_image'] instanceof UploadedFile) {
                $term->addMedia($data['featured_image'])->toMediaCollection('featured');
            } elseif (is_string($data['featured_image'])) {
                $this->mediaLibraryService->associateExistingMedia(
                    $term,
                    $data['featured_image'],
                    'featured'
                );
            }
        }

        return $term;
    }

    public function deleteTerm(Term $term): bool
    {
        // Check if term has posts.
        if ($term->posts()->count() > 0) {
            return false;
        }

        // Check if term has children.
        if ($term->children()->count() > 0) {
            return false;
        }

        return $term->delete();
    }

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

    public function getPaginatedTerms(array $filters = [], int $perPage = 10)
    {
        // Set default taxonomy if not provided.
        if (! isset($filters['taxonomy'])) {
            $filters['taxonomy'] = 'category';
        }

        // Create base query with taxonomy filter.
        $query = Term::where('taxonomy', $filters['taxonomy']);
        $query = $query->applyFilters($filters);

        return $query->paginate($perPage);
    }
}
