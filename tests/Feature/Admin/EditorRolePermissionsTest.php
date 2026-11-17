<?php

declare(strict_types=1);

use App\Services\RolesService;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('the predefined editor role can work with posts and pages', function () {
    $permissions = app(RolesService::class)->getPredefinedRolePermissions('Editor');

    expect($permissions)->toContain('post.view', 'post.create', 'post.edit', 'post.publish', 'media.view')
        ->not->toContain('post.delete', 'user.view', 'settings.edit');
});
