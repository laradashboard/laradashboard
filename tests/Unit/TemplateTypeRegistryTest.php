<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\TemplateType;
use App\Services\TemplateTypeRegistry;
use App\Models\EmailTemplate;

class TemplateTypeRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TemplateTypeRegistry::clear();
    }

    public function test_enum_values_register_base_types(): void
    {
        $values = TemplateType::getValues();
        $this->assertContains(TemplateType::EMAIL->value, $values);
        $this->assertContains(TemplateType::HEADER->value, $values);
    }

    public function test_register_custom_template_type(): void
    {
        TemplateTypeRegistry::register('crm_special', ['label' => fn () => 'CRM Special', 'icon' => 'lucide:star', 'color' => '#abc']);
        $this->assertContains('crm_special', TemplateTypeRegistry::all());
        $this->assertEquals('CRM Special', TemplateTypeRegistry::getLabel('crm_special'));
    }

    public function test_email_template_type_accessors(): void
    {
        TemplateTypeRegistry::register('crm_special', ['label' => fn () => 'CRM Special']);
        $template = EmailTemplate::make(['type' => 'crm_special']);
        $this->assertEquals('CRM Special', $template->type_label);
    }
}
