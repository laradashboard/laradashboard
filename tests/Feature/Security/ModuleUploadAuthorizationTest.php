<?php

declare(strict_types=1);

beforeEach(fn () => $this->setUpSecurityUsers());

test('module upload requires superadmin even when user has module.create permission', function () {
    $response = $this->actingAs($this->adminUser)->get('/admin/modules/upload');

    $response->assertForbidden();
});

test('superadmin with module.create permission can access module upload', function () {
    $this->superadminUser->syncPermissions(['module.create', 'module.view']);

    $response = $this->actingAs($this->superadminUser)->get('/admin/modules/upload');

    $response->assertOk();
});
