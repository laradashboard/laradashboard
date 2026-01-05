<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

$guestRoutes = [
    '/login',
    '/password/reset',
    '/password/reset/test-token',
];

$authRoutes = [
    '/admin',
    '/admin/roles',
    '/admin/roles/create',
    '/admin/roles/1/edit',
    '/admin/permissions',
    '/admin/permissions/1',
    '/admin/users',
    '/admin/users/create',
    '/admin/users/1/edit',
    '/admin/modules',
    '/admin/action-log',
    '/admin/posts/post',
    '/admin/posts/post/create',
    '/admin/posts/post/1/edit',
    '/admin/posts/post/1',
    '/admin/posts/page',
    '/admin/posts/page/create',
    '/admin/posts/page/2/edit',
    '/admin/posts/page/2',
    '/admin/terms/category',
    '/admin/terms/category/1/edit',
    '/admin/terms/tag',
    '/admin/terms/tag/2/edit',
    '/admin/settings',
    '/admin/settings?tab=general',
    '/admin/settings?tab=appearance',
    '/admin/settings?tab=content',
    '/admin/settings?tab=integrations',
    '/admin/translations',
];

test('guest routes are accessible', function (string $route) {
    $this->get($route)->assertStatus(200);
})->with($guestRoutes);

test('authenticated routes are accessible', function () use ($authRoutes) {
    // Run seeders using Laravel's Artisan facade.
    $this->artisan('db:seed', ['--force' => true]);

    // Find the superadmin user.
    $user = User::where('username', 'superadmin')->first();

    // Act as the superadmin user and check each route.
    array_map(function ($route) use ($user) {
        // @phpstan-ignore-next-line
        $this->actingAs($user)->get($route)->assertStatus(200);
    }, $authRoutes);
});
