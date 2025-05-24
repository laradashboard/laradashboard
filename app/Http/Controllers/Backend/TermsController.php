<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Services\ContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TermsController extends Controller
{
    public function __construct(private readonly ContentService $contentService)
    {
        // Remove middleware and use checkAuthorization instead in each method
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $taxonomy)
    {
        $this->checkAuthorization(auth()->user(), ['term.view']);

        // Get taxonomy
        $taxonomyModel = $this->contentService->getTaxonomies()->where('name', $taxonomy)->first();
        
        if (!$taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        // Query terms
        $query = Term::where('taxonomy', $taxonomy);

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Get terms with pagination
        $terms = $query->orderBy('name', 'asc')
            ->paginate(config('settings.default_pagination', 20));

        // Get parent terms for hierarchical taxonomies
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

        return view('backend.pages.terms.index', compact('terms', 'taxonomy', 'taxonomyModel', 'parentTerms', 'term'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $taxonomy)
    {
        $this->checkAuthorization(auth()->user(), ['term.create']);

        // Get taxonomy
        $taxonomyModel = $this->contentService->getTaxonomies()->where('name', $taxonomy)->first();
        
        if (!$taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:terms,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:terms,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create term
        $term = new Term();
        $term->name = $request->name;
        $term->slug = $request->slug ?: Str::slug($request->name);
        $term->taxonomy = $taxonomy;
        $term->description = $request->description;
        $term->parent_id = $request->parent_id;
        $term->save();

        // Get taxonomy label for message
        $taxLabel = $taxonomyModel->label_singular ?? Str::title($taxonomy);

        return redirect()->route('admin.terms.index', $taxonomy)
            ->with('success', __(':taxLabel created successfully', ['taxLabel' => $taxLabel]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $taxonomy, string $id)
    {
        $this->checkAuthorization(auth()->user(), ['term.edit']);

        // Get taxonomy model
        $taxonomyModel = $this->contentService->getTaxonomies()->where('name', $taxonomy)->first();
        
        if (!$taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        // Get term
        $term = Term::where('taxonomy', $taxonomy)->findOrFail($id);
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:terms,slug,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:terms,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('edit', $id);
        }

        // Update term
        $term->name = $request->name;
        $term->slug = $request->slug ?: Str::slug($request->name);
        $term->description = $request->description;
        $term->parent_id = $request->parent_id;
        $term->save();

        // Get taxonomy label for message
        $taxLabel = $taxonomyModel->label_singular ?? Str::title($taxonomy);

        return redirect()->route('admin.terms.index', $taxonomy)
            ->with('success', __(':taxLabel updated successfully', ['taxLabel' => $taxLabel]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $taxonomy, string $id)
    {
        $this->checkAuthorization(auth()->user(), ['term.delete']);

        // Get taxonomy model
        $taxonomyModel = $this->contentService->getTaxonomies()->where('name', $taxonomy)->first();
        
        if (!$taxonomyModel) {
            return redirect()->route('admin.posts.index')->with('error', __('Taxonomy not found'));
        }

        $term = Term::where('taxonomy', $taxonomy)->findOrFail($id);
        
        // Get taxonomy label for messages
        $taxLabel = $taxonomyModel->label_singular ?? Str::title($taxonomy);
        
        // Check if term has posts
        if ($term->posts()->count() > 0) {
            return redirect()->route('admin.terms.index', $taxonomy)
                ->with('error', __('Cannot delete :taxLabel as it is associated with posts', ['taxLabel' => $taxLabel]));
        }
        
        // Check if term has children
        if ($term->children()->count() > 0) {
            return redirect()->route('admin.terms.index', $taxonomy)
                ->with('error', __('Cannot delete :taxLabel as it has child items', ['taxLabel' => $taxLabel]));
        }
        
        $term->delete();
        
        return redirect()->route('admin.terms.index', $taxonomy)
            ->with('success', __(':taxLabel deleted successfully', ['taxLabel' => $taxLabel]));
    }
}
