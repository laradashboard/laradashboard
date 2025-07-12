<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Term\StoreTermRequest;
use App\Http\Requests\Term\UpdateTermRequest;
use App\Http\Resources\TermResource;
use App\Models\Term;
use App\Services\TermService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TermController extends ApiController
{
    public function __construct(private readonly TermService $termService)
    {
    }

    /**
     * Display a listing of terms for a specific taxonomy.
     *
     * @tags Terms
     */
    public function index(Request $request, string $taxonomy): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['term.view']);

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
     * Store a newly created term.
     *
     * @tags Terms
     */
    public function store(StoreTermRequest $request, string $taxonomy): JsonResponse
    {
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
     * Display the specified term.
     *
     * @tags Terms
     */
    public function show(string $taxonomy, int $id): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['term.view']);

        $term = Term::with(['taxonomyModel', 'parent', 'children'])
            ->whereHas('taxonomyModel', function ($query) use ($taxonomy) {
                $query->where('name', $taxonomy);
            })
            ->findOrFail($id);

        return $this->resourceResponse(
            new TermResource($term),
            ucfirst($taxonomy) . ' term retrieved successfully'
        );
    }

    /**
     * Update the specified term.
     *
     * @tags Terms
     */
    public function update(UpdateTermRequest $request, string $taxonomy, int $id): JsonResponse
    {
        $term = Term::whereHas('taxonomyModel', function ($query) use ($taxonomy) {
            $query->where('name', $taxonomy);
        })->findOrFail($id);

        $updatedTerm = $this->termService->updateTerm($term, $request->validated());

        $this->logAction('Term Updated', $updatedTerm);

        return $this->resourceResponse(
            new TermResource($updatedTerm->load('taxonomyModel')),
            ucfirst($taxonomy) . ' term updated successfully'
        );
    }

    /**
     * Remove the specified term.
     *
     * @tags Terms
     */
    public function destroy(string $taxonomy, int $id): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['term.delete']);

        $term = Term::whereHas('taxonomyModel', function ($query) use ($taxonomy) {
            $query->where('name', $taxonomy);
        })->findOrFail($id);

        // Check if term has posts
        if ($term->posts()->count() > 0) {
            return $this->errorResponse('Cannot delete term with assigned posts', 400);
        }

        $term->delete();

        $this->logAction('Term Deleted', $term);

        return $this->successResponse(null, ucfirst($taxonomy) . ' term deleted successfully');
    }

    /**
     * Bulk delete terms.
     *
     * @tags Terms
     */
    public function bulkDelete(Request $request, string $taxonomy): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['term.delete']);

        $request->validate([
            /** @example [1, 2, 3] */
            'ids' => 'required|array|min:1',
            /** @example 1 */
            'ids.*' => 'integer|exists:terms,id',
        ]);

        $termIds = $request->input('ids');

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
