<?php

declare(strict_types=1);

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Role;

/*
 * MenuItem::getUrl() used to return a custom menu item's `target` verbatim, and the
 * public navigation rendered it straight into an href. Blade's {{ }} only
 * HTML-entity-escapes; it does not restrict URL schemes, so a saved
 * `javascript:alert(document.cookie)` target became a live, site-wide stored-XSS
 * payload. See GHSA-883p-xm36-3p99.
 */

beforeEach(function () {
    $this->setUpSecurityUsers();
});

test('a javascript: custom target is rejected by the add-item endpoint', function () {
    $menu = Menu::create(['name' => 'Main Navigation', 'location' => 'test-nav-'.uniqid()]);

    $response = $this->actingAs($this->superadminUser)->postJson(
        "/admin/menus/{$menu->id}/items",
        [
            'label' => 'Click me',
            'type' => 'custom',
            'target' => 'javascript:alert(document.cookie)',
        ]
    );

    $response->assertInvalid(['target']);
    expect(MenuItem::where('menu_id', $menu->id)->count())->toBe(0);
});

test('a javascript: custom target is rejected by the update-item endpoint', function () {
    $menu = Menu::create(['name' => 'Main Navigation', 'location' => 'test-nav-'.uniqid()]);
    $item = MenuItem::create([
        'menu_id' => $menu->id,
        'label' => 'Click me',
        'type' => 'custom',
        'target' => 'https://example.com',
        'position' => 0,
    ]);

    $response = $this->actingAs($this->superadminUser)->putJson(
        "/admin/menus/{$menu->id}/items/{$item->id}",
        [
            'label' => 'Click me',
            'type' => 'custom',
            'target' => 'JavaScript:alert(1)',
        ]
    );

    $response->assertInvalid(['target']);
    expect($item->fresh()->target)->toBe('https://example.com');
});

test('a dangerous custom target that reaches the model is still never rendered as a live href', function (string $dangerousTarget) {
    $menu = Menu::create(['name' => 'Main Navigation', 'location' => 'test-nav-'.uniqid()]);

    // Bypass validation entirely (an older row, a seeder, a direct DB edit) to prove
    // the model itself is the backstop, not just the controller-level validation rule.
    $item = MenuItem::create([
        'menu_id' => $menu->id,
        'label' => 'Click me',
        'type' => 'custom',
        'target' => $dangerousTarget,
        'position' => 0,
    ]);

    expect($item->getUrl())->toBe('#');

    $html = view('components.menu-items', [
        'items' => collect([$item]),
        'depth' => 0,
        'itemClass' => '',
        'linkClass' => '',
        'activeClass' => 'active',
        'showIcons' => false,
        'maxDepth' => null,
    ])->render();

    expect($html)
        ->toContain('href="#"')
        ->not->toContain('javascript:')
        ->not->toContain('vbscript:')
        ->not->toContain('data:text/html');
})->with([
    'javascript:alert(document.cookie)',
    'JavaScript:alert(1)',
    "   javascript:alert(1)",
    "java\tscript:alert(1)",
    'vbscript:msgbox(1)',
    'data:text/html,<script>alert(1)</script>',
]);

test('ordinary custom targets keep working', function (string $safeTarget) {
    $menu = Menu::create(['name' => 'Main Navigation', 'location' => 'test-nav-'.uniqid()]);
    $item = MenuItem::create([
        'menu_id' => $menu->id,
        'label' => 'Click me',
        'type' => 'custom',
        'target' => $safeTarget,
        'position' => 0,
    ]);

    expect($item->getUrl())->toBe($safeTarget);
})->with([
    'https://example.com',
    'http://example.com/path?x=1',
    'mailto:person@example.com',
    'tel:+15551234567',
    '/about',
    '#section',
]);

test('a lower-privileged role granted menu.edit is also protected', function () {
    Role::firstOrCreate(['name' => Role::ADMIN, 'guard_name' => 'web']);
    $editor = $this->createUserWithRole(Role::ADMIN, ['settings.edit', 'menu.view', 'menu.edit']);
    $menu = Menu::create(['name' => 'Main Navigation', 'location' => 'test-nav-'.uniqid()]);

    $response = $this->actingAs($editor)->postJson(
        "/admin/menus/{$menu->id}/items",
        [
            'label' => 'Click me',
            'type' => 'custom',
            'target' => 'javascript:alert(document.cookie)',
        ]
    );

    $response->assertInvalid(['target']);
});
