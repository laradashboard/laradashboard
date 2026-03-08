<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\EmailTemplate;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $this->user = User::factory()->create();
    $this->authorizedUser = User::factory()->create();
});

// ─── LocaleController ─────────────────────────────────────────────────────────

test('unauthenticated user cannot switch locale', function () {
    $response = $this->get(route('locale.switch', ['lang' => 'en']));

    $response->assertRedirect(route('login'));
});

test('authenticated user can switch locale', function () {
    $response = $this->actingAs($this->user)->get(route('locale.switch', ['lang' => 'bn']));

    $response->assertRedirect();
    expect(session('locale'))->toBe('bn');
});

// ─── AiCommandController ──────────────────────────────────────────────────────

test('user without ai_content.generate permission cannot access ai command process', function () {
    $response = $this->actingAs($this->user)->postJson(route('admin.ai.command.process'), [
        'command' => 'Create a post about testing',
    ]);

    $response->assertForbidden();
});

test('user without ai_content.generate permission cannot access ai command status', function () {
    $response = $this->actingAs($this->user)->getJson(route('admin.ai.command.status'));

    $response->assertForbidden();
});

test('user without ai_content.generate permission cannot access ai command examples', function () {
    $response = $this->actingAs($this->user)->getJson(route('admin.ai.command.examples'));

    $response->assertForbidden();
});

test('user with ai_content.generate permission can access ai command examples', function () {
    $role = Role::firstOrCreate(['name' => 'AiUser', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'ai_content.generate', 'guard_name' => 'web']);
    $role->givePermissionTo('ai_content.generate');
    $this->authorizedUser->assignRole($role);

    $response = $this->actingAs($this->authorizedUser)->getJson(route('admin.ai.command.examples'));

    $response->assertOk();
});

test('user with ai_content.generate permission can access ai command status', function () {
    $role = Role::firstOrCreate(['name' => 'AiUser', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'ai_content.generate', 'guard_name' => 'web']);
    $role->givePermissionTo('ai_content.generate');
    $this->authorizedUser->assignRole($role);

    $response = $this->actingAs($this->authorizedUser)->getJson(route('admin.ai.command.status'));

    $response->assertOk();
});

// ─── SendTestEmailController ──────────────────────────────────────────────────

test('user without settings.edit permission cannot send test email', function () {
    $emailTemplate = EmailTemplate::factory()->create();

    $response = $this->actingAs($this->user)->postJson(route('admin.emails.send-test'), [
        'type' => 'email-template',
        'id' => $emailTemplate->id,
        'email' => 'test@example.com',
    ]);

    $response->assertForbidden();
});

test('user with settings.edit permission can send test email', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'settings.edit', 'guard_name' => 'web']);
    $role->givePermissionTo('settings.edit');
    $this->authorizedUser->assignRole($role);

    Setting::factory()->mailFromAddress('dev@example.com', 'Laravel App')->create();
    Setting::factory()->mailFromName('Laravel App')->create();

    $emailTemplate = EmailTemplate::factory()->create();

    $response = $this->actingAs($this->authorizedUser)->postJson(route('admin.emails.send-test'), [
        'type' => 'email-template',
        'id' => $emailTemplate->id,
        'email' => 'test@example.com',
    ]);

    $response->assertOk();
});

// ─── DuplicateEmailTemplateController ─────────────────────────────────────────

test('user without settings.edit permission cannot duplicate email template', function () {
    $emailTemplate = EmailTemplate::factory()->create();

    $response = $this->actingAs($this->user)->post(
        route('admin.email-templates.duplicate', $emailTemplate),
        ['name' => 'Duplicated Template']
    );

    $response->assertForbidden();
});

test('user with settings.edit permission can duplicate email template', function () {
    $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'settings.edit', 'guard_name' => 'web']);
    $role->givePermissionTo('settings.edit');
    $this->authorizedUser->assignRole($role);

    $emailTemplate = EmailTemplate::factory()->create();

    $response = $this->actingAs($this->authorizedUser)->post(
        route('admin.email-templates.duplicate', $emailTemplate),
        ['name' => 'Duplicated Template']
    );

    $response->assertRedirect();
    $response->assertSessionHas('success');
});
