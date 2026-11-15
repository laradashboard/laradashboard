<?php

declare(strict_types=1);

use App\Models\Menu;
use App\Models\MenuItem;
use App\Livewire\Admin\Menus\Builder;
use App\Models\Role;
use Livewire\Livewire;

// GHSA-883p-xm36-3p99: a custom menu item's target was rendered verbatim as the nav href.

beforeEach(function () {
    $this->setUpSecurityUsers();
});

test('a javascript: custom target is rejected by the add-item endpoint', function () {
    $menu = Menu::create(['name' => 'Main Navigation', 'location' => 'test-nav-'.uniqid()]);

    $response = $this->actingAs($this->superadminUser)->postJson(
        route('admin.menus.items.store', $menu),
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
        route('admin.menus.items.update', [$menu, $item]),
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
    "\x01javascript:alert(1)",
    "\x0Cjavascript:alert(1)",
    "\x1Fjavascript:alert(1)",
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
        route('admin.menus.items.store', $menu),
        [
            'label' => 'Click me',
            'type' => 'custom',
            'target' => 'javascript:alert(document.cookie)',
        ]
    );

    $response->assertInvalid(['target']);
});

test('the menu builder rejects a script target', function (string $dangerousTarget) {
    $menu = Menu::create(['name' => 'Main Navigation', 'location' => 'test-nav-'.uniqid()]);

    $this->actingAs($this->superadminUser);

    Livewire::test(Builder::class, ['menu' => $menu])
        ->set('label', 'Click me')
        ->set('type', 'custom')
        ->set('target', $dangerousTarget)
        ->call('saveItem')
        ->assertHasErrors('target');

    expect(MenuItem::where('menu_id', $menu->id)->count())->toBe(0);
})->with([
    'javascript:alert(document.cookie)',
    "\x0Cjavascript:alert(1)",
]);
