<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Post;
use App\Models\User;
use App\Services\Content\ContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TextEditorBlockBuilderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->admin = User::factory()->create();

        $role = Role::firstOrCreate(['name' => 'content-admin', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'post.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'post.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'post.edit', 'guard_name' => 'web']);
        $role->givePermissionTo(['post.view', 'post.create', 'post.edit']);
        $this->admin->assignRole($role);

        app(ContentService::class)->registerPostType([
            'name' => 'post',
            'label' => 'Posts',
            'label_singular' => 'Post',
            'description' => 'Default post type for blog entries',
            'taxonomies' => ['category', 'tag'],
        ]);
    }

    public function test_create_route_loads_builder_for_text_editor(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/posts/post/create');

        $response->assertOk();
        $response->assertSee('lara-builder-root', false);
        $response->assertSee('data-context="page"', false);
    }

    public function test_edit_route_loads_text_editor_block_from_design_json(): void
    {
        $post = Post::factory()->create([
            'title' => 'Text Editor Post',
            'post_type' => 'post',
            'status' => PostStatus::DRAFT->value,
            'user_id' => $this->admin->id,
            'content' => '<div data-lara-block="text-editor" data-props=\'{"content":"&lt;p&gt;Stored rich text&lt;/p&gt;","align":"left","color":"#333333","fontSize":"16px","lineHeight":"1.6","layoutStyles":{}}\'></div>',
            'design_json' => [
                'version' => 1,
                'blocks' => [
                    [
                        'id' => 'te-1',
                        'type' => 'text-editor',
                        'props' => [
                            'content' => '<p>Stored rich text</p>',
                            'align' => 'left',
                            'color' => '#333333',
                            'fontSize' => '16px',
                            'lineHeight' => '1.6',
                        ],
                    ],
                ],
                'canvasSettings' => [],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/posts/post/{$post->id}/edit");

        $response->assertOk();
        $response->assertSee('lara-builder-root', false);
        $response->assertSee('text-editor', false);
        $response->assertSee('Stored rich text', false);
    }

    public function test_builder_store_persists_text_editor_design_json(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/posts/post', [
                'title' => 'New Text Editor Post',
                'content' => '<div data-lara-block="text-editor" data-props=\'{"content":"&lt;p&gt;&lt;strong&gt;Bold save&lt;/strong&gt;&lt;/p&gt;","align":"left","color":"#333333","fontSize":"16px","lineHeight":"1.6","layoutStyles":{}}\'></div>',
                'design_json' => [
                    'version' => 1,
                    'blocks' => [
                        [
                            'id' => 'te-new',
                            'type' => 'text-editor',
                            'props' => [
                                'content' => '<p><strong>Bold save</strong></p>',
                                'align' => 'left',
                                'color' => '#333333',
                                'fontSize' => '16px',
                                'lineHeight' => '1.6',
                            ],
                        ],
                    ],
                    'canvasSettings' => [],
                ],
                'excerpt' => '',
                'status' => PostStatus::DRAFT->value,
            ]);

        $response->assertSuccessful();

        $post = Post::query()->where('title', 'New Text Editor Post')->first();
        $this->assertNotNull($post);
        $this->assertSame('text-editor', $post->design_json['blocks'][0]['type'] ?? null);
        $this->assertStringContainsString('Bold save', $post->design_json['blocks'][0]['props']['content'] ?? '');
    }
}
