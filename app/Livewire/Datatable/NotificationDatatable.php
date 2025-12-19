<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;
use App\Models\Setting;
use App\Services\NotificationTypeRegistry;
use App\Services\ReceiverTypeRegistry;
use Spatie\QueryBuilder\QueryBuilder;

class NotificationDatatable extends Datatable
{
    public string $model = Notification::class;
    public string $notification_type = '';
    public string $receiver_type = '';
    public array $queryString = [
        ...parent::QUERY_STRING_DEFAULTS,
        'notification_type' => [],
        'receiver_type' => [],
    ];

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by name or description');
    }

    public function updatingNotificationType()
    {
        $this->resetPage();
    }

    public function updatingReceiverType()
    {
        $this->resetPage();
    }

    public function getFilters(): array
    {
        return [
            [
                'id' => 'notification_type',
                'label' => __('Notification Type'),
                'filterLabel' => __('Filter by Notification Type'),
                'icon' => 'lucide:bell',
                'allLabel' => __('All Types'),
                'options' => NotificationTypeRegistry::getDropdownItems(),
                'selected' => $this->notification_type,
            ],
            [
                'id' => 'receiver_type',
                'label' => __('Receiver Type'),
                'filterLabel' => __('Filter by Receiver Type'),
                'icon' => 'lucide:users',
                'allLabel' => __('All Receivers'),
                'options' => ReceiverTypeRegistry::getDropdownItems(),
                'selected' => $this->receiver_type,
            ],
        ];
    }

    public function getRoutes(): array
    {
        return [
            'create' => 'admin.notifications.create',
            'view' => 'admin.notifications.show',
            'edit' => 'admin.notifications.edit',
            'delete' => 'admin.notifications.destroy',
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
            'notification' => $item->id,
        ];
    }

    protected function getHeaders(): array
    {
        return [
            [
                'id' => 'name',
                'title' => __('Name'),
                'width' => '20%',
                'sortable' => true,
                'sortBy' => 'name',
            ],
            [
                'id' => 'notification_type',
                'title' => __('Type'),
                'width' => '15%',
                'sortable' => true,
                'sortBy' => 'notification_type',
            ],
            [
                'id' => 'receiver_type',
                'title' => __('Receiver'),
                'width' => '15%',
                'sortable' => true,
                'sortBy' => 'receiver_type',
            ],
            [
                'id' => 'email_template',
                'title' => __('Template'),
                'width' => '20%',
                'sortable' => false,
            ],
            [
                'id' => 'is_active',
                'title' => __('Status'),
                'width' => '10%',
                'sortable' => true,
                'sortBy' => 'is_active',
            ],
            [
                'id' => 'actions',
                'title' => __('Action'),
                'width' => '10%',
                'sortable' => false,
                'is_action' => true,
            ],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        $query = QueryBuilder::for(Notification::query())
            ->select('notifications.*')
            ->with(['emailTemplate'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->notification_type, function ($query) {
                $query->where('notification_type', $this->notification_type);
            })
            ->when($this->receiver_type, function ($query) {
                $query->where('receiver_type', $this->receiver_type);
            });

        return $this->sortQuery($query);
    }

    public function renderNameColumn(Notification $notification): Renderable
    {
        return view('backend.pages.notifications.partials.notification-name', compact('notification'));
    }

    public function renderNotificationTypeColumn(Notification $notification): Renderable
    {
        return view('backend.pages.notifications.partials.notification-type', compact('notification'));
    }

    public function renderReceiverTypeColumn(Notification $notification): string|Renderable
    {
        return '<span class="badge-info">' . e($notification->receiver_type_label) . '</span>';
    }

    public function renderEmailTemplateColumn(Notification $notification): Renderable
    {
        return view('backend.pages.notifications.partials.notification-template', compact('notification'));
    }

    public function renderIsActiveColumn(Notification $notification): string|Renderable
    {
        return $notification->is_active ? '<span class="badge-success">' . __('Active') . '</span>' : '<span class="badge-bad">' . __('Inactive') . '</span>';
    }

    public function renderAfterActionView($notification): string|Renderable
    {
        return view('backend.pages.notifications.partials.action-buttons', compact('notification'));
    }

    protected function handleBulkDelete(array $ids): int
    {
        $notifications = Notification::whereIn('id', $ids)->where('is_deleteable', true)->get();
        $deletedCount = 0;
        foreach ($notifications as $notification) {
            $this->authorize('manage', Setting::class);
            $notification->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }

    public function handleRowDelete(Model|Notification $notification): bool
    {
        if (! $notification->is_deleteable) {
            return false;
        }
        $this->authorize('manage', Setting::class);
        return $notification->delete();
    }

    public function getActionCellPermissions($item): array
    {
        $permissions = parent::getActionCellPermissions($item);

        // Double-check for notifications specifically
        if (! $item->is_deleteable) {
            $permissions['delete'] = false;
        }

        return $permissions;
    }
}
