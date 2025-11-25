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

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $this->admin = User::factory()->create();
    $adminRole = Role::create(['name' => 'Superadmin', 'guard_name' => 'web']);

    Permission::firstOrCreate(['name' => 'settings.edit']);
    $adminRole->givePermissionTo('settings.edit');
    $this->admin->assignRole($adminRole);
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
    $this->withoutExceptionHandling();

    $emailTemplate = EmailTemplate::factory()->create();

    $notification = Notification::create([
        'name' => 'Test Notification',
        'notification_type' => 'manual',
        'email_template_id' => $emailTemplate->id,
        'body_html' => '<p>Notification body</p>',
        'receiver_type' => 'single',
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
