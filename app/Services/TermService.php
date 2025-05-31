<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Term;
use Illuminate\Pagination\LengthAwarePaginator;

class TermService
{
    /**
     * Get terms with filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getTerms(array $filters = []): LengthAwarePaginator
    {
        // Set default taxonomy if not provided.
        if (!isset($filters['taxonomy'])) {
            $filters['taxonomy'] = 'category';
        }

        // Create base query with taxonomy filter.
        $query = Term::where('taxonomy', $filters['taxonomy']);

        return $query->applyFilters($filters)
            ->paginateData([
                'per_page' => config('settings.default_pagination') ?? 20
            ]);
    }

    /**
     * Get a term by ID.
     *
     * @param int $id
     * @param string|null $taxonomy
     * @return Term|null
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
     *
     * @param string $taxonomy
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTermsDropdown(string $taxonomy)
    {
        return Term::where('taxonomy', $taxonomy)
            ->orderBy('name', 'asc')
            ->get();
    }
}
