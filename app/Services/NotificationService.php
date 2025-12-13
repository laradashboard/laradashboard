<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Notification;
use App\Enums\ReceiverType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    private function _buildNotificationQuery($filter = null)
    {
        if ($filter === null) {
            $filter = request()->all();
        }

        $query = Notification::query()
            ->with(['creator', 'updater', 'emailTemplate']);

        if (isset($filter['search']) && ! empty($filter['search'])) {
            $search = $filter['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filter['notification_type']) && ! empty($filter['notification_type'])) {
            $query->where('notification_type', $filter['notification_type']);
        }

        if (isset($filter['receiver_type']) && ! empty($filter['receiver_type'])) {
            $query->where('receiver_type', $filter['receiver_type']);
        }

        if (isset($filter['is_active']) && $filter['is_active'] !== '') {
            $query->where('is_active', (bool) $filter['is_active']);
        }

        if (isset($filter['created_by']) && ! empty($filter['created_by'])) {
            $query->where('created_by', $filter['created_by']);
        }

        if (isset($filter['date_from']) && ! empty($filter['date_from'])) {
            $query->whereDate('created_at', '>=', $filter['date_from']);
        }

        if (isset($filter['date_to']) && ! empty($filter['date_to'])) {
            $query->whereDate('created_at', '<=', $filter['date_to']);
        }

        return $query;
    }

    public function getPaginatedNotifications(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $filter = request()->all();
        if ($search) {
            $filter['search'] = $search;
        }

        return $this->_buildNotificationQuery($filter)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllNotifications($filter = null): Collection
    {
        return $this->_buildNotificationQuery($filter)
            ->orderBy('name')
            ->get();
    }

    public function getNotificationById(int $id): ?Notification
    {
        return Notification::with(['creator', 'updater', 'emailTemplate'])
            ->find($id);
    }

    public function getNotificationByUuid(string $uuid): ?Notification
    {
        return Notification::with(['creator', 'updater', 'emailTemplate'])
            ->where('uuid', $uuid)
            ->first();
    }

    public function createNotification(array $data): Notification
    {
        DB::beginTransaction();

        try {
            if (! isset($data['uuid']) || empty($data['uuid'])) {
                $data['uuid'] = Str::uuid();
            }

            if (! isset($data['created_by'])) {
                $data['created_by'] = auth()->id();
            }

            $notification = Notification::create($data);

            DB::commit();

            Log::info('Notification created', ['notification_id' => $notification->id, 'name' => $notification->name]);

            return $notification->load(['creator', 'updater', 'emailTemplate']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create notification', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    public function updateNotification(Notification $notification, array $data): Notification
    {
        DB::beginTransaction();

        try {
            $data['updated_by'] = auth()->id();

            $notification->update($data);

            DB::commit();

            Log::info('Notification updated', ['notification_id' => $notification->id, 'name' => $notification->name]);

            return $notification->load(['creator', 'updater', 'emailTemplate']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update notification', ['error' => $e->getMessage(), 'notification_id' => $notification->id]);
            throw $e;
        }
    }

    public function deleteNotification(Notification $notification): bool
    {
        try {
            $notification->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete notification', ['error' => $e->getMessage(), 'notification_id' => $notification->id]);
            throw $e;
        }
    }

    public function getNotificationsByType($type): Collection
    {
        return Notification::byType($type)
            ->active()
            ->with(['emailTemplate'])
            ->get();
    }

    public function getNotificationsByReceiverType(ReceiverType $type): Collection
    {
        return Notification::byReceiverType($type)
            ->active()
            ->with(['emailTemplate'])
            ->get();
    }
}
