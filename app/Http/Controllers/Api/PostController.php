<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends ApiController
{
    public function __construct(private readonly PostService $postService)
    {
    }

    /**
     * Display a listing of posts for a specific post-type.
     *
     * @tags Posts
     */
    public function index(Request $request, string $postType = 'post'): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['post.view']);

        $filters = $request->only(['search', 'status', 'author', 'term']);
        $filters['post_type'] = $postType;
        $perPage = (int) ($request->input('per_page') ?? config('settings.default_pagination', 10));

        $posts = $this->postService->getPaginatedPosts($filters, $perPage);

        return $this->resourceResponse(
            PostResource::collection($posts)->additional([
                'meta' => [
                    'pagination' => [
                        'current_page' => $posts->currentPage(),
                        'last_page' => $posts->lastPage(),
                        'per_page' => $posts->perPage(),
                        'total' => $posts->total(),
                    ],
                    'post_type' => $postType,
                ],
            ]),
            ucfirst($postType) . 's retrieved successfully'
        );
    }

    /**
     * Store a newly created post.
     *
     * @tags Posts
     */
    public function store(StorePostRequest $request, string $postType = 'post'): JsonResponse
    {
        $data = $request->validated();
        $data['post_type'] = $postType;
        $data['author_id'] = Auth::id();

        $post = $this->postService->createPost($data);

        $this->logAction('Post Created', $post);

        return $this->resourceResponse(
            new PostResource($post->load(['author', 'terms'])),
            ucfirst($postType) . ' created successfully',
            201
        );
    }

    /**
     * Display the specified post.
     *
     * @tags Posts
     */
    public function show(string $postType, int $id): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['post.view']);

        $post = Post::with(['author', 'terms', 'postMeta'])
            ->where('post_type', $postType)
            ->findOrFail($id);

        return $this->resourceResponse(
            new PostResource($post),
            ucfirst($postType) . ' retrieved successfully'
        );
    }

    /**
     * Update the specified post.
     *
     * @tags Posts
     */
    public function update(UpdatePostRequest $request, string $postType, int $id): JsonResponse
    {

        $post = Post::where('post_type', $postType)->findOrFail($id);

        $updatedPost = $this->postService->updatePost($post, $request->validated());

        $this->logAction('Post Updated', $updatedPost);

        return $this->resourceResponse(
            new PostResource($updatedPost->load(['author', 'terms'])),
            ucfirst($postType) . ' updated successfully'
        );
    }

    /**
     * Remove the specified post.
     *
     * @tags Posts
     */
    public function destroy(string $postType, int $id): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['post.delete']);

        $post = Post::where('post_type', $postType)->findOrFail($id);

        $post->delete();

        $this->logAction('Post Deleted', $post);

        return $this->successResponse(null, ucfirst($postType) . ' deleted successfully');
    }

    /**
     * Bulk delete posts.
     *
     * @tags Posts
     */
    public function bulkDelete(Request $request, string $postType): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['post.delete']);

        $request->validate([
            /** @example [1, 2, 3] */
            'ids' => 'required|array|min:1',
            /** @example 1 */
            'ids.*' => 'integer|exists:posts,id',
        ]);

        $postIds = $request->input('ids');

        $deletedCount = Post::where('post_type', $postType)
            ->whereIn('id', $postIds)
            ->delete();

        $this->logAction('Bulk Post Deletion', null, [
            'post_type' => $postType,
            'deleted_count' => $deletedCount,
        ]);

        return $this->successResponse(
            ['deleted_count' => $deletedCount],
            $deletedCount . " " . $postType . "s deleted successfully"
        );
    }
}
