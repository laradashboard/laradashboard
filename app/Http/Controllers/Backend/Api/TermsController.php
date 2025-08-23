<?php

namespace App\Http\Controllers\Backend\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Term\StoreTermRequest;
use App\Http\Requests\Term\UpdateTermRequest;
use App\Services\Content\ContentService;
use App\Services\PostService;
use App\Services\TermService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
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
    #[ExcludeRouteFromDocs]
    public function store(StoreTermRequest $request, string $taxonomyName): JsonResponse
    {
        $this->authorize('create', \App\Models\Term::class);

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

    /**
     * Update an existing term via API
     */
    #[ExcludeRouteFromDocs]
    public function update(UpdateTermRequest $request, string $taxonomyName, int $id): JsonResponse
    {
        // Check if taxonomy exists
        $taxonomy = $this->termService->getTaxonomy($taxonomyName);
        if (! $taxonomy) {
            return response()->json([
                'message' => __('Taxonomy not found'),
            ], 404);
        }

        // Find the term
        $term = $this->termService->getTermById($id, $taxonomyName);
        if (! $term) {
            return response()->json([
                'message' => __('Term not found'),
            ], 404);
        }

        $this->authorize('update', $term);

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

        $updatedTerm = $this->termService->updateTerm($term, $request->validated());

        // Get taxonomy label for message.
        $taxLabel = $this->termService->getTaxonomyLabel($taxonomyName, true);

        // Get post if post_id is provided.
        $postId = $request->input('post_id');
        $post = $postId ? $this->postService->getPostById($postId) : null;

        return response()->json([
            'message' => __(':taxLabel updated successfully.', ['taxLabel' => $taxLabel]),
            'term' => $updatedTerm,
            'taxonomies' => $taxonomies,
            'post_type' => $postTypeModel->name,
            'content' => Blade::render('backend.pages.posts.partials.post-taxonomy-chooser', [
                'taxonomy' => $taxonomy,
                'post' => $post,
                'post_id' => $post ? $post->id : null,
                'post_type' => $postTypeModel->name,
            ]),
        ]);
    }

    /**
     * Delete a term via API
     */
    #[ExcludeRouteFromDocs]
    public function destroy(string $taxonomyName, int $id): JsonResponse
    {
        // Check if taxonomy exists
        $taxonomy = $this->termService->getTaxonomy($taxonomyName);
        if (! $taxonomy) {
            return response()->json([
                'message' => __('Taxonomy not found'),
            ], 404);
        }

        // Find the term
        $term = $this->termService->getTermById($id, $taxonomyName);
        if (! $term) {
            return response()->json([
                'message' => __('Term not found'),
            ], 404);
        }

        $this->authorize('delete', $term);

        // Check if term can be deleted
        $errors = $this->termService->canDeleteTerm($term);
        if (count($errors) > 0) {
            $errorMessages = [];
            if (in_array('has_posts', $errors)) {
                $errorMessages[] = __('Cannot delete term with assigned posts');
            }
            if (in_array('has_children', $errors)) {
                $errorMessages[] = __('Cannot delete term with child terms');
            }

            return response()->json([
                'message' => implode('. ', $errorMessages),
            ], 400);
        }

        // Delete the term
        $result = $this->termService->deleteTerm($term);

        if ($result) {
            // Get taxonomy label for message.
            $taxLabel = $this->termService->getTaxonomyLabel($taxonomyName, true);

            return response()->json([
                'message' => __(':taxLabel deleted successfully.', ['taxLabel' => $taxLabel]),
            ]);
        }

        return response()->json([
            'message' => __('Failed to delete term'),
        ], 500);
    }
}
