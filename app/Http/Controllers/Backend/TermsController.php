<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Term\StoreTermRequest;
use App\Http\Requests\Term\UpdateTermRequest;
use App\Models\Term;
use App\Services\Content\ContentService;
use App\Services\TermService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TermsController extends Controller
{
    public function __construct(
        private readonly ContentService $contentService,
        private readonly TermService $termService
    ) {
    }

    public function index(Request $request, string $taxonomy)
    {
        $this->checkAuthorization(auth()->user(), ['term.view']);

        // Get taxonomy.
        $taxonomyModel = $this->contentService->getTaxonomies()->where('name', $taxonomy)->first();

        if (!$taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        // Prepare filters
        $filters = [
            'taxonomy' => $taxonomy,
            'search' => $request->search
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
                ]
            ]);
    }

    public function store(StoreTermRequest $request, string $taxonomy)
    {
        // Get taxonomy.
        $taxonomyModel = $this->contentService->getTaxonomies()->where('name', $taxonomy)->first();

        if (!$taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        // Create term.
        $term = new Term();
        $term->name = $request->name;
        $term->slug = $request->slug ?: Str::slug($request->name);
        $term->taxonomy = $taxonomy;
        $term->description = $request->description;
        $term->parent_id = $request->parent_id;

        // Handle featured image upload.
        if ($request->hasFile('featured_image') && $taxonomyModel->show_featured_image) {
            $imagePath = $request->file('featured_image')->store('terms', 'public');
            $term->featured_image = $imagePath;
        }

        $term->save();

        // Get taxonomy label for message.
        $taxLabel = $taxonomyModel->label_singular ?? Str::title($taxonomy);

        return redirect()->route('admin.terms.index', $taxonomy)
            ->with('success', __(':taxLabel created successfully', ['taxLabel' => $taxLabel]));
    }

    public function update(UpdateTermRequest $request, string $taxonomy, string $id)
    {
        // Get taxonomy model.
        $taxonomyModel = $this->contentService->getTaxonomies()->where('name', $taxonomy)->first();

        if (!$taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        // Get term.
        $term = Term::where('taxonomy', $taxonomy)->findOrFail($id);

        // Update term.
        $term->name = $request->name;
        $term->slug = $request->slug ?: Str::slug($request->name);
        $term->description = $request->description;
        $term->parent_id = $request->parent_id;

        // Handle featured image upload.
        if ($request->hasFile('featured_image') && $taxonomyModel->show_featured_image) {
            // Delete old image if exists.
            if ($term->featured_image) {
                Storage::disk('public')->delete($term->featured_image);
            }

            $imagePath = $request->file('featured_image')->store('terms', 'public');
            $term->featured_image = $imagePath;
        }

        // Handle image removal.
        if ($request->has('remove_featured_image') && $request->remove_featured_image && $term->featured_image) {
            Storage::disk('public')->delete($term->featured_image);
            $term->featured_image = null;
        }

        $term->save();

        // Get taxonomy label for message.
        $taxLabel = $taxonomyModel->label_singular ?? Str::title($taxonomy);

        return redirect()->route('admin.terms.index', $taxonomy)
            ->with('success', __(':taxLabel updated successfully', ['taxLabel' => $taxLabel]));
    }

    public function destroy(string $taxonomy, string $id)
    {
        $this->checkAuthorization(auth()->user(), ['term.delete']);

        // Get taxonomy model.
        $taxonomyModel = $this->contentService->getTaxonomies()->where('name', $taxonomy)->first();

        if (!$taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        $term = Term::where('taxonomy', $taxonomy)->findOrFail($id);

        // Get taxonomy label for messages.
        $taxLabel = $taxonomyModel->label_singular ?? Str::title($taxonomy);

        // Check if term has posts.
        if ($term->posts()->count() > 0) {
            return redirect()->route('admin.terms.index', $taxonomy)
                ->with('error', __('Cannot delete :taxLabel as it is associated with posts', ['taxLabel' => $taxLabel]));
        }

        // Check if term has children.
        if ($term->children()->count() > 0) {
            return redirect()->route('admin.terms.index', $taxonomy)
                ->with('error', __('Cannot delete :taxLabel as it has child items', ['taxLabel' => $taxLabel]));
        }

        // Delete featured image if exists.
        if ($term->featured_image) {
            Storage::disk('public')->delete($term->featured_image);
        }

        $term->delete();

        return redirect()->route('admin.terms.index', $taxonomy)
            ->with('success', __(':taxLabel deleted successfully', ['taxLabel' => $taxLabel]));
    }

    public function edit(string $taxonomy, string $term)
    {
        $this->checkAuthorization(auth()->user(), ['term.edit']);

        // Get taxonomy.
        $taxonomyModel = $this->contentService->getTaxonomies()->where('name', $taxonomy)->first();

        if (!$taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        // Get term.
        $term = Term::where('taxonomy', $taxonomy)->findOrFail($term);

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
                        'url' => route('admin.terms.index', $taxonomy)
                    ],
                ]
            ]);
    }
}
