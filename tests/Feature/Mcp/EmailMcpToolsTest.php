<?php

declare(strict_types=1);

use App\Mcp\Servers\LaraDashboardServer;
use App\Mcp\Tools\GetEmailTemplateTool;
use App\Mcp\Tools\ListEmailTemplatesTool;
use App\Mcp\Tools\SendEmailTool;
use App\Models\EmailTemplate;
use App\Models\Role;
use App\Models\User;
use App\Services\Mcp\McpTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['email_template.view', 'email_template.edit', 'settings.edit'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => Role::ADMIN, 'guard_name' => 'web']);
    $this->user->assignRole($role);
    $this->user->syncPermissions(['email_template.view', 'email_template.edit', 'settings.edit']);

    app(McpTokenService::class)->createToken($this->user);
    $accessToken = $this->user->tokens()->latest()->first();
    $this->user->withAccessToken($accessToken);

    $this->actingAs($this->user);
});

test('list email templates mcp tool returns templates', function () {
    EmailTemplate::factory()->create([
        'name' => 'MCP Welcome Template',
        'subject' => 'Welcome aboard',
    ]);

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(ListEmailTemplatesTool::class, ['search' => 'MCP Welcome', 'per_page' => 5])
        ->assertOk()
        ->assertSee('MCP Welcome Template');
});

test('get email template mcp tool returns template details', function () {
    $template = EmailTemplate::factory()->create([
        'name' => 'MCP Detail Template',
        'subject' => 'Hello {first_name}',
        'body_html' => '<p>Hi {first_name}</p>',
    ]);

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(GetEmailTemplateTool::class, [
            'template_id' => $template->id,
            'include_rendered' => true,
            'variables' => ['first_name' => 'Agent'],
        ])
        ->assertOk()
        ->assertSee('MCP Detail Template')
        ->assertSee('Agent');
});

test('send email mcp tool sends using a template', function () {
    Mail::fake();

    $template = EmailTemplate::factory()->create([
        'subject' => 'MCP Send Test',
        'body_html' => '<p>Test message</p>',
    ]);

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(SendEmailTool::class, [
            'to' => 'recipient@example.com',
            'template_id' => $template->id,
        ])
        ->assertOk()
        ->assertSee('recipient@example.com');
});

test('send email mcp tool sends raw subject and body', function () {
    Mail::fake();

    LaraDashboardServer::actingAs($this->user, 'sanctum')
        ->tool(SendEmailTool::class, [
            'to' => 'raw@example.com',
            'subject' => 'Raw MCP Email',
            'body_html' => '<p>Raw body</p>',
        ])
        ->assertOk()
        ->assertSee('Raw MCP Email');
});

test('send email mcp tool is not granted to a token minted with email template view only', function () {
    Mail::fake();

    $viewer = User::factory()->create();
    $viewer->syncPermissions(['email_template.view']);

    app(McpTokenService::class)->createToken($viewer);
    $viewer->withAccessToken($viewer->tokens()->latest()->first());

    LaraDashboardServer::actingAs($viewer, 'sanctum')
        ->tool(SendEmailTool::class, [
            'to' => 'raw@example.com',
            'subject' => 'Should not send',
            'body_html' => '<p>Raw body</p>',
        ])
        ->assertHasErrors()
        ->assertSee('mcp:email.send');

    Mail::assertNothingSent();
});

test('send email mcp tool rejects a legacy token holding the ability when the user only has view permission', function () {
    Mail::fake();

    $viewer = User::factory()->create();
    $viewer->syncPermissions(['email_template.view']);

    // Simulates a token minted before email.send required email_template.edit.
    $token = $viewer->createToken('legacy-mcp', ['mcp:email.send']);
    $viewer->withAccessToken($token->accessToken);

    LaraDashboardServer::actingAs($viewer, 'sanctum')
        ->tool(SendEmailTool::class, [
            'to' => 'raw@example.com',
            'subject' => 'Should not send',
            'body_html' => '<p>Raw body</p>',
        ])
        ->assertHasErrors()
        ->assertSee('You do not have permission to perform this action.');

    Mail::assertNothingSent();
});
