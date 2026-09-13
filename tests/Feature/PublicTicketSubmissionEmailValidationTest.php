<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Crm\Livewire\Components\PublicTicketSubmission;
use Modules\Crm\Models\Ticket;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();

    config([
        'settings.recaptcha_site_key' => '',
        'settings.recaptcha_secret_key' => '',
        'settings.auth_registration_email_domain_check_enabled' => '1',
    ]);
});

test('support ticket form shows validation error for disposable email domains', function () {
    Livewire::test(PublicTicketSubmission::class)
        ->set('customer_name', 'Lara Dashboard')
        ->set('customer_email', 'testing-wrong-email@mailinator.com')
        ->set('customer_phone', '01995248251')
        ->set('title', 'test')
        ->set('description', 'testing wrong email domain')
        ->call('submit')
        ->assertSet('success', false)
        ->assertHasErrors(['customer_email']);

    expect(Ticket::query()->count())->toBe(0);
});

test('support ticket form accepts deliverable email domains', function () {
    Livewire::test(PublicTicketSubmission::class)
        ->set('customer_name', 'Jane Doe')
        ->set('customer_email', 'jane@example.com')
        ->set('title', 'Need help')
        ->set('description', 'Valid email should create a ticket')
        ->call('submit')
        ->assertSet('success', true);

    expect(Ticket::query()->count())->toBe(1);
    expect(Ticket::query()->value('customer_email'))->toBe('jane@example.com');
});
