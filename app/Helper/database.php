<?php

/**
 * Database helper functions for posts and terms
 */

use App\Models\Post;
use App\Models\Term;
use Illuminate\Support\Facades\Cache;

/**
 * Get a post by ID or slug
 *
 * @param  int|string  $id  Post ID or slug
 * @param  string|null  $postType  Post type
 * @return Post|null
 */
function get_post($id, ?string $postType = null)
{
    $cacheKey = "post_{$id}".($postType ? "_{$postType}" : '');

    return Cache::remember($cacheKey, 3600, function () use ($id, $postType) {
        $query = Post::query();

        if (is_numeric($id)) {
            $query->where('id', $id);
        } else {
            $query->where('slug', $id);
        }

        if ($postType) {
            $query->where('post_type', $postType);
        }

        return $query->first();
    });
}

/**
 * Get a term by ID or slug
 *
 * @param  int|string  $id  Term ID or slug
 * @param  string|null  $taxonomy  Taxonomy name
 * @return Term|null
 */
function get_term($id, ?string $taxonomy = null)
{
    $cacheKey = "term_{$id}".($taxonomy ? "_{$taxonomy}" : '');

    return Cache::remember($cacheKey, 3600, function () use ($id, $taxonomy) {
        $query = Term::query();

        if (is_numeric($id)) {
            $query->where('id', $id);
        } else {
            $query->where('slug', $id);
        }

        if ($taxonomy) {
            $query->where('taxonomy', $taxonomy);
        }

        return $query->first();
    });
}

/**
 * Get terms for a taxonomy
 *
 * @param  string  $taxonomy  Taxonomy name
 * @param  array  $args  Query arguments
 * @return \Illuminate\Database\Eloquent\Collection
 */
function get_terms(string $taxonomy, array $args = [])
{
    $query = Term::query()->where('taxonomy', $taxonomy);

    // Order
    $orderBy = $args['orderby'] ?? 'name';
    $order = $args['order'] ?? 'asc';
    $query->orderBy($orderBy, $order);

    // Parent
    if (isset($args['parent'])) {
        $query->where('parent_id', $args['parent']);
    }

    // Limit
    if (isset($args['limit'])) {
        $query->limit($args['limit']);
    }

    return $query->get();
}

/**
 * Get post terms
 *
 * @param  Post|int  $post  Post object or ID
 * @param  string|null  $taxonomy  Taxonomy name
 * @return \Illuminate\Database\Eloquent\Collection
 */
function get_post_terms($post, ?string $taxonomy = null)
{
    if (is_numeric($post)) {
        $post = get_post($post);
    }

    if (! $post) {
        return collect();
    }

    if ($taxonomy) {
        return $post->terms()->where('taxonomy', $taxonomy)->get();
    }

    return $post->terms;
}

/**
 * Get formatted post date
 *
 * @param  Post|int  $post  Post object or ID
 * @param  string  $format  Date format
 * @return string|null
 */
function get_post_date($post, string $format = 'M d, Y')
{
    if (is_numeric($post)) {
        $post = get_post($post);
    }

    if (! $post) {
        return null;
    }

    if ($post->published_at) {
        return $post->published_at->format($format);
    }

    return $post->created_at->format($format);
}

/**
 * Get the permalink/URL for a post
 *
 * @param  Post|int  $post  Post object or ID
 * @return string|null
 */
function get_permalink($post)
{
    if (is_numeric($post)) {
        $post = get_post($post);
    }

    if (! $post) {
        return null;
    }

    return route('post.show', ['post_type' => $post->post_type, 'slug' => $post->slug]);
}
