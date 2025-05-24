<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Term;
use App\Services\ContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostsController extends Controller
{
    public function __construct(private readonly ContentService $contentService)
    {
        // Remove middleware and use checkAuthorization instead in each method
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $postType = 'post')
    {
        $this->checkAuthorization(auth()->user(), ['post.view']);

        // Get post type
        $postTypeModel = $this->contentService->getPostType($postType);

        if (!$postTypeModel) {
            return redirect()->route('admin.posts.index')->with('error', 'Post type not found');
        }

        // Query posts
        $query = Post::where('post_type', $postType)
            ->with(['user', 'terms']);

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Handle status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Handle category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->whereHas('terms', function ($q) use ($request) {
                $q->where('id', $request->category)
                    ->where('taxonomy', 'category');
            });
        }

        // Get posts with pagination
        $posts = $query->orderBy('created_at', 'desc')
            ->paginate(config('settings.default_pagination', 10));

        // Get categories for filter
        $categories = Term::where('taxonomy', 'category')->get();

        return view('backend.pages.posts.index', compact('posts', 'postType', 'postTypeModel', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $postType = 'post')
    {
        $this->checkAuthorization(auth()->user(), ['post.create']);

        // Get post type
        $postTypeModel = $this->contentService->getPostType($postType);

        if (!$postTypeModel) {
            return redirect()->route('admin.posts.index')->with('error', 'Post type not found');
        }

        // Get taxonomies
        $taxonomies = [];
        if (!empty($postTypeModel->taxonomies)) {
            $taxonomies = $this->contentService->getTaxonomies()
                ->whereIn('name', $postTypeModel->taxonomies)
                ->all();
        }

        // Get parent posts for hierarchical post types
        $parentPosts = [];
        if ($postTypeModel->hierarchical) {
            $parentPosts = Post::where('post_type', $postType)
                ->where('status', 'publish')
                ->pluck('title', 'id')
                ->toArray();
        }

        return view('backend.pages.posts.create', compact('postType', 'postTypeModel', 'taxonomies', 'parentPosts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $postType = 'post')
    {
        $this->checkAuthorization(auth()->user(), ['post.create']);

        // Get post type
        $postTypeModel = $this->contentService->getPostType($postType);

        if (!$postTypeModel) {
            return redirect()->route('admin.posts.index')->with('error', 'Post type not found');
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'status' => 'required|in:draft,publish,pending,future,private',
            'featured_image' => 'nullable|file|image|max:5120',
            'parent_id' => 'nullable|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create post
        $post = new Post();
        $post->title = $request->title;
        $post->slug = $request->slug ?: Str::slug($request->title);
        $post->content = $request->content;
        $post->excerpt = $request->excerpt;
        $post->status = $request->status;
        $post->post_type = $postType;
        $post->user_id = auth()->id();
        $post->parent_id = $request->parent_id;

        // Handle featured image
        if ($request->hasFile('featured_image')) {
            $post->featured_image = storeImageAndGetUrl($request, 'featured_image', 'uploads/posts');
        }

        // Handle publish date
        if ($request->status === 'future' && !empty($request->published_at)) {
            $post->published_at = $request->published_at;
        } elseif ($request->status === 'publish') {
            $post->published_at = now();
        }

        $post->save();

        // Handle taxonomies
        $this->handleTaxonomies($request, $post);

        return redirect()->route('admin.posts.edit', [$postType, $post->id])
            ->with('success', 'Post created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $postType, string $id)
    {
        $this->checkAuthorization(auth()->user(), ['post.view']);

        $post = Post::where('post_type', $postType)->findOrFail($id);
        $postTypeModel = $this->contentService->getPostType($postType);

        return view('backend.pages.posts.show', compact('post', 'postType', 'postTypeModel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $postType, string $id)
    {
        $this->checkAuthorization(auth()->user(), ['post.edit']);

        // Get post
        $post = Post::where('post_type', $postType)->findOrFail($id);

        // Get post type
        $postTypeModel = $this->contentService->getPostType($postType);

        if (!$postTypeModel) {
            return redirect()->route('admin.posts.index')->with('error', 'Post type not found');
        }

        // Get taxonomies
        $taxonomies = [];
        if (!empty($postTypeModel->taxonomies)) {
            $taxonomies = $this->contentService->getTaxonomies()
                ->whereIn('name', $postTypeModel->taxonomies)
                ->all();
        }

        // Get parent posts for hierarchical post types
        $parentPosts = [];
        if ($postTypeModel->hierarchical) {
            $parentPosts = Post::where('post_type', $postType)
                ->where('status', 'publish')
                ->where('id', '!=', $id)
                ->pluck('title', 'id')
                ->toArray();
        }

        // Get selected terms
        $selectedTerms = [];
        foreach ($post->terms as $term) {
            if (!isset($selectedTerms[$term->taxonomy])) {
                $selectedTerms[$term->taxonomy] = [];
            }
            $selectedTerms[$term->taxonomy][] = $term->id;
        }

        return view('backend.pages.posts.edit', compact('post', 'postType', 'postTypeModel', 'taxonomies', 'parentPosts', 'selectedTerms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $postType, string $id)
    {
        $this->checkAuthorization(auth()->user(), ['post.edit']);

        // Get post
        $post = Post::where('post_type', $postType)->findOrFail($id);

        // Validate request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,' . $id,
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'status' => 'required|in:draft,publish,pending,future,private',
            'featured_image' => 'nullable|file|image|max:5120',
            'parent_id' => 'nullable|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update post
        $post->title = $request->title;
        $post->slug = $request->slug ?: Str::slug($request->title);
        $post->content = $request->content;
        $post->excerpt = $request->excerpt;
        $post->status = $request->status;
        $post->parent_id = $request->parent_id;

        // Handle featured image
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if (!empty($post->featured_image)) {
                deleteImageFromPublic($post->featured_image);
            }
            $post->featured_image = storeImageAndGetUrl($request, 'featured_image', 'uploads/posts');
        } elseif ($request->has('remove_featured_image') && $request->remove_featured_image) {
            // Delete image if remove is checked
            if (!empty($post->featured_image)) {
                deleteImageFromPublic($post->featured_image);
                $post->featured_image = null;
            }
        }

        // Handle publish date
        if ($request->status === 'future' && !empty($request->published_at)) {
            $post->published_at = $request->published_at;
        } elseif ($request->status === 'publish' && !$post->published_at) {
            $post->published_at = now();
        }

        $post->save();

        // Handle taxonomies
        $this->handleTaxonomies($request, $post);

        return redirect()->route('admin.posts.edit', [$postType, $post->id])
            ->with('success', 'Post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $postType, string $id)
    {
        $this->checkAuthorization(auth()->user(), ['post.delete']);

        $post = Post::where('post_type', $postType)->findOrFail($id);

        // Delete featured image if exists
        if (!empty($post->featured_image)) {
            deleteImageFromPublic($post->featured_image);
        }

        $post->delete();

        return redirect()->route('admin.posts.index', $postType)
            ->with('success', 'Post deleted successfully');
    }

    /**
     * Handle taxonomies for a post
     */
    protected function handleTaxonomies(Request $request, Post $post)
    {
        // Get current post type
        $postTypeModel = $this->contentService->getPostType($post->post_type);

        if (!$postTypeModel || empty($postTypeModel->taxonomies)) {
            return;
        }

        // Initialize empty arrays for each taxonomy
        $termIds = [];
        foreach ($postTypeModel->taxonomies as $taxonomy) {
            $termKey = 'taxonomy_' . $taxonomy;
            if ($request->has($termKey)) {
                $taxonomyTerms = $request->input($termKey);
                if (is_array($taxonomyTerms)) {
                    $termIds = array_merge($termIds, $taxonomyTerms);
                }
            }
        }

        // Sync terms
        $post->terms()->sync($termIds);
    }
}
