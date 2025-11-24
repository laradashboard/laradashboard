<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Livewire\Datatable\NotificationDatatable;
use App\Services\ReceiverTypeRegistry;

class NotificationDatatableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ReceiverTypeRegistry::clear();
    }

    public function test_receiver_filter_has_options(): void
    {
        // Force register base values
        \App\Enums\ReceiverType::getValues();
        $dt = new NotificationDatatable();
        $filters = $dt->getFilters();

        $this->assertIsArray($filters);
        $receiverFilter = collect($filters)->firstWhere('id', 'receiver_type');
        $this->assertNotNull($receiverFilter);
        $this->assertGreaterThan(0, count($receiverFilter['options']));
    }
}
