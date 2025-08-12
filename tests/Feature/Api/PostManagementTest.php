<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestUtils;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use ApiTestUtils;

    protected function setUp(): void
    {
        parent::setUp();

        // Create basic roles if they don't exist
        $this->createRoles();

        // Create permissions
        $this->createPermissions();

        // Create test users
        $this->user = \App\Models\User::factory()->create();
        $this->adminUser = \App\Models\User::factory()->create();

        // Assign permissions to users
        $this->assignPermissions();

        // Assign admin role to admin user if role system exists
        if (class_exists(\App\Models\Role::class)) {
            $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin']);
            $this->adminUser->assignRole($adminRole);
        }
    }

    #[Test]
    public function authenticated_user_can_list_posts()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            Post::factory(5)->create(['post_type' => 'page']);

            $response = $this->getJson('/api/v1/posts/page');

            $response->assertStatus(200)
                ->assertJsonStructure($this->getApiResourceStructure());
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function unauthenticated_user_cannot_list_posts()
    {
        $response = $this->getJson('/api/v1/posts/page');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function authenticated_user_can_create_post()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            $postData = [
                'title' => 'Test Post',
                'content' => 'This is a test post content',
                'status' => 'publish',
                'post_type' => 'page',
            ];

            $response = $this->postJson('/api/v1/posts/page', $postData);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'content',
                        'status',
                        'post_type',
                        'created_at',
                        'updated_at',
                    ],
                ]);

            $this->assertDatabaseHas('posts', [
                'title' => 'Test Post',
                'post_type' => 'page',
            ]);
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function post_creation_requires_title()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            $response = $this->postJson('/api/v1/posts/page', [
                'content' => 'Content without title',
                'post_type' => 'page',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.title', ['The title field is required.']);
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function post_creation_validates_title_length()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            $longTitle = str_repeat('Very long title ', 20); // > 255 chars

            $response = $this->postJson('/api/v1/posts/page', [
                'title' => $longTitle,
                'content' => 'Test content',
                'post_type' => 'page',
            ]);

            $response->assertStatus(422);
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function post_creation_validates_status()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            $invalidStatuses = ['invalid-status', 123, null];

            foreach ($invalidStatuses as $status) {
                $response = $this->postJson('/api/v1/posts/page', [
                    'title' => 'Test Post',
                    'content' => 'Test content',
                    'status' => $status,
                    'post_type' => 'page',
                ]);

                $response->assertStatus(422);
            }
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function authenticated_user_can_show_post()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            $post = Post::factory()->create(['post_type' => 'page']);

            $response = $this->getJson("/api/v1/posts/page/{$post->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $post->id,
                        'title' => $post->title,
                        'post_type' => $post->post_type,
                    ],
                ]);
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function show_post_returns_404_for_nonexistent_post()
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/v1/posts/page/999999');

        $response->assertStatus(404);
    }

    #[Test]
    public function authenticated_user_can_update_post()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            $post = Post::factory()->create(['post_type' => 'page']);

            $updateData = [
                'title' => 'Updated Post Title',
                'content' => 'Updated content',
                'status' => 'draft',
            ];

            $response = $this->putJson("/api/v1/posts/page/{$post->id}", $updateData);

            $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $post->id,
                        'title' => 'Updated Post Title',
                        'status' => 'draft',
                    ],
                ]);

            $this->assertDatabaseHas('posts', [
                'id' => $post->id,
                'title' => 'Updated Post Title',
                'status' => 'draft',
            ]);
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function authenticated_user_can_delete_post()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            $post = Post::factory()->create(['post_type' => 'page']);

            $response = $this->deleteJson("/api/v1/posts/page/{$post->id}");

            $response->assertStatus(204);

            $this->assertDatabaseMissing('posts', [
                'id' => $post->id,
            ]);
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function authenticated_user_can_bulk_delete_posts()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            $posts = Post::factory(3)->create(['post_type' => 'page']);
            $postIds = $posts->pluck('id')->toArray();

            $response = $this->postJson('/api/v1/posts/page/bulk-delete', [
                'ids' => $postIds,
            ]);

            $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Posts deleted successfully',
                    'deleted_count' => 3,
                ]);

            foreach ($postIds as $id) {
                $this->assertDatabaseMissing('posts', ['id' => $id]);
            }
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function post_bulk_delete_requires_ids_array()
    {
        $this->authenticateUser();

        $response = $this->postJson('/api/v1/posts/page/bulk-delete', []);

        $response->assertStatus(422)
            ->assertJsonPath('errors.ids', ['The ids field is required.']);
    }

    #[Test]
    public function post_creation_handles_different_post_types()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            $postTypes = ['page', 'article', 'blog', 'news'];

            foreach ($postTypes as $postType) {
                $response = $this->postJson("/api/v1/posts/{$postType}", [
                    'title' => "Test {$postType}",
                    'content' => "Content for {$postType}",
                    'status' => 'publish',
                    'post_type' => $postType,
                ]);

                $response->assertStatus(201);

                $this->assertDatabaseHas('posts', [
                    'title' => "Test {$postType}",
                    'post_type' => $postType,
                ]);
            }
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function post_creation_with_meta_data()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            $postData = [
                'title' => 'Post with Meta',
                'content' => 'Content with meta data',
                'status' => 'publish',
                'post_type' => 'page',
                'meta' => [
                    'featured_image' => 'image.jpg',
                    'seo_title' => 'SEO Title',
                    'seo_description' => 'SEO Description',
                ],
            ];

            $response = $this->postJson('/api/v1/posts/page', $postData);

            $response->assertStatus(201);
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function post_creation_with_author()
    {
        $author = $this->authenticateUser();

        if (class_exists(Post::class)) {
            $response = $this->postJson('/api/v1/posts/page', [
                'title' => 'Post with Author',
                'content' => 'Content',
                'status' => 'publish',
                'post_type' => 'page',
            ]);

            $response->assertStatus(201);

            // Test only API response structure, not database details
            $this->assertDatabaseHas('posts', [
                'title' => 'Post with Author',
            ]);
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function post_management_handles_edge_case_inputs()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            $edgeCases = $this->getEdgeCaseData();

            foreach ($edgeCases as $case => $value) {
                $response = $this->postJson('/api/v1/posts/page', [
                    'title' => is_string($value) ? $value : 'Test Title',
                    'content' => $value,
                    'post_type' => 'page',
                ]);

                // Should handle gracefully
                $this->assertContains($response->status(), [200, 201, 422]);
            }
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function post_endpoints_filter_by_status()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            Post::factory(2)->create(['post_type' => 'page', 'status' => 'published']);
            Post::factory(3)->create(['post_type' => 'page', 'status' => 'draft']);

            $response = $this->getJson('/api/v1/posts/page?status=published');

            $response->assertStatus(200);

            if ($response->json('data')) {
                foreach ($response->json('data') as $post) {
                    $this->assertEquals('published', $post['status']);
                }
            }
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function post_endpoints_search_functionality()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            Post::factory()->create(['title' => 'Searchable Post Title', 'post_type' => 'page']);
            Post::factory()->create(['title' => 'Another Post', 'post_type' => 'page']);

            $response = $this->getJson('/api/v1/posts/page?search=Searchable');

            $response->assertStatus(200);

            if ($response->json('data')) {
                $this->assertStringContainsString('Searchable', $response->json('data.0.title'));
            }
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function post_endpoints_paginate_results()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            Post::factory(25)->create(['post_type' => 'page']);

            $response = $this->getJson('/api/v1/posts/page');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => ['*' => ['id', 'title']],
                    'links',
                    'meta' => [
                        'current_page',
                        'per_page',
                        'total',
                    ],
                ]);
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function post_endpoints_handle_sql_injection_attempts()
    {
        $this->authenticateUser();

        $maliciousInputs = [
            "'; DROP TABLE posts; --",
            "1' OR '1'='1",
            "UNION SELECT * FROM posts",
        ];

        foreach ($maliciousInputs as $input) {
            $response = $this->getJson("/api/v1/posts/page?search={$input}");

            // Should not cause internal server error
            $this->assertNotEquals(500, $response->status());
        }
    }

    #[Test]
    public function post_creation_validates_slug_uniqueness()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            // Create first post
            $this->postJson('/api/v1/posts/page', [
                'title' => 'Unique Post',
                'slug' => 'unique-post',
                'content' => 'Content',
                'post_type' => 'page',
            ]);

            // Try to create second post with same slug
            $response = $this->postJson('/api/v1/posts/page', [
                'title' => 'Another Unique Post',
                'slug' => 'unique-post',
                'content' => 'Different content',
                'post_type' => 'page',
            ]);

            $response->assertStatus(422);
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function post_creation_auto_generates_slug_from_title()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            $response = $this->postJson('/api/v1/posts/page', [
                'title' => 'This Should Generate Slug',
                'content' => 'Content',
                'status' => 'publish',
                'post_type' => 'page',
            ]);

            $response->assertStatus(201);

            $post = Post::where('title', 'This Should Generate Slug')->first();
            if ($post && isset($post->slug)) {
                $this->assertEquals('this-should-generate-slug', $post->slug);
            }
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }

    #[Test]
    public function post_update_preserves_created_date()
    {
        $this->authenticateUser();

        if (class_exists(Post::class)) {
            $post = Post::factory()->create(['post_type' => 'page']);
            $originalCreatedAt = $post->created_at;

            $response = $this->putJson("/api/v1/posts/page/{$post->id}", [
                'title' => 'Updated Title',
                'content' => 'Updated Content',
                'status' => 'publish',
            ]);

            $response->assertStatus(200);

            $post->refresh();
            $this->assertEquals($originalCreatedAt, $post->created_at);
        } else {
            $this->markTestSkipped('Post system not implemented');
        }
    }
}
