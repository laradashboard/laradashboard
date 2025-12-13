<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\NotificationTypeRegistry;
use App\Enums\NotificationType;

class NotificationTypeRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        NotificationTypeRegistry::clear();
    }

    public function test_register_and_all(): void
    {
        // Register base types via NotificationType::getValues (this will also register base types)
        $values = NotificationType::getValues();
        $this->assertContains(NotificationType::FORGOT_PASSWORD->value, $values);
        $this->assertContains(NotificationType::CUSTOM->value, $values);

        // Register a new custom type
        NotificationTypeRegistry::register('my_custom');
        $this->assertTrue(NotificationTypeRegistry::has('my_custom'));
        $this->assertContains('my_custom', NotificationTypeRegistry::all());
    }

    public function test_metadata_label_and_icon(): void
    {
        NotificationTypeRegistry::register('meta_type', ['label' => fn () => 'Meta Label', 'icon' => 'lucide:meta']);

        $this->assertEquals('Meta Label', NotificationTypeRegistry::getLabel('meta_type'));
        $this->assertEquals('lucide:meta', NotificationTypeRegistry::getIcon('meta_type'));

        // When NotificationType::label uses registry meta
        // Registry should return the metadata label/icon
        $this->assertEquals('Meta Label', NotificationTypeRegistry::getLabel('meta_type'));
        $this->assertEquals('lucide:meta', NotificationTypeRegistry::getIcon('meta_type'));
    }
}
