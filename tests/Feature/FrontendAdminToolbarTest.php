<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Hooks\AdminFilterHook;
use App\Models\Post;
use App\Models\User;
use App\Support\Facades\Hook;
use App\View\Components\FrontendAdminToolbar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendAdminToolbarTest extends TestCase
{
    use RefreshDatabase;

    public function test_toolbar_not_rendered_for_guests(): void
    {
        $component = new FrontendAdminToolbar();

        $this->assertFalse($component->shouldRender());
    }

    public function test_toolbar_rendered_for_admin_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        $component = new FrontendAdminToolbar();

        $this->assertTrue($component->shouldRender());
        $this->assertNotEmpty($component->leftItems);
    }

    public function test_toolbar_has_default_items(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Superadmin');
        $this->actingAs($user);

        $component = new FrontendAdminToolbar();

        $ids = array_column($component->leftItems, 'id');
        $this->assertContains('dashboard', $ids);
        $this->assertContains('new-page', $ids);
        $this->assertContains('new-post', $ids);
    }

    public function test_toolbar_includes_edit_current_when_context_post_provided(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        $post = Post::factory()->create(['post_type' => 'page']);
        $component = new FrontendAdminToolbar(contextPost: $post);

        $ids = array_column($component->leftItems, 'id');
        $this->assertContains('edit-current', $ids);
    }

    public function test_toolbar_no_edit_current_without_context_post(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        $component = new FrontendAdminToolbar();

        $ids = array_column($component->leftItems, 'id');
        $this->assertNotContains('edit-current', $ids);
    }

    public function test_toolbar_items_sorted_by_priority(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        $component = new FrontendAdminToolbar();

        $priorities = array_column($component->leftItems, 'priority');
        $sorted = $priorities;
        sort($sorted);
        $this->assertEquals($sorted, $priorities);
    }

    public function test_toolbar_disabled_via_hook(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        Hook::addFilter(AdminFilterHook::FRONTEND_TOOLBAR_ENABLED, function () {
            return false;
        });

        $component = new FrontendAdminToolbar();

        $this->assertFalse($component->shouldRender());
    }

    public function test_toolbar_items_can_be_added_via_hook(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        Hook::addFilter(AdminFilterHook::FRONTEND_TOOLBAR_ITEMS, function (array $items) {
            $items[] = [
                'id' => 'custom-item',
                'label' => 'Custom',
                'url' => '/custom',
                'icon' => 'lucide:star',
                'position' => 'left',
                'priority' => 25,
                'permission' => null,
                'separator' => false,
            ];

            return $items;
        });

        $component = new FrontendAdminToolbar();

        $ids = array_column($component->leftItems, 'id');
        $this->assertContains('custom-item', $ids);
    }
}
