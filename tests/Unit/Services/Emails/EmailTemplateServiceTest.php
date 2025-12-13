<?php

declare(strict_types=1);

use App\Services\Emails\EmailTemplateService;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('updateTemplate updates when attributes change and returns unchanged when no change', function () {
    $user = User::factory()->create();
    test()->actingAs($user);

    $template = EmailTemplate::factory()->create([
        'name' => 'Forgot Password',
    ]);

    $service = app(EmailTemplateService::class);

    $newData = [
        'name' => 'Forgot Passwordsdsd',
        'subject' => $template->subject,
        'body_html' => $template->body_html,
        'type' => $template->type,
        'description' => $template->description,
        'is_active' => $template->is_active,
    ];

    $updated = $service->updateTemplate($template, $newData);

    expect($updated->name)->toEqual('Forgot Passwordsdsd');
    $firstUpdatedAt = $updated->updated_at;

    // call update with the same data - should not change updated_at
    $unchanged = $service->updateTemplate($updated, $newData);

    expect($unchanged->updated_at)->toEqual($firstUpdatedAt);
});
