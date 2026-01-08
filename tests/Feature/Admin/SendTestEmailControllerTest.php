<?php

declare(strict_types=1);

use App\Models\EmailTemplate;
use App\Models\Role;
use App\Models\User;
use App\Services\Emails\EmailSender;
use Illuminate\Notifications\Messages\MailMessage;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Notification;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Setting;

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
});

test('admin can send email template test', function () {
    $emailTemplate = EmailTemplate::factory()->create();

    $response = $this->actingAs($this->admin)->post(route('admin.emails.send-test'), [
        'type' => 'email-template',
        'id' => $emailTemplate->id,
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => __('Test email sent successfully.')]);
});

test('admin send email template uses EmailSender', function () {
    // Fake mails so actual emails are not sent in tests
    \Illuminate\Support\Facades\Mail::fake();
    $this->withoutExceptionHandling();

    $emailTemplate = EmailTemplate::factory()->create();

    // Prepare a simple MailMessage as the expected output of EmailSender
    $mailMessage = new MailMessage();
    $mailMessage->subject('Subject from EmailSender')->view('emails.custom-html', ['content' => '<p>Test</p>']);

    $emailSenderMock = Mockery::mock(EmailSender::class);
    $emailSenderMock->shouldReceive('setSubject')->once()->andReturn($emailSenderMock);
    $emailSenderMock->shouldReceive('setContent')->once()->andReturn($emailSenderMock);
    $emailSenderMock->shouldReceive('getMailMessage')->once()->andReturn($mailMessage);
    $this->app->instance(EmailSender::class, $emailSenderMock);

    $response = $this->actingAs($this->admin)->post(route('admin.emails.send-test'), [
        'type' => 'email-template',
        'id' => $emailTemplate->id,
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => __('Test email sent successfully.')]);
});

test('admin send notification uses EmailSender', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $emailTemplate = EmailTemplate::factory()->create();

    $notification = Notification::create([
        'name' => 'Test Notification',
        'notification_type' => 'custom',
        'email_template_id' => $emailTemplate->id,
        'receiver_type' => 'user',
    ]);

    $mailMessage = new MailMessage();
    $mailMessage->subject('Subject from EmailSender')->view('emails.custom-html', ['content' => '<p>Test</p>']);

    $emailSenderMock = Mockery::mock(EmailSender::class);
    $emailSenderMock->shouldReceive('setSubject')->once()->andReturn($emailSenderMock);
    $emailSenderMock->shouldReceive('setContent')->once()->andReturn($emailSenderMock);
    $emailSenderMock->shouldReceive('getMailMessage')->once()->andReturn($mailMessage);
    $this->app->instance(EmailSender::class, $emailSenderMock);

    $response = $this->actingAs($this->admin)->post(route('admin.emails.send-test'), [
        'type' => 'notification',
        'id' => $notification->id,
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => __('Test email sent successfully.')]);
});
