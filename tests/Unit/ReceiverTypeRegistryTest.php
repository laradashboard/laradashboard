<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\ReceiverType;
use App\Services\ReceiverTypeRegistry;
use App\Models\Notification;

class ReceiverTypeRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ReceiverTypeRegistry::clear();
    }

    public function test_enum_values_register_base_types(): void
    {
        $values = ReceiverType::getValues();
        $this->assertContains(ReceiverType::USER->value, $values);
        $this->assertContains(ReceiverType::ANY_EMAIL->value, $values);
    }

    public function test_register_custom_receiver(): void
    {
        ReceiverTypeRegistry::register('contact', ['label' => fn () => 'Contact', 'icon' => 'lucide:user']);
        $this->assertContains('contact', ReceiverTypeRegistry::all());
        $this->assertEquals('Contact', ReceiverTypeRegistry::getLabel('contact'));
        $this->assertEquals('lucide:user', ReceiverTypeRegistry::getIcon('contact'));
    }

    public function test_notification_label_accessor(): void
    {
        ReceiverTypeRegistry::register('contact', ['label' => fn () => 'Contact']);
        $notification = Notification::make(['receiver_type' => 'contact']);
        $this->assertEquals('Contact', $notification->receiver_type_label);
    }
}
