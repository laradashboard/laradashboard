<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Term\StoreTermRequest;
use App\Http\Requests\Term\UpdateTermRequest;
use App\Models\Term;
use App\Services\Content\ContentService;
use App\Services\TermService;
use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function __construct(
        private readonly ContentService $contentService,
        private readonly TermService $termService
    ) {
    }

    public function index(Request $request, string $taxonomy)
    {
        $this->authorize('viewAny', Term::class);

        // Get taxonomy using service
        $taxonomyModel = $this->termService->getTaxonomy($taxonomy);

        if (! $taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        // Prepare filters
        $filters = [
            'taxonomy' => $taxonomy,
            'search' => $request->search,
        ];

        // Get terms with pagination using service
        $terms = $this->termService->getTerms($filters);

        // Get parent terms for hierarchical taxonomies.
        $parentTerms = [];
        if ($taxonomyModel->hierarchical) {
            $parentTerms = Term::where('taxonomy', $taxonomy)
                ->orderBy('name', 'asc')
                ->get();
        }

        // Get term being edited if exists
        $term = null;
        if ($request->has('edit') && is_numeric($request->edit)) {
            $term = Term::findOrFail($request->edit);
        }

        return view('backend.pages.terms.index', compact('terms', 'taxonomy', 'taxonomyModel', 'parentTerms', 'term'))
            ->with([
                'breadcrumbs' => [
                    'title' => $taxonomyModel->label,
                ],
            ]);
    }

    public function store(StoreTermRequest $request, string $taxonomy)
    {
        $this->authorize('create', Term::class);
        // Get taxonomy using service
        $taxonomyModel = $this->termService->getTaxonomy($taxonomy);

        if (! $taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        // Create term using service
        $term = $this->termService->createTerm($request->validated(), $taxonomy);

        // Get taxonomy label for message
        $taxLabel = $this->termService->getTaxonomyLabel($taxonomy, true);

        return redirect()->route('admin.terms.index', $taxonomy)
            ->with('success', __(':taxLabel created successfully', ['taxLabel' => $taxLabel]));
    }

    public function update(UpdateTermRequest $request, string $taxonomy, string $id)
    {
        // Get taxonomy using service
        $taxonomyModel = $this->termService->getTaxonomy($taxonomy);

        if (! $taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        // Get term using service
        $term = $this->termService->getTermById((int) $id, $taxonomy);

        $this->authorize('update', $term);

        // Update term using service
        $this->termService->updateTerm($term, $request->validated());

        // Get taxonomy label for message
        $taxLabel = $this->termService->getTaxonomyLabel($taxonomy, true);

        return redirect()->route('admin.terms.index', $taxonomy)
            ->with('success', __(':taxLabel updated successfully', ['taxLabel' => $taxLabel]));
    }

    public function destroy(string $taxonomy, string $id)
    {
        // Get taxonomy using service
        $taxonomyModel = $this->termService->getTaxonomy($taxonomy);

        if (! $taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        // Get term using service
        $term = $this->termService->getTermById((int) $id, $taxonomy);

        $this->authorize('delete', $term);

        // Get taxonomy label for messages
        $taxLabel = $this->termService->getTaxonomyLabel($taxonomy, true);

        // Check if term can be deleted
        $errors = $this->termService->canDeleteTerm($term);

        if (in_array('has_posts', $errors)) {
            return redirect()->route('admin.terms.index', $taxonomy)
                ->with('error', __('Cannot delete :taxLabel as it is associated with posts', ['taxLabel' => $taxLabel]));
        }

        if (in_array('has_children', $errors)) {
            return redirect()->route('admin.terms.index', $taxonomy)
                ->with('error', __('Cannot delete :taxLabel as it has child items', ['taxLabel' => $taxLabel]));
        }

        // Delete term using service
        $this->termService->deleteTerm($term);

        return redirect()->route('admin.terms.index', $taxonomy)
            ->with('success', __(':taxLabel deleted successfully', ['taxLabel' => $taxLabel]));
    }

    public function edit(string $taxonomy, string $term)
    {
        // Get taxonomy using service
        $taxonomyModel = $this->termService->getTaxonomy($taxonomy);

        if (! $taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        // Get term using service
        $term = $this->termService->getTermById((int) $term, $taxonomy);

        $this->authorize('update', $term);

        // Get parent terms for hierarchical taxonomies.
        $parentTerms = [];
        if ($taxonomyModel->hierarchical) {
            $parentTerms = Term::where('taxonomy', $taxonomy)
                ->orderBy('name', 'asc')
                ->get();
        }

        return view('backend.pages.terms.edit', compact('taxonomy', 'taxonomyModel', 'term', 'parentTerms'))
            ->with('breadcrumbs', [
                'title' => __('Edit :taxLabel', ['taxLabel' => $taxonomyModel->label_singular]),
                'items' => [
                    [
                        'label' => $taxonomyModel->label,
                        'url' => route('admin.terms.index', $taxonomy),
                    ],
                ],
            ]);
    }

    /**
     * Delete multiple terms at once
     */
    public function bulkDelete(Request $request, string $taxonomy)
    {
        $this->authorize('bulkDelete', Term::class);

        // Get taxonomy using service
        $taxonomyModel = $this->termService->getTaxonomy($taxonomy);

        if (! $taxonomyModel) {
            return redirect()->route('admin.posts.index')
                ->with('error', __('Taxonomy not found'));
        }

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->route('admin.terms.index', $taxonomy)
                ->with('error', __('No terms selected for deletion'));
        }

        // Get taxonomy label for messages
        $taxLabel = $this->termService->getTaxonomyLabel($taxonomy, true);
        $deletedCount = 0;
        $errorMessages = [];

        foreach ($ids as $id) {
            // Get term using service
            $term = $this->termService->getTermById((int) $id, $taxonomy);

            if (! $term) {
                continue;
            }

            // Check if term can be deleted
            $errors = $this->termService->canDeleteTerm($term);

            if (! empty($errors)) {
                if (in_array('has_posts', $errors)) {
                    $errorMessages[] = __('":name" cannot be deleted as it is associated with posts', ['name' => $term->name]);
                }

                if (in_array('has_children', $errors)) {
                    $errorMessages[] = __('":name" cannot be deleted as it has child items', ['name' => $term->name]);
                }

                continue;
            }

            // Delete term using service
            $this->termService->deleteTerm($term);
            $deletedCount++;
        }

        if ($deletedCount > 0) {
            session()->flash('success', __(':count :taxLabel deleted successfully', [
                'count' => $deletedCount,
                'taxLabel' => strtolower($taxonomyModel->label),
            ]));
        }

        if (! empty($errorMessages)) {
            session()->flash('error', implode('<br>', $errorMessages));
        } elseif ($deletedCount === 0) {
            session()->flash('error', __('No :taxLabel were deleted', ['taxLabel' => strtolower($taxonomyModel->label)]));
        }

        return redirect()->route('admin.terms.index', $taxonomy);
    }
}
