<?php

declare(strict_types=1);

use App\Models\EmailTemplate;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'email_template.view', 'guard_name' => 'web']);
    $role->givePermissionTo('email_template.view');
    $this->admin->assignRole($role);
});

test('the template preview renders the email in an isolated iframe, not inline in the admin page', function () {
    $template = EmailTemplate::factory()->create([
        'body_html' => '<h1 class="preview-marker">Welcome aboard, {first_name}!</h1>',
    ]);

    $html = $this->actingAs($this->admin)
        ->get(route('admin.email-templates.show', $template))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('data-email-preview')
        ->toContain('srcdoc="')
        ->toContain('sandbox="allow-same-origin allow-popups"')
        // The email markup only appears escaped inside srcdoc, never as live admin-page markup.
        ->not->toContain('<h1 class="preview-marker">')
        ->toContain('&lt;h1 class=&quot;preview-marker&quot;&gt;')
        // Variables are filled with sample data.
        ->toContain('Welcome aboard, John!');
});

test('the content endpoint keeps raw variables by default and fills sample data with preview=1', function () {
    $template = EmailTemplate::factory()->create([
        'subject' => 'Welcome to {app_name}, {first_name}!',
        'body_html' => '<p>Hi {first_name}</p>',
    ]);

    $this->actingAs($this->admin)
        ->getJson(route('admin.email-templates.content', $template))
        ->assertOk()
        ->assertJsonPath('subject', 'Welcome to {app_name}, {first_name}!')
        ->assertJson(fn ($json) => $json->where('body_html', fn ($html) => str_contains($html, 'Hi {first_name}'))->etc());

    $this->actingAs($this->admin)
        ->getJson(route('admin.email-templates.content', $template).'?preview=1')
        ->assertOk()
        ->assertJsonPath('subject', 'Welcome to '.config('app.name').', John!')
        ->assertJson(fn ($json) => $json->where('body_html', fn ($html) => str_contains($html, 'Hi John'))->etc());
});

test('the notification form loads the template preview with sample data', function () {
    expect(file_get_contents(resource_path('views/backend/pages/notifications/partials/_form.blade.php')))
        ->toContain('/content?preview=1');
});

test('the notification page previews its template with sample data', function () {
    Permission::firstOrCreate(['name' => 'settings.edit', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'settings.view', 'guard_name' => 'web']);
    $this->admin->givePermissionTo(['settings.edit', 'settings.view']);

    $template = EmailTemplate::factory()->create(['body_html' => '<p>Hi {first_name}</p>']);
    $notification = \App\Models\Notification::query()->create([
        'name' => 'Registration Welcome Notification',
        'notification_type' => 'registration_welcome',
        'receiver_type' => 'user',
        'email_template_id' => $template->id,
        'is_active' => true,
    ]);

    $html = $this->actingAs($this->admin)
        ->get(route('admin.notifications.show', $notification))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('Hi John')->not->toContain('Hi {first_name}');
});
