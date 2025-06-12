<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Post;
use App\Models\Taxonomy;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TermTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_fillable_attributes()
    {
        $term = new Term();
        $this->assertEquals([
            'name',
            'slug',
            'taxonomy',
            'description',
            'featured_image',
            'parent_id',
        ], $term->getFillable());
    }

    #[Test]
    public function it_auto_generates_slug_when_creating()
    {
        $term = Term::create([
            'name' => 'Test Term',
            'taxonomy' => 'category',
        ]);

        $this->assertEquals('test-term', $term->slug);
    }

    #[Test]
    public function it_auto_generates_slug_when_updating_name_with_empty_slug()
    {
        $term = Term::create([
            'name' => 'Test Term',
            'taxonomy' => 'category',
            'slug' => 'custom-slug',
        ]);

        $this->assertEquals('custom-slug', $term->slug);

        // Ensure term exists and has required properties.
        $this->assertNotNull($term);
        $this->assertInstanceOf(Term::class, $term);

        // Update with empty slug
        $term->update([
            'name' => 'Updated Term',
            'slug' => '',
        ]);

        $refreshedTerm = $term->fresh();
        $this->assertNotNull($refreshedTerm);
        $this->assertEquals('updated-term', $refreshedTerm->slug);
    }

    #[Test]
    public function it_has_taxonomy_relationship()
    {
        // Create taxonomy
        $taxonomy = Taxonomy::create([
            'name' => 'test_taxonomy',
            'label' => 'Test Taxonomy',
            'description' => 'Test taxonomy description',
            'label_singular' => 'Test Taxonomy',
        ]);

        $term = Term::create([
            'name' => 'Test Term',
            'taxonomy' => 'test_taxonomy',
        ]);

        $taxonomyModel = $term->taxonomyModel()->first();
        $this->assertEquals($taxonomy->name, $taxonomyModel->name);
    }

    #[Test]
    public function it_has_parent_and_children_relationships()
    {
        $parent = Term::create([
            'name' => 'Parent Term',
            'taxonomy' => 'category',
        ]);

        $child = Term::create([
            'name' => 'Child Term',
            'taxonomy' => 'category',
            'parent_id' => $parent->id,
        ]);

        $this->assertInstanceOf(Term::class, $child->parent);
        $this->assertEquals($parent->id, $child->parent->id);

        $this->assertCount(1, $parent->children);
        $this->assertEquals($child->id, $parent->children->first()->id);
    }

    #[Test]
    public function it_has_posts_relationship()
    {
        $user = \App\Models\User::factory()->create();

        $term = Term::create([
            'name' => 'Test Term',
            'taxonomy' => 'category',
        ]);

        $post = Post::create([
            'title' => 'Test Post',
            'post_type' => 'post',
            'content' => 'Test content',
            'status' => 'publish',
            'user_id' => $user->id,
        ]);

        // Ensure models were created successfully.
        $this->assertNotNull($term);
        $this->assertNotNull($post);
        $this->assertInstanceOf(Term::class, $term);
        $this->assertInstanceOf(Post::class, $post);

        // Get IDs safely.
        $termId = $term->getKey();
        $postId = $post->getKey();

        $this->assertNotNull($termId);
        $this->assertNotNull($postId);

        // Now attach the post to the term.
        $post->terms()->attach($termId);

        $posts = $term->posts()->get();
        $this->assertCount(1, $posts);
        $this->assertEquals($postId, $posts->first()->getKey());
    }

    #[Test]
    public function it_can_sort_by_post_count()
    {
        $user = \App\Models\User::factory()->create();

        // Create terms
        $term1 = Term::create([
            'name' => 'Term 1',
            'taxonomy' => 'category',
        ]);

        $term2 = Term::create([
            'name' => 'Term 2',
            'taxonomy' => 'category',
        ]);

        // Create posts
        $post1 = Post::create([
            'title' => 'Post 1',
            'post_type' => 'post',
            'content' => 'Content 1',
            'status' => 'publish',
            'user_id' => $user->id,
        ]);

        $post2 = Post::create([
            'title' => 'Post 2',
            'post_type' => 'post',
            'content' => 'Content 2',
            'status' => 'publish',
            'user_id' => $user->id,
        ]);

        // Ensure models were created successfully.
        $this->assertNotNull($term1);
        $this->assertNotNull($term2);
        $this->assertNotNull($post1);
        $this->assertNotNull($post2);

        // Get IDs safely.
        $term1Id = $term1->getKey();
        $term2Id = $term2->getKey();
        $post1Id = $post1->getKey();
        $post2Id = $post2->getKey();

        $this->assertNotNull($term1Id);
        $this->assertNotNull($term2Id);
        $this->assertNotNull($post1Id);
        $this->assertNotNull($post2Id);

        // Attach posts to terms.
        $term1->posts()->attach($post1Id);
        $term2->posts()->attach([$post1Id, $post2Id]);

        // Sort by post count ascending
        $termModel = new Term();
        $query = Term::where('taxonomy', 'category');
        $termModel->sortByPostCount($query, 'asc');
        $ascTerms = $query->get();

        $this->assertEquals('Term 1', $ascTerms->first()->name);
        $this->assertEquals('Term 2', $ascTerms->last()->name);

        // Sort by post count descending
        $query = Term::where('taxonomy', 'category');
        $termModel->sortByPostCount($query, 'desc');
        $descTerms = $query->get();

        $this->assertEquals('Term 2', $descTerms->first()->name);
        $this->assertEquals('Term 1', $descTerms->last()->name);
    }

    #[Test]
    public function it_has_searchable_columns()
    {
        $term = new Term();
        $reflection = new \ReflectionClass($term);
        $method = $reflection->getMethod('getSearchableColumns');
        $method->setAccessible(true);

        $this->assertEquals(['name', 'slug', 'description'], $method->invoke($term));
    }

    #[Test]
    public function it_has_excluded_sort_columns()
    {
        $term = new Term();
        $reflection = new \ReflectionClass($term);
        $method = $reflection->getMethod('getExcludedSortColumns');
        $method->setAccessible(true);

        $this->assertEquals(['description'], $method->invoke($term));
    }
}
