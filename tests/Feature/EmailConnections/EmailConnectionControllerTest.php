<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\EmailConnection;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\EmailProviderRegistry;
use App\Services\EmailProviders\PhpMailProvider;
use App\Services\EmailProviders\SmtpProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $this->admin = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

    Permission::firstOrCreate(['name' => 'settings.edit', 'guard_name' => 'web']);
    $adminRole->givePermissionTo('settings.edit');
    $this->admin->assignRole($adminRole);

    // Ensure mail_from_address and mail_from_name are set in settings table for tests
    Setting::factory()->mailFromAddress('dev@example.com', 'Laravel App')->create();
    Setting::factory()->mailFromName('Laravel App')->create();

    // Register email providers
    EmailProviderRegistry::clear();
    EmailProviderRegistry::registerProvider(PhpMailProvider::class);
    EmailProviderRegistry::registerProvider(SmtpProvider::class);
});

test('admin can view email connections index page', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.email-connections.index'));

    $response->assertStatus(200);
    $response->assertSee(__('Email Connections'));
    $response->assertSee(__('Connections'));
});

test('admin can create a php mail connection', function () {
    $response = $this->actingAs($this->admin)->postJson(route('admin.email-connections.store'), [
        'name' => 'Test PHP Mail Connection',
        'from_email' => 'test@example.com',
        'from_name' => 'Test Sender',
        'provider_type' => 'php_mail',
        'is_active' => true,
        'priority' => 10,
        'settings' => [
            'sendmail_path' => '/usr/sbin/sendmail -bs -i',
        ],
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('email_connections', [
        'name' => 'Test PHP Mail Connection',
        'from_email' => 'test@example.com',
        'provider_type' => 'php_mail',
    ]);
});

test('admin can create an smtp connection', function () {
    $response = $this->actingAs($this->admin)->postJson(route('admin.email-connections.store'), [
        'name' => 'Test SMTP Connection',
        'from_email' => 'smtp@example.com',
        'from_name' => 'SMTP Sender',
        'provider_type' => 'smtp',
        'is_active' => true,
        'priority' => 20,
        'settings' => [
            'host' => 'smtp.example.com',
            'port' => 587,
            'encryption' => 'tls',
        ],
        'credentials' => [
            'username' => 'testuser',
            'password' => 'testpass',
        ],
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('email_connections', [
        'name' => 'Test SMTP Connection',
        'from_email' => 'smtp@example.com',
        'provider_type' => 'smtp',
    ]);
});

test('admin can update an email connection', function () {
    $connection = EmailConnection::create([
        'name' => 'Original Connection',
        'from_email' => 'original@example.com',
        'provider_type' => 'php_mail',
        'is_active' => true,
        'priority' => 10,
        'created_by' => $this->admin->id,
    ]);

    $response = $this->actingAs($this->admin)->putJson(route('admin.email-connections.update', $connection), [
        'name' => 'Updated Connection',
        'from_email' => 'updated@example.com',
        'provider_type' => 'php_mail',
        'is_active' => false,
        'priority' => 20,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('email_connections', [
        'id' => $connection->id,
        'name' => 'Updated Connection',
        'from_email' => 'updated@example.com',
        'is_active' => false,
    ]);
});

test('admin can delete an email connection', function () {
    $connection = EmailConnection::create([
        'name' => 'Connection to Delete',
        'from_email' => 'delete@example.com',
        'provider_type' => 'php_mail',
        'is_active' => true,
        'priority' => 10,
        'created_by' => $this->admin->id,
    ]);

    $response = $this->actingAs($this->admin)->deleteJson(route('admin.email-connections.destroy', $connection));

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseMissing('email_connections', [
        'id' => $connection->id,
    ]);
});

test('admin can set a connection as default', function () {
    $connection1 = EmailConnection::create([
        'name' => 'Connection 1',
        'from_email' => 'conn1@example.com',
        'provider_type' => 'php_mail',
        'is_active' => true,
        'is_default' => true,
        'priority' => 10,
        'created_by' => $this->admin->id,
    ]);

    $connection2 = EmailConnection::create([
        'name' => 'Connection 2',
        'from_email' => 'conn2@example.com',
        'provider_type' => 'smtp',
        'settings' => ['host' => 'smtp.example.com', 'port' => 587],
        'is_active' => true,
        'is_default' => false,
        'priority' => 20,
        'created_by' => $this->admin->id,
    ]);

    $response = $this->actingAs($this->admin)->postJson(route('admin.email-connections.default', $connection2));

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    // Verify connection2 is now default
    $this->assertDatabaseHas('email_connections', [
        'id' => $connection2->id,
        'is_default' => true,
    ]);

    // Verify connection1 is no longer default
    $this->assertDatabaseHas('email_connections', [
        'id' => $connection1->id,
        'is_default' => false,
    ]);
});

test('admin can get provider fields', function () {
    $response = $this->actingAs($this->admin)->getJson(route('admin.email-connections.providers.fields', 'smtp'));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'provider' => [
            'key',
            'name',
            'icon',
            'description',
            'fields',
        ],
    ]);
    $response->assertJsonPath('provider.key', 'smtp');
});

test('admin can get all providers', function () {
    $response = $this->actingAs($this->admin)->getJson(route('admin.email-connections.providers'));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'providers' => [
            '*' => ['key', 'name', 'icon', 'description'],
        ],
    ]);
});

test('validation fails with invalid email', function () {
    $response = $this->actingAs($this->admin)->postJson(route('admin.email-connections.store'), [
        'name' => 'Test Connection',
        'from_email' => 'invalid-email',
        'provider_type' => 'php_mail',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['from_email']);
});

test('validation fails with missing required fields', function () {
    $response = $this->actingAs($this->admin)->postJson(route('admin.email-connections.store'), [
        'name' => '',
        'from_email' => '',
        'provider_type' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'from_email', 'provider_type']);
});
