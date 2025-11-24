<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\NotificationTypeService;
use App\Services\NotificationTypeRegistry;

class NotificationTypeServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        NotificationTypeRegistry::clear();
    }

    public function test_get_notification_types_dropdown_contains_registered_types(): void
    {
        NotificationTypeRegistry::register('a_type', ['label' => fn () => 'A Type', 'icon' => 'lucide:a']);
        NotificationTypeRegistry::register('b_type', ['label' => fn () => 'B Type', 'icon' => 'lucide:b']);

        $svc = new NotificationTypeService();
        $dropdown = $svc->getNotificationTypesDropdown();

        $this->assertIsArray($dropdown);
        $labels = array_column($dropdown, 'label');
        $values = array_column($dropdown, 'value');
        $this->assertContains('A Type', $labels);
        $this->assertContains('B Type', $labels);
        $this->assertContains('a_type', $values);
        $this->assertContains('b_type', $values);
    }
}
