<?php

declare(strict_types=1);

use App\Services\ExportService;
use Illuminate\Support\Facades\Schema;

it('returns filter options for models with filterable fields', function () {
    // Mock Schema facade
    Schema::shouldReceive('getColumnListing')
        ->andReturn(['id', 'name', 'type', 'status', 'is_active', 'category_id', 'created_at']);

    // Mock model class with methods
    $mockModel = new class () {
        public function getTable()
        {
            return 'test_table';
        }

        public static function distinct()
        {
            return new class () {
                public function whereNotNull($column)
                {
                    return $this;
                }

                public function pluck($column)
                {
                    return collect(['product', 'service']);
                }
            };
        }

        public function category()
        {
            return new class () {
                public function getRelated()
                {
                    return new class () {
                        public static function select($columns)
                        {
                            return new class () {
                                public function get()
                                {
                                    return collect([
                                        (object)['id' => 1, 'name' => 'Electronics'],
                                        (object)['id' => 2, 'name' => 'Clothing'],
                                    ]);
                                }
                            };
                        }
                    };
                }
            };
        }
    };

    $service = new class ('TestModel') extends ExportService {
        protected function resolveModelClass(string $modelType, ?string $modelNamespace = null): ?string
        {
            return 'MockModel';
        }

        protected function getDistinctValues(string $column): array
        {
            return [
                ['value' => 'product', 'label' => 'Product'],
                ['value' => 'service', 'label' => 'Service'],
            ];
        }

        protected function getRelationshipOptions(string $relation, string $labelField): array
        {
            return [
                ['value' => 1, 'label' => 'Electronics'],
                ['value' => 2, 'label' => 'Clothing'],
            ];
        }
    };

    $filters = $service->getFilterOptions();

    expect($filters)->toHaveKey('type');
    expect($filters)->toHaveKey('is_active');
    expect($filters['is_active'])->toBe([
        ['value' => '1', 'label' => 'Active'],
        ['value' => '0', 'label' => 'Inactive'],
    ]);
});

it('returns empty array when model class not found', function () {
    $service = new ExportService('NonExistentModel');
    $filters = $service->getFilterOptions();

    expect($filters)->toBe([]);
});
