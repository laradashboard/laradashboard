<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    Permission::firstOrCreate(['name' => 'media.view', 'guard_name' => 'web']);
    $this->user->givePermissionTo('media.view');
});

test('media index page loads', function () {
    $response = $this->actingAs($this->user)->get(route('admin.media.index'));

    $response->assertOk();
    $response->assertSee(__('Media Library'));
});

test('media index does not break out of the x-data attribute', function () {
    // A double quote inside the double-quoted x-data attribute terminates it early,
    // which kills the Alpine component and leaks the rest of the JS as page text.
    $response = $this->actingAs($this->user)->get(route('admin.media.index'));

    $response->assertOk();

    $previous = libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($response->getContent());
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $root = $dom->getElementById('mediaManager');
    expect($root)->not->toBeNull();

    // The last member of the Alpine object survives only if the attribute never
    // got cut short, so this proves the whole expression made it through intact.
    $xData = $root->getAttribute('x-data');
    expect($xData)
        ->toContain('showSingleDeleteModal(id)')
        ->and(rtrim($xData))->toEndWith('}');
});

test('media index emits the doctype first', function () {
    // The Alpine wrapper must live inside the layout component: any element emitted
    // before <!DOCTYPE html> puts the browser into quirks mode.
    $response = $this->actingAs($this->user)->get(route('admin.media.index'));

    $response->assertOk();
    expect(strtolower(ltrim($response->getContent())))->toStartWith('<!doctype html');
});
