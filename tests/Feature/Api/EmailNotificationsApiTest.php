<?php

declare(strict_types=1);

use App\Models\EmailTemplate;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ApiTestUtils;

pest()->use(
    RefreshDatabase::class,
    WithFaker::class,
    ApiTestUtils::class
);

beforeEach(function () {
    $this->createRoles();
    $this->createPermissions();

    $this->user = User::factory()->create();
    $this->adminUser = User::factory()->create();

    $this->assignPermissions();

    if (class_exists(Role::class)) {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->adminUser->assignRole($adminRole);
        // Ensure admin user has all permissions for settings
        $adminRole->givePermissionTo('settings.edit');
        $adminRole->givePermissionTo('settings.view');
    }

    // Seed mail_from_address and mail_from_name for tests
    Setting::factory()->mailFromAddress('dev@example.com', 'Laravel App')->create();
    Setting::factory()->mailFromName('Laravel App')->create();
});

// Email templates

test('authenticated user can list email templates', function () {
    $this->authenticateUser();

    if (class_exists(EmailTemplate::class)) {
        EmailTemplate::factory(3)->create();

        $response = $this->getJson('/api/v1/email-templates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'message',
                'success',
            ]);
    } else {
        $this->markTestSkipped('Email template model not implemented');
    }
});

test('authenticated user can create email template', function () {
    $this->authenticateUser();

    if (class_exists(EmailTemplate::class)) {
        $data = [
            'name' => 'API Test Template',
            'subject' => 'Hello',
            'body_html' => '<p>Welcome</p>',
            'type' => 'transactional',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/email-templates', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'API Test Template');

        $this->assertDatabaseHas('email_templates', ['name' => 'API Test Template']);
    } else {
        $this->markTestSkipped('Email template model not implemented');
    }
});

// Notifications

test('authenticated user can list notifications', function () {
    $this->authenticateUser();

    if (class_exists(Notification::class)) {
        $emailTemplate = EmailTemplate::factory()->create();

        Notification::create([
            'name' => 'Test Notification',
            'description' => 'Test',
            'notification_type' => 'custom',
            'email_template_id' => $emailTemplate->id,
            'receiver_type' => 'any_email',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'message',
                'success',
            ]);
    } else {
        $this->markTestSkipped('Notification model not implemented');
    }
});

test('authenticated user can create notification', function () {
    $this->authenticateUser();

    if (class_exists(Notification::class)) {
        $emailTemplate = EmailTemplate::factory()->create();

        $data = [
            'name' => 'API Notification',
            'description' => 'Test',
            'notification_type' => 'custom',
            'email_template_id' => $emailTemplate->id,
            'receiver_type' => 'any_email',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/notifications', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'API Notification');

        $this->assertDatabaseHas('notifications', ['name' => 'API Notification']);
    } else {
        $this->markTestSkipped('Notification model not implemented');
    }
});

// Email settings

test('authenticated user can get email settings', function () {
    $this->authenticateUser();

    Setting::updateOrCreate(['option_name' => 'mail_from_address'], ['option_value' => 'test@example.com']);

    $response = $this->getJson('/api/v1/email-settings');

    $response->assertStatus(200)
        ->assertJsonStructure(['data', 'success', 'message']);
});
