<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\Hooks\PostActionHook;
use App\Enums\Hooks\PostFilterHook;
use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Requests\Common\BulkDeleteRequest;
use App\Models\Post;
use App\Models\Term;
use App\Services\Content\ContentService;
use App\Services\MediaLibraryService;
use App\Services\PostMetaService;
use App\Services\PostService;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(
        private readonly ContentService $contentService,
        private readonly PostMetaService $postMetaService,
        private readonly PostService $postService,
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

        $this->setBreadcrumbTitle($postTypeModel->label)
            ->setBreadcrumbIcon($postTypeModel->icon ?? 'lucide:file-text')
            ->setBreadcrumbActionButton(
                route('admin.posts.create', $postType),
                __('New :postType', ['postType' => $postTypeModel->label_singular]),
                'feather:plus',
                'post.create'
            );

        // Get categories and tags for filters.
        $categories = Term::where('taxonomy', 'category')->select('id', 'name')->get();
        $tags = Term::where('taxonomy', 'tag')->select('id', 'name')->get();

        return $this->renderViewWithBreadcrumbs('backend.pages.posts.index', compact('postType', 'postTypeModel', 'categories', 'tags'));
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

        $this->setBreadcrumbTitle(__('New :postType', ['postType' => $postTypeModel->label_singular]))
            ->setBreadcrumbIcon($postTypeModel->icon ?? 'lucide:file-plus')
            ->addBreadcrumbItem($postTypeModel->label, route('admin.posts.index', $postType));

        return $this->renderViewWithBreadcrumbs('backend.pages.posts.create', compact('postType', 'postTypeModel', 'taxonomies', 'parentPosts'));
    }

    public function store(StorePostRequest $request, string $postType = 'post'): RedirectResponse
    {
        $this->authorize('create', Post::class);

        // Get post type.
        $postTypeModel = $this->contentService->getPostType($postType);

        if (! $postTypeModel) {
            return redirect()->route('admin.posts.index')->with('error', 'Post type not found');
        }

        $data = $this->addHooks(
            $request->validated(),
            PostActionHook::POST_CREATED_BEFORE,
            PostFilterHook::POST_CREATED_BEFORE
        );

        // Create post
        $post = new Post();
        $post->title = $data['title'];
        $post->slug = $data['slug'] ?? Str::slug($data['title']);
        $post->content = $data['content'];
        $post->excerpt = $data['excerpt'] ?? Str::limit(strip_tags($data['content']), 200);
        $post->status = $data['status'];
        $post->post_type = $postType;
        $post->user_id = Auth::id();
        $post->parent_id = $data['parent_id'] ?? null;

        // Handle publish date
        if (isset($data['schedule_post']) && $data['schedule_post'] && ! empty($data['published_at'])) {
            $post->status = PostStatus::SCHEDULED->value;
            $post->published_at = Carbon::parse($data['published_at']);
        } elseif ($data['status'] === PostStatus::SCHEDULED->value && ! empty($data['published_at'])) {
            $post->published_at = Carbon::parse($data['published_at']);
        } elseif ($data['status'] === PostStatus::PUBLISHED->value) {
            $post->published_at = now();
        }

        $post->save();

        // Handle featured image removal first.
        if (isset($data['remove_featured_image']) && $data['remove_featured_image']) {
            $post->clearMediaCollection('featured');
        } elseif (! empty($data['featured_image'])) {
            if ($request->hasFile('featured_image')) {
                $post->clearMediaCollection('featured');
                $post->addMediaFromRequest('featured_image')->toMediaCollection('featured');
            } else {
                $this->mediaService->associateExistingMedia($post, $data['featured_image'], 'featured');
            }
        }

        $post = $this->addHooks(
            $post,
            PostActionHook::POST_CREATED_AFTER,
            PostFilterHook::POST_CREATED_AFTER
        );

        // Handle post meta.
        $this->handlePostMeta($request, $post);

        // Handle taxonomies
        $this->handleTaxonomies($request, $post);

        session()->flash('success', __('Post has been created.'));

        return redirect()->route('admin.posts.edit', [$postType, $post->id]);
    }

    public function show(string $postType, string $id): Renderable
    {
        $post = Post::where('post_type', $postType)->findOrFail($id);
        $this->authorize('view', $post);
        $postTypeModel = $this->contentService->getPostType($postType);

        $this->setBreadcrumbTitle(__('View :postName', ['postName' => $post->title]))
            ->setBreadcrumbIcon($postTypeModel->icon ?? 'lucide:file-text')
            ->addBreadcrumbItem($postTypeModel->label, route('admin.posts.index', $postType))
            ->setBreadcrumbActionButton(
                route('admin.posts.edit', [$postType, $post->id]),
                __('Edit :postType', ['postType' => $postTypeModel->label_singular]),
                'feather:edit-2',
                'post.edit'
            );

        return $this->renderViewWithBreadcrumbs('backend.pages.posts.show', compact('post', 'postType', 'postTypeModel'));
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

        $this->setBreadcrumbTitle(__('Edit :postType', ['postType' => $postTypeModel->label_singular]))
            ->setBreadcrumbIcon($postTypeModel->icon ?? 'lucide:file-pen')
            ->addBreadcrumbItem($postTypeModel->label, route('admin.posts.index', $postType));

        return $this->renderViewWithBreadcrumbs('backend.pages.posts.edit', compact('post', 'postType', 'postTypeModel', 'taxonomies', 'parentPosts', 'selectedTerms'));
    }

    public function update(UpdatePostRequest $request, string $postType, string $id): RedirectResponse
    {
        // Get post.
        $post = Post::where('post_type', $postType)->findOrFail($id);
        $this->authorize('update', $post);

        $data = $this->addHooks(
            $request->validated(),
            PostActionHook::POST_UPDATED_BEFORE,
            PostFilterHook::POST_UPDATED_BEFORE
        );

        // Update post.
        $post->title = $data['title'];
        $post->slug = $data['slug'] ?? Str::slug($data['title']);
        $post->content = $data['content'];
        $post->excerpt = $data['excerpt'] ?? Str::limit(strip_tags($data['content']), 200);
        $post->status = $data['status'];
        $post->parent_id = $data['parent_id'] ?? null;

        // Handle publish date.
        if (isset($data['schedule_post']) && $data['schedule_post'] && ! empty($data['published_at'])) {
            $post->status = PostStatus::SCHEDULED->value;
            $post->published_at = Carbon::parse($data['published_at']);
        } elseif ($data['status'] === PostStatus::SCHEDULED->value && ! empty($data['published_at'])) {
            $post->published_at = Carbon::parse($data['published_at']);
        } elseif ($data['status'] === PostStatus::PUBLISHED->value && ! $post->published_at) {
            $post->published_at = now();
        }

        $post->save();

        // Handle featured image removal first.
        if (isset($data['remove_featured_image']) && $data['remove_featured_image']) {
            $post->clearMediaCollection('featured');
        } elseif (! empty($data['featured_image'])) {
            $post->clearMediaCollection('featured');

            if ($request->hasFile('featured_image')) {
                $post->addMediaFromRequest('featured_image')->toMediaCollection('featured');
            } else {
                $this->mediaService->associateExistingMedia($post, $data['featured_image'], 'featured');
            }
        }

        $post = $this->addHooks(
            $post,
            PostActionHook::POST_UPDATED_AFTER,
            PostFilterHook::POST_UPDATED_AFTER
        );

        // Handle post meta.
        $this->handlePostMeta($request, $post);

        // Handle taxonomies.
        $this->handleTaxonomies($request, $post);

        session()->flash('success', __('Post has been updated.'));

        return back();
    }

    /**
     * Delete a post
     */
    public function destroy(string $postType, string $id): RedirectResponse
    {
        $post = Post::where('post_type', $postType)->findOrFail($id);
        $this->authorize('delete', $post);

        $post = $this->addHooks(
            $post,
            PostActionHook::POST_DELETED_BEFORE,
            PostFilterHook::POST_DELETED_BEFORE
        );

        $post->delete();

        $this->addHooks(
            $post,
            PostActionHook::POST_DELETED_AFTER,
            PostFilterHook::POST_DELETED_AFTER
        );

        session()->flash('success', __('Post has been deleted.'));

        return redirect()->route('admin.posts.index', $postType);
    }

    /**
     * Delete multiple posts at once
     */
    public function bulkDelete(BulkDeleteRequest $request, string $postType): RedirectResponse
    {
        $this->authorize('bulkDelete', Post::class);

        $ids = $request->validated('ids');

        if (empty($ids)) {
            session()->flash('error', __('No posts selected for deletion.'));
            return redirect()->route('admin.posts.index', $postType);
        }

        $ids = $this->addHooks(
            $ids,
            PostActionHook::POST_BULK_DELETED_BEFORE
        );

        $deletedCount = $this->postService->bulkDeletePosts($ids, $postType);

        $this->addHooks(
            ['deleted_count' => $deletedCount, 'post_type' => $postType],
            PostActionHook::POST_BULK_DELETED_AFTER
        );

        if ($deletedCount > 0) {
            session()->flash('success', __(':count posts deleted successfully', ['count' => $deletedCount]));
        } else {
            session()->flash('error', __('No posts were deleted.'));
        }

        return redirect()->route('admin.posts.index', $postType);
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
            $termKey = 'taxonomy_' . $taxonomy;
            if ($request->has($termKey)) {
                $taxonomyTerms = $request->input($termKey);
                if (is_array($taxonomyTerms)) {
                    $termIds = array_merge($termIds, $taxonomyTerms);
                }
            }
        }

        // Sync terms.
        $post->terms()->sync($termIds);

        $this->addHooks(
            ['post' => $post, 'term_ids' => $termIds],
            PostActionHook::POST_TAXONOMIES_UPDATED
        );
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

        $this->addHooks(
            [
                'post' => $post,
                'meta_keys' => $metaKeys,
                'meta_values' => $metaValues,
                'meta_types' => $metaTypes,
                'meta_default_values' => $metaDefaultValues,
            ],
            PostActionHook::POST_META_UPDATED
        );
    }

    /**
     * Show the LaraBuilder for creating a new post.
     */
    public function builderCreate(string $postType = 'post'): RedirectResponse|View
    {
        $this->authorize('create', Post::class);

        $postTypeModel = $this->contentService->getPostType($postType);

        if (! $postTypeModel) {
            return redirect()->route('admin.posts.index')->with('error', 'Post type not found');
        }

        // Get taxonomies with their terms.
        $taxonomies = $this->getTaxonomiesWithTerms($postTypeModel);

        // Get parent posts for hierarchical post types.
        $parentPosts = [];
        if ($postTypeModel->hierarchical) {
            $parentPosts = Post::where('post_type', $postType)
                ->pluck('title', 'id')
                ->toArray();
        }

        return view('backend.pages.posts.builder', [
            'postType' => $postType,
            'postTypeModel' => $postTypeModel,
            'taxonomies' => $taxonomies,
            'selectedTerms' => [],
            'parentPosts' => $parentPosts,
            'initialData' => null,
            'postData' => null,
            'saveUrl' => route('admin.posts.store', $postType),
        ]);
    }

    /**
     * Show the LaraBuilder for editing an existing post.
     */
    public function builderEdit(string $postType, string $id): RedirectResponse|View
    {
        $post = Post::with(['postMeta', 'terms', 'media'])
            ->where('post_type', $postType)
            ->findOrFail($id);

        $this->authorize('update', $post);

        $postTypeModel = $this->contentService->getPostType($postType);

        if (! $postTypeModel) {
            return redirect()->route('admin.posts.index')->with('error', 'Post type not found');
        }

        // Get taxonomies with their terms.
        $taxonomies = $this->getTaxonomiesWithTerms($postTypeModel);

        // Get parent posts for hierarchical post types.
        $parentPosts = [];
        if ($postTypeModel->hierarchical) {
            $parentPosts = Post::where('post_type', $postType)
                ->where('id', '!=', $id)
                ->pluck('title', 'id')
                ->toArray();
        }

        // Get selected terms.
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

        // Get initial data from design_json.
        $initialData = $post->design_json;

        // Get post data for the builder.
        $postData = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'status' => $post->status,
            'excerpt' => $post->excerpt,
            'parent_id' => $post->parent_id,
            'published_at' => $post->published_at?->format('Y-m-d\TH:i'),
            'featured_image_url' => $post->getFeaturedImageUrl(),
        ];

        return view('backend.pages.posts.builder', [
            'post' => $post,
            'postType' => $postType,
            'postTypeModel' => $postTypeModel,
            'taxonomies' => $taxonomies,
            'selectedTerms' => $selectedTerms,
            'parentPosts' => $parentPosts,
            'initialData' => $initialData,
            'postData' => $postData,
            'saveUrl' => route('admin.posts.update', [$postType, $post->id]),
        ]);
    }

    /**
     * Store a new post from the LaraBuilder.
     */
    public function builderStore(Request $request, string $postType = 'post'): JsonResponse
    {
        $this->authorize('create', Post::class);

        $postTypeModel = $this->contentService->getPostType($postType);

        if (! $postTypeModel) {
            return response()->json(['success' => false, 'message' => 'Post type not found'], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:200',
            'status' => 'required|string|in:draft,published,pending,scheduled,private',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'design_json' => 'nullable|array',
            'published_at' => 'nullable|date',
            'parent_id' => 'nullable|integer|exists:posts,id',
        ]);

        $data = $request->all();

        $data = $this->addHooks(
            $data,
            PostActionHook::POST_CREATED_BEFORE,
            PostFilterHook::POST_CREATED_BEFORE
        );

        // Generate unique slug
        $baseSlug = $data['slug'] ?? Str::slug($data['title']);
        $slug = $this->generateUniqueSlug($baseSlug);

        // Create post.
        $post = new Post();
        $post->title = $data['title'];
        $post->slug = $slug;
        $post->content = $data['content'] ?? '';
        $post->design_json = $data['design_json'] ?? null;
        $post->excerpt = $data['excerpt'] ?? Str::limit(strip_tags($data['content'] ?? ''), 200);
        $post->status = $data['status'];
        $post->post_type = $postType;
        $post->user_id = Auth::id();
        $post->parent_id = $data['parent_id'] ?? null;

        // Handle publish date.
        if ($data['status'] === PostStatus::SCHEDULED->value && ! empty($data['published_at'])) {
            $post->published_at = Carbon::parse($data['published_at']);
        } elseif ($data['status'] === PostStatus::PUBLISHED->value) {
            $post->published_at = now();
        }

        $post->save();

        // Handle featured image.
        $this->handleBuilderFeaturedImage($request, $post);

        // Handle taxonomies.
        $this->handleBuilderTaxonomies($request, $post, $postTypeModel);

        $post = $this->addHooks(
            $post,
            PostActionHook::POST_CREATED_AFTER,
            PostFilterHook::POST_CREATED_AFTER
        );

        return response()->json([
            'success' => true,
            'message' => __('Post has been created.'),
            'id' => $post->id,
            'redirect' => route('admin.posts.edit', [$postType, $post->id]),
        ]);
    }

    /**
     * Update an existing post from the LaraBuilder.
     */
    public function builderUpdate(Request $request, string $postType, string $id): JsonResponse
    {
        $post = Post::where('post_type', $postType)->findOrFail($id);
        $this->authorize('update', $post);

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:200',
            'status' => 'required|string|in:draft,published,pending,scheduled,private',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'design_json' => 'nullable|array',
            'published_at' => 'nullable|date',
            'parent_id' => 'nullable|integer|exists:posts,id',
        ]);

        $data = $request->all();

        $data = $this->addHooks(
            $data,
            PostActionHook::POST_UPDATED_BEFORE,
            PostFilterHook::POST_UPDATED_BEFORE
        );

        // Get post type model for taxonomy handling.
        $postTypeModel = $this->contentService->getPostType($postType);

        // Update post.
        $post->title = $data['title'];
        $post->slug = $data['slug'] ?? Str::slug($data['title']);
        $post->content = $data['content'] ?? '';
        $post->design_json = $data['design_json'] ?? null;
        $post->excerpt = $data['excerpt'] ?? Str::limit(strip_tags($data['content'] ?? ''), 200);
        $post->status = $data['status'];
        $post->parent_id = $data['parent_id'] ?? null;

        // Handle publish date.
        if ($data['status'] === PostStatus::SCHEDULED->value && ! empty($data['published_at'])) {
            $post->published_at = Carbon::parse($data['published_at']);
        } elseif ($data['status'] === PostStatus::PUBLISHED->value && ! $post->published_at) {
            $post->published_at = now();
        }

        $post->save();

        // Handle featured image.
        $this->handleBuilderFeaturedImage($request, $post);

        // Handle taxonomies.
        if ($postTypeModel) {
            $this->handleBuilderTaxonomies($request, $post, $postTypeModel);
        }

        $post = $this->addHooks(
            $post,
            PostActionHook::POST_UPDATED_AFTER,
            PostFilterHook::POST_UPDATED_AFTER
        );

        return response()->json([
            'success' => true,
            'message' => __('Post has been updated.'),
        ]);
    }

    /**
     * Upload an image for the post builder.
     */
    public function uploadImage(Request $request, string $postType = 'post'): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $file = $request->file('image');
        $filename = 'post_' . uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('post-images', $filename, 'public');

        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path),
        ]);
    }

    /**
     * Upload a video for the post builder.
     */
    public function uploadVideo(Request $request, string $postType = 'post'): JsonResponse
    {
        $request->validate([
            'video' => 'required|mimetypes:video/mp4,video/webm,video/ogg,video/quicktime|max:102400',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $videoFile = $request->file('video');
        $videoFilename = 'post_video_' . uniqid() . '_' . time() . '.' . $videoFile->getClientOriginalExtension();
        $videoPath = $videoFile->storeAs('post-videos', $videoFilename, 'public');

        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $thumbFile = $request->file('thumbnail');
            $thumbFilename = 'post_thumb_' . uniqid() . '_' . time() . '.' . $thumbFile->getClientOriginalExtension();
            $thumbPath = $thumbFile->storeAs('post-videos/thumbnails', $thumbFilename, 'public');
            $thumbnailUrl = asset('storage/' . $thumbPath);
        }

        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $videoPath),
            'thumbnailUrl' => $thumbnailUrl,
        ]);
    }

    /**
     * Get taxonomies with their terms for the builder.
     */
    protected function getTaxonomiesWithTerms($postTypeModel): array
    {
        $taxonomies = [];

        if (! empty($postTypeModel->taxonomies)) {
            $taxonomyObjects = $this->contentService->getTaxonomies()
                ->whereIn('name', $postTypeModel->taxonomies)
                ->all();

            foreach ($taxonomyObjects as $taxonomy) {
                $terms = \App\Models\Term::where('taxonomy', $taxonomy->name)
                    ->select('id', 'name', 'parent_id')
                    ->orderBy('name')
                    ->get()
                    ->toArray();

                $taxonomies[] = [
                    'name' => $taxonomy->name,
                    'label' => $taxonomy->label,
                    'hierarchical' => $taxonomy->hierarchical,
                    'terms' => $terms,
                ];
            }
        }

        return $taxonomies;
    }

    /**
     * Handle featured image from the builder.
     */
    protected function handleBuilderFeaturedImage(Request $request, Post $post): void
    {
        // Handle removal.
        if ($request->boolean('remove_featured_image')) {
            $post->clearMediaCollection('featured');

            return;
        }

        // Handle new image URL.
        $featuredImageUrl = $request->input('featured_image');
        if ($featuredImageUrl && filter_var($featuredImageUrl, FILTER_VALIDATE_URL)) {
            // Check if it's a different image.
            $currentUrl = $post->getFeaturedImageUrl();
            if ($featuredImageUrl !== $currentUrl) {
                $post->clearMediaCollection('featured');

                // Try to associate existing media or add from URL.
                $this->mediaService->associateExistingMedia($post, $featuredImageUrl, 'featured');
            }
        }
    }

    /**
     * Handle taxonomies from the builder.
     */
    protected function handleBuilderTaxonomies(Request $request, Post $post, $postTypeModel): void
    {
        if (empty($postTypeModel->taxonomies)) {
            return;
        }

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

        $post->terms()->sync($termIds);

        $this->addHooks(
            ['post' => $post, 'term_ids' => $termIds],
            PostActionHook::POST_TAXONOMIES_UPDATED
        );
    }

    /**
     * Generate a unique slug by appending a number if necessary.
     */
    protected function generateUniqueSlug(string $baseSlug, ?int $excludeId = null): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $query = Post::where('slug', $slug);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            if (! $query->exists()) {
                break;
            }

            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
