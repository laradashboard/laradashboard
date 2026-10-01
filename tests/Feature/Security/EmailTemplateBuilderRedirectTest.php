<?php

declare(strict_types=1);

use App\Models\EmailTemplate;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    config(['app.url' => 'http://localhost']);

    foreach (['email_template.view', 'email_template.create', 'email_template.edit'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $role = Role::firstOrCreate(['name' => 'EmailEditor', 'guard_name' => 'web']);
    $role->syncPermissions([
        'email_template.view',
        'email_template.create',
        'email_template.edit',
    ]);

    $this->editor = User::factory()->create();
    $this->editor->assignRole($role);
});

test('email template builder omits external redirect_url from the page', function () {
    $response = $this->actingAs($this->editor)->get(
        route('admin.email-templates.create', [
            'redirect_url' => 'https://evil.test/steal',
        ])
    );

    $response->assertOk();
    $response->assertSee('data-redirect-url=""', false);
});

test('email template builder passes sanitized same-origin redirect_url', function () {
    $response = $this->actingAs($this->editor)->get(
        route('admin.email-templates.create', [
            'redirect_url' => '/admin/settings',
        ])
    );

    $response->assertOk();
    $response->assertSee('data-redirect-url="/admin/settings"', false);
});

test('email template edit builder omits external redirect_url', function () {
    $template = EmailTemplate::factory()->create();

    $response = $this->actingAs($this->editor)->get(
        route('admin.email-templates.edit', [
            'email_template' => $template,
            'redirect_url' => 'https://evil.test/steal',
        ])
    );

    $response->assertOk();
    $response->assertSee('data-redirect-url=""', false);
});
