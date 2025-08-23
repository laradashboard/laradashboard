<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Term\BulkDeleteTermRequest;
use App\Http\Requests\Term\StoreTermRequest;
use App\Http\Requests\Term\UpdateTermRequest;
use App\Http\Resources\TermResource;
use App\Models\Term;
use App\Services\TermService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TermController extends ApiController
{
    public function __construct(private readonly TermService $termService)
    {
    }

    /**
     * Terms list.
     *
     * @tags Terms
     */
    #[QueryParameter('per_page', description: 'Number of terms per page.', type: 'int', default: 15, example: 20)]
    #[QueryParameter('search', description: 'Search term for filtering by name or description.', type: 'string', example: 'Laravel')]
    #[QueryParameter('parent', description: 'Filter terms by parent ID.', type: 'int', example: 1)]
    #[QueryParameter('sort', description: 'Sort terms by field (prefix with - for descending).', type: 'string', example: 'name')]
    public function index(Request $request, string $taxonomy): JsonResponse
    {
        $this->authorize('viewAny', Term::class);

        $filters = $request->only(['search', 'parent_id']);
        $filters['taxonomy'] = $taxonomy;
        $perPage = (int) ($request->input('per_page') ?? config('settings.default_pagination', 10));

        $terms = $this->termService->getPaginatedTerms($filters, $perPage);

        return $this->resourceResponse(
            TermResource::collection($terms)->additional([
                'meta' => [
                    'pagination' => [
                        'current_page' => $terms->currentPage(),
                        'last_page' => $terms->lastPage(),
                        'per_page' => $terms->perPage(),
                        'total' => $terms->total(),
                    ],
                    'taxonomy' => $taxonomy,
                ],
            ]),
            ucfirst($taxonomy) . ' terms retrieved successfully'
        );
    }

    /**
     * Create Term.
     *
     * @tags Terms
     */
    public function store(StoreTermRequest $request, string $taxonomy): JsonResponse
    {
        $this->authorize('create', Term::class);

        $data = $request->validated();

        $term = $this->termService->createTerm($data, $taxonomy);

        $this->logAction('Term Created', $term);

        return $this->resourceResponse(
            new TermResource($term->load('taxonomyModel')),
            ucfirst($taxonomy) . ' term created successfully',
            201
        );
    }

    /**
     * Show Term.
     *
     * @tags Terms
     */
    public function show(string $taxonomy, int $id): JsonResponse
    {
        $term = Term::with(['taxonomyModel', 'parent', 'children'])
            ->whereHas('taxonomyModel', function ($query) use ($taxonomy) {
                $query->where('name', $taxonomy);
            })
            ->findOrFail($id);

        $this->authorize('view', $term);

        return $this->resourceResponse(
            new TermResource($term),
            ucfirst($taxonomy) . ' term retrieved successfully'
        );
    }

    /**
     * Update Term.
     *
     * @tags Terms
     */
    public function update(UpdateTermRequest $request, string $taxonomy, int $id): JsonResponse
    {
        $term = Term::whereHas('taxonomyModel', function ($query) use ($taxonomy) {
            $query->where('name', $taxonomy);
        })->findOrFail($id);

        $this->authorize('update', $term);

        $updatedTerm = $this->termService->updateTerm($term, $request->validated());

        $this->logAction('Term Updated', $updatedTerm);

        return $this->resourceResponse(
            new TermResource($updatedTerm->load('taxonomyModel')),
            ucfirst($taxonomy) . ' term updated successfully'
        );
    }

    /**
     * Delete Term.
     *
     * @tags Terms
     */
    public function destroy(string $taxonomy, int $id): JsonResponse
    {
        $term = Term::whereHas('taxonomyModel', function ($query) use ($taxonomy) {
            $query->where('name', $taxonomy);
        })->findOrFail($id);

        $this->authorize('delete', $term);

        // Check if term has posts
        if ($term->posts()->count() > 0) {
            return $this->errorResponse('Cannot delete term with assigned posts', 400);
        }

        $term->delete();

        $this->logAction('Term Deleted', $term);

        return $this->successResponse(null, ucfirst($taxonomy) . ' term deleted successfully');
    }

    /**
     * Bulk Delete Terms.
     *
     * @tags Terms
     */
    public function bulkDelete(BulkDeleteTermRequest $request, string $taxonomy): JsonResponse
    {
        $termIds = $request->input('ids');

        $this->authorize('delete', Term::class);

        // Check if any terms have posts assigned
        $termsWithPosts = Term::whereIn('id', $termIds)->whereHas('posts')->count();
        if ($termsWithPosts > 0) {
            return $this->errorResponse('Cannot delete terms with assigned posts', 400);
        }

        $deletedCount = Term::whereHas('taxonomyModel', function ($query) use ($taxonomy) {
            $query->where('name', $taxonomy);
        })->whereIn('id', $termIds)->delete();

        $this->logAction('Bulk Term Deletion', null, [
            'taxonomy' => $taxonomy,
            'deleted_count' => $deletedCount,
        ]);

        return $this->successResponse(
            ['deleted_count' => $deletedCount],
            $deletedCount . " " . $taxonomy . " terms deleted successfully"
        );
    }
}
