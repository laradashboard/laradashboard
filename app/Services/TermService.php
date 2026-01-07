<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Hooks\TermActionHook;
use App\Enums\Hooks\TermFilterHook;
use App\Models\Term;
use App\Services\Content\ContentService;
use App\Support\Facades\Hook;
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
        // Fire action before term creation
        Hook::doAction(TermActionHook::TERM_CREATED_BEFORE, $data, $taxonomy);

        // Allow filtering of term data
        $data = Hook::applyFilters(TermFilterHook::TERM_CREATED_BEFORE, $data);

        $term = new Term();
        $term->name = $data['name'];
        $term->slug = $term->generateSlugFromString($data['slug'] ?? $data['name'] ?? '');
        $term->taxonomy = $taxonomy;
        $term->description = $data['description'] ?? null;
        $term->parent_id = $data['parent_id'] ?? null;
        $term->save();

        if (isset($data['featured_image']) && ! empty($data['featured_image'])) {
            if ($data['featured_image'] instanceof UploadedFile) {
                $term->addMedia($data['featured_image'])->toMediaCollection('featured');
                Hook::doAction(TermActionHook::TERM_FEATURED_IMAGE_ADDED, $term, $data['featured_image']);
            } elseif (is_string($data['featured_image'])) {
                $this->mediaLibraryService->associateExistingMedia(
                    $term,
                    $data['featured_image'],
                    'featured'
                );
                Hook::doAction(TermActionHook::TERM_FEATURED_IMAGE_ADDED, $term, $data['featured_image']);
            }
        }

        // Fire action after term creation
        Hook::doAction(TermActionHook::TERM_CREATED_AFTER, $term);

        return Hook::applyFilters(TermFilterHook::TERM_CREATED_AFTER, $term);
    }

    public function updateTerm(Term $term, array $data): Term
    {
        // Fire action before term update
        Hook::doAction(TermActionHook::TERM_UPDATED_BEFORE, $term, $data);

        // Allow filtering of term data
        $data = Hook::applyFilters(TermFilterHook::TERM_UPDATED_BEFORE, $data);

        $oldParentId = $term->parent_id;

        $term->name = $data['name'];

        // Generate slug if needed
        $slug = $data['slug'] ?? '';
        if ($term->slug !== $slug) {
            $slugSource = ! empty($slug) ? $slug : $data['name'];
            $term->slug = $term->generateSlugFromString($slugSource, 'slug');
        }

        $term->description = $data['description'] ?? null;
        $term->parent_id = $data['parent_id'] ?? null;
        $term->save();

        // Fire parent changed action if parent changed
        if ($oldParentId !== $term->parent_id) {
            Hook::doAction(TermActionHook::TERM_PARENT_CHANGED, $term, $oldParentId, $term->parent_id);
        }

        if (isset($data['remove_featured_image']) && $data['remove_featured_image']) {
            $term->clearMediaCollection('featured');
            Hook::doAction(TermActionHook::TERM_FEATURED_IMAGE_REMOVED, $term);
        } elseif (isset($data['featured_image']) && ! empty($data['featured_image'])) {
            $term->clearMediaCollection('featured');

            if ($data['featured_image'] instanceof UploadedFile) {
                $term->addMedia($data['featured_image'])->toMediaCollection('featured');
                Hook::doAction(TermActionHook::TERM_FEATURED_IMAGE_ADDED, $term, $data['featured_image']);
            } elseif (is_string($data['featured_image'])) {
                $this->mediaLibraryService->associateExistingMedia(
                    $term,
                    $data['featured_image'],
                    'featured'
                );
                Hook::doAction(TermActionHook::TERM_FEATURED_IMAGE_ADDED, $term, $data['featured_image']);
            }
        }

        // Fire action after term update
        Hook::doAction(TermActionHook::TERM_UPDATED_AFTER, $term);

        return Hook::applyFilters(TermFilterHook::TERM_UPDATED_AFTER, $term);
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

        $termId = $term->id;
        $taxonomy = $term->taxonomy;

        // Fire action before term deletion
        Hook::doAction(TermActionHook::TERM_DELETED_BEFORE, $term);

        $deleted = $term->delete();

        // Fire action after term deletion
        if ($deleted) {
            Hook::doAction(TermActionHook::TERM_DELETED_AFTER, $termId, $taxonomy);
        }

        return $deleted;
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
