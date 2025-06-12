<?php

namespace App\Http\Controllers\Backend\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Term\StoreTermRequest;
use App\Services\Content\ContentService;
use App\Services\PostService;
use App\Services\TermService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Blade;

class TermsController extends Controller
{
    public function __construct(
        private readonly ContentService $contentService,
        private readonly TermService $termService,
        private readonly PostService $postService
    ) {
    }

    /**
     * Store a new term via API
     */
    public function store(StoreTermRequest $request, string $taxonomyName): JsonResponse
    {
        // Check if taxonomy exists
        $taxonomy = $this->termService->getTaxonomy($taxonomyName);
        if (! $taxonomy) {
            return response()->json([
                'message' => __('Taxonomy not found'),
            ], 404);
        }

        $taxonomies = [];
        $post_type = $request->input('post_type', null);
        $postTypeModel = $this->contentService->getPostType($post_type);
        if (! $postTypeModel) {
            return response()->json([
                'message' => __('Post type not found'),
            ], 404);
        }

        if (! empty($postTypeModel->taxonomies)) {
            $taxonomies = $this->contentService->getTaxonomies()
                ->whereIn('name', $postTypeModel->taxonomies)
                ->all();
        }

        $term = $this->termService->createTerm($request->validated(), $taxonomyName);

        // Get taxonomy label for message.
        $taxLabel = $this->termService->getTaxonomyLabel($taxonomyName, true);

        // Get post if post_id is provided.
        $postId = $request->input('post_id');
        $post = $postId ? $this->postService->getPostById($postId) : null;

        return response()->json([
            'message' => __(':taxLabel created successfully.', ['taxLabel' => $taxLabel]),
            'term' => $term,
            'taxonomies' => $taxonomies,
            'post_type' => $postTypeModel->name,
            'content' => Blade::render('backend.pages.posts.partials.post-taxonomy-chooser', [
                'taxonomy' => $taxonomy,
                'post' => $post,
                'post_id' => $post ? $post->id : null,
                'post_type' => $postTypeModel->name,
            ]),
        ], 201);
    }
}
