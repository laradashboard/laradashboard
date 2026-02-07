<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Models\InboundEmailConnection;
use App\Models\Setting;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\QueryBuilder;

class InboundEmailConnectionDatatable extends Datatable
{
    public string $model = InboundEmailConnection::class;

    public string $statusFilter = '';

    public array $queryString = [
        ...parent::QUERY_STRING_DEFAULTS,
        'statusFilter' => ['as' => 'status'],
    ];

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by name or host');
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function getFilters(): array
    {
        return [
            [
                'id' => 'statusFilter',
                'label' => __('Status'),
                'filterLabel' => __('Filter by Status'),
                'icon' => 'lucide:filter',
                'allLabel' => __('All Statuses'),
                'options' => [
                    ['value' => 'active', 'label' => __('Active')],
                    ['value' => 'inactive', 'label' => __('Inactive')],
                    ['value' => 'connected', 'label' => __('Connected')],
                    ['value' => 'failed', 'label' => __('Failed')],
                ],
                'selected' => $this->statusFilter,
            ],
        ];
    }

    public function getRoutes(): array
    {
        return [
            'create' => null,
            'view' => null,
            'edit' => null,
            'delete' => 'admin.inbound-email-connections.destroy',
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
            'inbound_email_connection' => $item->id,
        ];
    }

    protected function getHeaders(): array
    {
        return [
            [
                'id' => 'name',
                'title' => __('Connection Name'),
                'width' => '20%',
                'sortable' => true,
                'sortBy' => 'name',
            ],
            [
                'id' => 'host',
                'title' => __('IMAP Server'),
                'width' => '20%',
                'sortable' => true,
                'sortBy' => 'imap_host',
            ],
            [
                'id' => 'username',
                'title' => __('Username'),
                'width' => '15%',
                'sortable' => true,
                'sortBy' => 'imap_username',
            ],
            [
                'id' => 'status',
                'title' => __('Status'),
                'width' => '12%',
                'sortable' => true,
                'sortBy' => 'is_active',
            ],
            [
                'id' => 'last_checked',
                'title' => __('Last Checked'),
                'width' => '13%',
                'sortable' => true,
                'sortBy' => 'last_checked_at',
            ],
            [
                'id' => 'actions',
                'title' => __('Actions'),
                'width' => '20%',
                'sortable' => false,
                'is_action' => true,
            ],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        $query = QueryBuilder::for(InboundEmailConnection::query())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('imap_host', 'like', "%{$this->search}%")
                        ->orWhere('imap_username', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function ($query) {
                match ($this->statusFilter) {
                    'active' => $query->where('is_active', true),
                    'inactive' => $query->where('is_active', false),
                    'connected' => $query->where('last_check_status', 'success'),
                    'failed' => $query->where('last_check_status', 'failed'),
                    default => null,
                };
            });

        return $this->sortQuery($query);
    }

    public function renderNameColumn(InboundEmailConnection $connection): Renderable
    {
        return view('backend.pages.inbound-email-connections.partials.name-column', compact('connection'));
    }

    public function renderHostColumn(InboundEmailConnection $connection): string
    {
        return sprintf('%s:%d', $connection->imap_host, $connection->imap_port);
    }

    public function renderUsernameColumn(InboundEmailConnection $connection): string
    {
        return $connection->imap_username;
    }

    public function renderStatusColumn(InboundEmailConnection $connection): Renderable
    {
        return view('backend.pages.inbound-email-connections.partials.status-column', compact('connection'));
    }

    public function renderLastCheckedColumn(InboundEmailConnection $connection): string
    {
        if (! $connection->last_checked_at) {
            return '<span class="text-gray-400 dark:text-gray-500">' . __('Never') . '</span>';
        }

        return $connection->last_checked_at->diffForHumans();
    }

    public function renderBeforeActionView($connection): string|Renderable
    {
        return view('backend.pages.inbound-email-connections.partials.action-edit', compact('connection'));
    }

    public function renderAfterActionView($connection): string|Renderable
    {
        return view('backend.pages.inbound-email-connections.partials.action-buttons', compact('connection'));
    }

    protected function handleBulkDelete(array $ids): int
    {
        $connections = InboundEmailConnection::whereIn('id', $ids)->get();
        $deletedCount = 0;

        foreach ($connections as $connection) {
            $this->authorize('manage', Setting::class);
            $connection->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }

    public function handleRowDelete(Model|InboundEmailConnection $connection): bool
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
