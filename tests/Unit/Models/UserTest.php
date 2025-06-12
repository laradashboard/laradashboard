<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_fillable_attributes()
    {
        $user = new User();
        $this->assertEquals([
            'name',
            'email',
            'password',
            'username',
        ], $user->getFillable());
    }

    #[Test]
    public function it_has_hidden_attributes()
    {
        $user = new User();
        $this->assertEquals([
            'password',
            'remember_token',
            'email_verified_at',
        ], $user->getHidden());
    }

    #[Test]
    public function it_has_casted_attributes()
    {
        $user = new User();
        $casts = $user->getCasts();
        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
    }

    #[Test]
    public function it_sends_admin_reset_password_notification_for_admin_routes()
    {
        Notification::fake();

        $user = User::factory()->create();

        // Mock the request to be an admin route
        $this->app['request']->headers->set('referer', 'http://localhost/admin/login');
        $this->app['request']->server->set('REQUEST_URI', '/admin/password/reset');

        $user->sendPasswordResetNotification('token');

        Notification::assertSentTo($user, AdminResetPasswordNotification::class);
    }

    #[Test]
    public function it_sends_default_reset_password_notification_for_non_admin_routes()
    {
        Notification::fake();

        $user = User::factory()->create();

        // Mock the request to be a non-admin route
        $this->app['request']->headers->set('referer', 'http://localhost/login');
        $this->app['request']->server->set('REQUEST_URI', '/password/reset');

        $user->sendPasswordResetNotification('token');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    #[Test]
    public function it_can_check_if_user_has_any_permission()
    {
        $user = User::factory()->create();
        $permission1 = Permission::create(['name' => 'test.permission1']);
        $permission2 = Permission::create(['name' => 'test.permission2']);

        $user->givePermissionTo($permission1);

        $this->assertTrue($user->hasAnyPermission(['test.permission1', 'test.permission2']));
        $this->assertTrue($user->hasAnyPermission('test.permission1'));
        $this->assertFalse($user->hasAnyPermission('test.permission2'));
        $this->assertTrue($user->hasAnyPermission([])); // Empty permissions should return true
    }

    #[Test]
    public function it_has_searchable_columns()
    {
        $user = new User();
        $reflection = new \ReflectionClass($user);
        $method = $reflection->getMethod('getSearchableColumns');
        $method->setAccessible(true);

        $this->assertEquals(['name', 'email', 'username'], $method->invoke($user));
    }

    #[Test]
    public function it_has_excluded_sort_columns()
    {
        $user = new User();
        $reflection = new \ReflectionClass($user);
        $method = $reflection->getMethod('getExcludedSortColumns');
        $method->setAccessible(true);

        $this->assertEquals([], $method->invoke($user));
    }
}
