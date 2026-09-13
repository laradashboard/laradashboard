<?php

declare(strict_types=1);

namespace Modules\Squartup\Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SquartupModuleHealthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    #[Test]
    public function health_route_loads_the_module_view(): void
    {
        $this->get('/squartup')
            ->assertOk()
            ->assertViewIs('squartup::health')
            ->assertSee('Squartup module is loaded', false);
    }

    #[Test]
    public function module_does_not_replace_laradashboard_admin_or_auth_routes(): void
    {
        $this->assertTrue(Route::has('admin.dashboard'));
        $this->assertTrue(Route::has('login'));
        $this->assertTrue(Route::has('logout'));
        $this->assertTrue(Route::has('profile.edit'));
        $this->assertFalse(Route::has('admin.squartup.dashboard'));
        $this->assertFalse(Route::has('dashboard'));
    }

    #[Test]
    public function module_does_not_register_a_catch_all_or_conflicting_paths(): void
    {
        $moduleRoutes = collect(Route::getRoutes())
            ->filter(fn ($route) => str_contains($route->getActionName(), 'Modules\\Squartup\\')
                || str_starts_with((string) $route->getName(), 'squartup.'));

        $this->assertTrue($moduleRoutes->contains(fn ($route) => $route->uri() === 'squartup'));

        foreach ($moduleRoutes as $route) {
            $uri = $route->uri();

            $this->assertStringStartsNotWith('admin', $uri);
            $this->assertNotSame('{slug}', $uri);
            $this->assertStringNotContainsString('{slug}', $uri);
            $this->assertNotSame('{any}', $uri);
            $this->assertNotSame('dashboard', $uri);
            $this->assertNotSame('profile', $uri);
            $this->assertNotSame('login', $uri);
            $this->assertNotSame('docs', $uri);
            $this->assertNotSame('api/user', $uri);
        }
    }

    #[Test]
    public function guest_can_still_reach_the_login_and_admin_entry_points(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/admin')->assertRedirect();
        $this->get('/admin/squartup')->assertNotFound();
    }
}
