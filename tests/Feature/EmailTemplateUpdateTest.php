<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EmailTemplate;
use App\Models\User;

class EmailTemplateUpdateTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        // Give admin permission to manage settings
        $this->admin->givePermissionTo('manage settings');
    }

    public function test_can_update_email_template()
    {
        // Create an email template
        $template = EmailTemplate::factory()->create([
            'name' => 'Original Template',
            'subject' => 'Original Subject',
            'body_html' => '<p>Original HTML</p>',
            'body_text' => 'Original Text',
        ]);

        // Update the template
        $response = $this->actingAs($this->admin)->put(
            route('admin.email-templates.update', $template->id),
            [
                'name' => 'Updated Template',
                'subject' => 'Updated Subject',
                'body_html' => '<p>Updated HTML</p>',
                'body_text' => 'Updated Text',
                'type' => 'transactional',
                'is_active' => true,
            ]
        );

        $response->assertRedirect(route('admin.email-templates.index'));
        $response->assertSessionHas('success', 'Email template updated successfully.');

        // Verify the template was updated
        $template->refresh();
        $this->assertEquals('Updated Template', $template->name);
        $this->assertEquals('Updated Subject', $template->subject);
        $this->assertEquals('<p>Updated HTML</p>', $template->body_html);
        $this->assertEquals('Updated Text', $template->body_text);
    }

    public function test_form_validation_prevents_duplicate_names()
    {
        // Create two templates
        $template1 = EmailTemplate::factory()->create([
            'name' => 'Template One',
        ]);

        $template2 = EmailTemplate::factory()->create([
            'name' => 'Template Two',
        ]);

        // Try to update template2 with the name of template1
        $response = $this->actingAs($this->admin)->put(
            route('admin.email-templates.update', $template2->id),
            [
                'name' => 'Template One', // This should fail - duplicate name
                'subject' => 'Updated Subject',
                'body_html' => '<p>Updated HTML</p>',
                'body_text' => 'Updated Text',
                'type' => 'transactional',
                'is_active' => true,
            ]
        );

        $response->assertSessionHasErrors('name');
    }

    public function test_can_keep_same_name_when_updating()
    {
        // Create a template
        $template = EmailTemplate::factory()->create([
            'name' => 'My Template',
            'subject' => 'Original Subject',
            'body_html' => '<p>Original HTML</p>',
            'body_text' => 'Original Text',
        ]);

        // Update the template but keep the same name (should work)
        $response = $this->actingAs($this->admin)->put(
            route('admin.email-templates.update', $template->id),
            [
                'name' => 'My Template', // Same name
                'subject' => 'Updated Subject',
                'body_html' => '<p>Updated HTML</p>',
                'body_text' => 'Updated Text',
                'type' => 'transactional',
                'is_active' => true,
            ]
        );

        $response->assertRedirect(route('admin.email-templates.index'));
        $response->assertSessionHas('success', 'Email template updated successfully.');

        // Verify the template was updated
        $template->refresh();
        $this->assertEquals('My Template', $template->name);
        $this->assertEquals('Updated Subject', $template->subject);
    }
}
