<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use App\Models\Term;
use App\Services\Content\ContentService;
use App\Services\ImageService;
use App\Services\MediaLibraryService;
use App\Services\PostMetaService;
use App\Services\PostService;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostsController extends Controller
{
    public function __construct(
        private readonly ContentService $contentService,
        private readonly PostMetaService $postMetaService,
        private readonly PostService $postService,
        private readonly ImageService $imageService,
        private readonly MediaLibraryService $mediaService
    ) {
    }

    public function index(Request $request, string $postType = 'post'): RedirectResponse|Renderable
    {
        $this->authorize('viewAny', Post::class);

        // Get post type.
        $postTypeModel = $this->contentService->getPostType($postType);

        if (! $postTypeModel) {
            return redirect()->route('admin.posts.index')->with('error', 'Post type not found');
        }

        // Prepare filters
        $filters = [
            'post_type' => $postType,
            'search' => $request->search,
            'status' => $request->status,
            'category' => $request->category,
            'tag' => $request->tag,
        ];

        // Get posts with pagination using service.
        $posts = $this->postService->getPosts($filters);

        // Get categories and tags for filters.
        $categories = Term::where('taxonomy', 'category')->select('id', 'name')->get();
        $tags = Term::where('taxonomy', 'tag')->select('id', 'name')->get();

        return view('backend.pages.posts.index', compact('posts', 'postType', 'postTypeModel', 'categories', 'tags'))
            ->with([
                'breadcrumbs' => [
                    'title' => $postTypeModel->label,
                ],
            ]);
    }

    public function create(string $postType = 'post'): RedirectResponse|Renderable
    {
        $this->authorize('create', Post::class);

        // Get post type.
        $postTypeModel = $this->contentService->getPostType($postType);

        if (! $postTypeModel) {
            return redirect()->route('admin.posts.index')->with('error', 'Post type not found');
        }

        // Get taxonomies.
        $taxonomies = [];
        if (! empty($postTypeModel->taxonomies)) {
            $taxonomies = $this->contentService->getTaxonomies()
                ->whereIn('name', $postTypeModel->taxonomies)
                ->all();
        }

        // Get parent posts for hierarchical post types.
        $parentPosts = [];
        if ($postTypeModel->hierarchical) {
            $parentPosts = Post::where('post_type', $postType)
                ->pluck('title', 'id')
                ->toArray();
        }

        return view('backend.pages.posts.create', compact('postType', 'postTypeModel', 'taxonomies', 'parentPosts'))
            ->with([
                'breadcrumbs' => [
                    'title' => __('New :postType', ['postType' => $postTypeModel->label_singular]),
                    'items' => [
                        [
                            'label' => $postTypeModel->label,
                            'url' => route('admin.posts.index', $postType),
                        ],
                    ],
                ],
            ]);
    }

    public function store(StorePostRequest $request, string $postType = 'post'): RedirectResponse
    {
        $this->authorize('create', Post::class);

        // Get post type.
        $postTypeModel = $this->contentService->getPostType($postType);

        if (! $postTypeModel) {
            return redirect()->route('admin.posts.index')->with('error', 'Post type not found');
        }

        // Create post
        $post = new Post();
        $post->title = $request->title;
        $post->slug = $request->slug ?: Str::slug($request->title);
        $post->content = $request->content;
        $post->excerpt = $request->excerpt ?: Str::limit(strip_tags($request->content), 200);
        $post->status = $request->status;
        $post->post_type = $postType;
        $post->user_id = Auth::id();
        $post->parent_id = $request->parent_id;

        // Handle publish date
        if ($request->has('schedule_post') && $request->schedule_post && ! empty($request->published_at)) {
            $post->status = 'future';
            $post->published_at = Carbon::parse($request->published_at);
        } elseif ($request->status === 'future' && ! empty($request->published_at)) {
            $post->published_at = Carbon::parse($request->published_at);
        } elseif ($request->status === 'publish') {
            $post->published_at = now();
        }

        $post = ld_apply_filters('before_post_save', $post, $request);

        $post->save();

        // Handle featured image removal first.
        if ($request->has('remove_featured_image') && $request->remove_featured_image) {
            $post->clearMediaCollection('featured');
        } elseif ($request->filled('featured_image')) {
            if ($request->hasFile('featured_image')) {
                $post->clearMediaCollection('featured');
                $post->addMediaFromRequest('featured_image')->toMediaCollection('featured');
            } else {
                $this->mediaService->associateExistingMedia($post, $request->input('featured_image'), 'featured');
            }
        }

        $post = ld_apply_filters('after_post_save', $post, $request);

        // Handle post meta.
        $this->handlePostMeta($request, $post);

        // Handle taxonomies
        $this->handleTaxonomies($request, $post);

        return redirect()->route('admin.posts.edit', [$postType, $post->id])
            ->with('success', 'Post created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $postType, string $id): Renderable
    {
        $post = Post::where('post_type', $postType)->findOrFail($id);
        $this->authorize('view', $post);
        $postTypeModel = $this->contentService->getPostType($postType);

        return view('backend.pages.posts.show', compact('post', 'postType', 'postTypeModel'))
            ->with([
                'breadcrumbs' => [
                    'title' => __('View :postName', ['postName' => $post->title]),
                    'items' => [
                        [
                            'label' => $postTypeModel->label,
                            'url' => route('admin.posts.index', $postType),
                        ],
                    ],
                ],
            ]);
    }

    public function edit(string $postType, string $id): RedirectResponse|Renderable
    {
        // Get post with postMeta relationship.
        $post = Post::with(['postMeta', 'terms'])
            ->where('post_type', $postType)
            ->findOrFail($id);

        $this->authorize('update', $post);

        // Get post type
        $postTypeModel = $this->contentService->getPostType($postType);

        if (! $postTypeModel) {
            return redirect()->route('admin.posts.index')->with('error', 'Post type not found');
        }

        // Get taxonomies
        $taxonomies = [];
        if (! empty($postTypeModel->taxonomies)) {
            $taxonomies = $this->contentService->getTaxonomies()
                ->whereIn('name', $postTypeModel->taxonomies)
                ->all();
        }

        // Get parent posts for hierarchical post types
        $parentPosts = [];
        if ($postTypeModel->hierarchical) {
            $parentPosts = Post::where('post_type', $postType)
                ->where('id', '!=', $id)
                ->pluck('title', 'id')
                ->toArray();
        }

        // Get selected terms
        $selectedTerms = [];
        foreach ($post->terms as $term) {
            $taxonomyName = $term->getAttribute('taxonomy');
            if ($taxonomyName && ! isset($selectedTerms[$taxonomyName])) {
                $selectedTerms[$taxonomyName] = [];
            }
            if ($taxonomyName) {
                $selectedTerms[$taxonomyName][] = $term->id;
            }
        }

        return view('backend.pages.posts.edit', compact('post', 'postType', 'postTypeModel', 'taxonomies', 'parentPosts', 'selectedTerms'))
            ->with([
                'breadcrumbs' => [
                    'title' => __('Edit :postType', ['postType' => $postTypeModel->label_singular]),
                    'items' => [
                        [
                            'label' => $postTypeModel->label,
                            'url' => route('admin.posts.index', $postType),
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, string $postType, string $id)
    {
        // Get post.
        $post = Post::where('post_type', $postType)->findOrFail($id);
        $this->authorize('update', $post);

        // Update post.
        $post->title = $request->title;
        $post->slug = $request->slug ?: Str::slug($request->title);
        $post->content = $request->content;
        $post->excerpt = $request->excerpt ?: Str::limit(strip_tags($request->content), 200);
        $post->status = $request->status;
        $post->parent_id = $request->parent_id;

        // Handle publish date.
        if ($request->has('schedule_post') && $request->schedule_post && ! empty($request->published_at)) {
            $post->status = 'future';
            $post->published_at = \Carbon\Carbon::parse($request->published_at);
        } elseif ($request->status === 'future' && ! empty($request->published_at)) {
            $post->published_at = \Carbon\Carbon::parse($request->published_at);
        } elseif ($request->status === 'publish' && ! $post->published_at) {
            $post->published_at = now();
        }

        $post = ld_apply_filters('before_post_update', $post, $request);

        $post->save();

        // Handle featured image removal first.
        if ($request->has('remove_featured_image') && $request->remove_featured_image) {
            $post->clearMediaCollection('featured');
        } elseif ($request->filled('featured_image')) {
            $post->clearMediaCollection('featured');

            if ($request->hasFile('featured_image')) {
                $post->addMediaFromRequest('featured_image')->toMediaCollection('featured');
            } else {
                $this->mediaService->associateExistingMedia($post, $request->input('featured_image'), 'featured');
            }
        }

        $post = ld_apply_filters('after_post_update', $post, $request);

        // Handle post meta.
        $this->handlePostMeta($request, $post);

        // Handle taxonomies.
        $this->handleTaxonomies($request, $post);

        return redirect()->route('admin.posts.edit', [$postType, $post->id])
            ->with('success', 'Post updated successfully');
    }

    /**
     * Delete a post
     */
    public function destroy(string $postType, string $id): RedirectResponse
    {
        $post = Post::where('post_type', $postType)->findOrFail($id);
        $this->authorize('delete', $post);

        ld_do_action('post_before_deleted', $post);
        $post->delete();
        ld_do_action('post_deleted', $post);

        return redirect()->route('admin.posts.index', $postType)
            ->with('success', __('Post deleted successfully'));
    }

    /**
     * Delete multiple posts at once
     */
    public function bulkDelete(Request $request, string $postType): RedirectResponse
    {
        $this->authorize('bulkDelete', Post::class);

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->route('admin.posts.index', $postType)
                ->with('error', __('No posts selected for deletion'));
        }

        $posts = Post::where('post_type', $postType)->whereIn('id', $ids)->get();

        foreach ($posts as $post) {
            ld_do_action('post_before_deleted', $post);

            $post->delete();

            ld_do_action('post_deleted', $post);
        }

        return redirect()->route('admin.posts.index', $postType)
            ->with('success', __(':count posts deleted successfully', ['count' => count($posts)]));
    }

    /**
     * Handle taxonomies for a post
     */
    protected function handleTaxonomies(Request $request, Post $post)
    {
        // Get current post type.
        $postTypeModel = $this->contentService->getPostType($post->post_type);

        if (! $postTypeModel || empty($postTypeModel->taxonomies)) {
            return;
        }

        // Initialize empty arrays for each taxonomy.
        $termIds = [];
        foreach ($postTypeModel->taxonomies as $taxonomy) {
            $termKey = 'taxonomy_'.$taxonomy;
            if ($request->has($termKey)) {
                $taxonomyTerms = $request->input($termKey);
                if (is_array($taxonomyTerms)) {
                    $termIds = array_merge($termIds, $taxonomyTerms);
                }
            }
        }

        // Sync terms.
        $post->terms()->sync($termIds);

        ld_do_action('post_taxonomies_updated', $post, $termIds);
    }

    protected function handlePostMeta(Request $request, Post $post)
    {
        $metaKeys = $request->input('meta_keys', []);
        $metaValues = $request->input('meta_values', []);
        $metaTypes = $request->input('meta_types', []);
        $metaDefaultValues = $request->input('meta_default_values', []);

        // Clear existing meta for this post.
        $post->postMeta()->delete();

        // Add new meta.
        foreach ($metaKeys as $index => $key) {
            if (! empty($key) && isset($metaValues[$index])) {
                $this->postMetaService->setMeta(
                    $post->id,
                    $key,
                    $metaValues[$index],
                    $metaTypes[$index] ?? 'input',
                    $metaDefaultValues[$index] ?? null
                );
            }
        }

        ld_do_action('post_meta_updated', $post, $metaKeys, $metaValues, $metaTypes, $metaDefaultValues);
    }
}
