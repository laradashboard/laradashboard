<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Models\EmailConnection;
use App\Models\Setting;
use App\Services\EmailProviderRegistry;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\QueryBuilder;

class EmailConnectionDatatable extends Datatable
{
    public string $model = EmailConnection::class;
    public string $providerType = '';
    public array $queryString = [
        ...parent::QUERY_STRING_DEFAULTS,
        'providerType' => ['as' => 'provider'],
    ];

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by name or email');
    }

    public function updatingProviderType(): void
    {
        $this->resetPage();
    }

    public function getFilters(): array
    {
        return [
            [
                'id' => 'providerType',
                'label' => __('Provider'),
                'filterLabel' => __('Filter by Provider'),
                'icon' => 'lucide:filter',
                'allLabel' => __('All Providers'),
                'options' => EmailProviderRegistry::getDropdownItems(),
                'selected' => $this->providerType,
            ],
        ];
    }

    public function getRoutes(): array
    {
        return [
            'create' => null,
            'view' => null,
            'edit' => null,
            'delete' => 'admin.email-connections.destroy',
        ];
    }

    public function getPermissions(): array
    {
        return [
            'create' => 'settings.edit',
            'view' => 'settings.edit',
            'edit' => 'settings.edit',
            'delete' => 'settings.edit',
        ];
    }

    protected function getItemRouteParameters($item): array
    {
        return [
            'email_connection' => $item->id,
        ];
    }

    protected function getHeaders(): array
    {
        return [
            [
                'id' => 'provider',
                'title' => __('Provider'),
                'width' => '15%',
                'sortable' => true,
                'sortBy' => 'provider_type',
            ],
            [
                'id' => 'name',
                'title' => __('Connection Name'),
                'width' => '20%',
                'sortable' => true,
                'sortBy' => 'name',
            ],
            [
                'id' => 'from_email',
                'title' => __('From Email'),
                'width' => '20%',
                'sortable' => true,
                'sortBy' => 'from_email',
            ],
            [
                'id' => 'status',
                'title' => __('Status'),
                'width' => '12%',
                'sortable' => true,
                'sortBy' => 'is_active',
            ],
            [
                'id' => 'priority',
                'title' => __('Priority'),
                'width' => '8%',
                'sortable' => true,
                'sortBy' => 'priority',
            ],
            [
                'id' => 'actions',
                'title' => __('Actions'),
                'width' => '15%',
                'sortable' => false,
                'is_action' => true,
            ],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        $query = QueryBuilder::for(EmailConnection::query())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('from_email', 'like', "%{$this->search}%")
                        ->orWhere('from_name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->providerType, function ($query) {
                $query->where('provider_type', $this->providerType);
            });

        return $this->sortQuery($query);
    }

    public function renderProviderColumn(EmailConnection $connection): Renderable
    {
        return view('backend.pages.email-connections.partials.provider-column', compact('connection'));
    }

    public function renderNameColumn(EmailConnection $connection): Renderable
    {
        return view('backend.pages.email-connections.partials.name-column', compact('connection'));
    }

    public function renderFromEmailColumn(EmailConnection $connection): string
    {
        return $connection->from_email;
    }

    public function renderStatusColumn(EmailConnection $connection): Renderable
    {
        return view('backend.pages.email-connections.partials.status-column', compact('connection'));
    }

    public function renderPriorityColumn(EmailConnection $connection): string
    {
        return (string) $connection->priority;
    }

    public function renderBeforeActionView($connection): string|Renderable
    {
        return view('backend.pages.email-connections.partials.action-edit', compact('connection'));
    }

    public function renderAfterActionView($connection): string|Renderable
    {
        return view('backend.pages.email-connections.partials.action-test', compact('connection'));
    }

    public function renderAfterActionEdit($connection): string|Renderable
    {
        if ($connection->is_default) {
            return '';
        }

        return view('backend.pages.email-connections.partials.action-default', compact('connection'));
    }

    protected function handleBulkDelete(array $ids): int
    {
        $connections = EmailConnection::whereIn('id', $ids)->get();
        $deletedCount = 0;

        foreach ($connections as $connection) {
            $this->authorize('manage', Setting::class);
            $connection->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }

    public function handleRowDelete(Model|EmailConnection $connection): bool
    {
        $this->authorize('manage', Setting::class);

        return (bool) $connection->delete();
    }

    public function getActionCellPermissions($item): array
    {
        $permissions = parent::getActionCellPermissions($item);
        $permissions['view'] = false;

        return $permissions;
    }
}
