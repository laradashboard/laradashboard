<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\NotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Enums\ReceiverType;
use App\Models\Setting;
use App\Services\NotificationService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * Notifications list.
     */
    #[QueryParameter('per_page', description: 'Number of notifications per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('search', description: 'Search term for filtering by name or description.', type: 'string', example: 'Password')]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $filters = $request->only(['search', 'notification_type', 'receiver_type']);
        $perPage = (int) ($request->input('per_page') ?? config('settings.default_pagination', 10));

        $notifications = $this->notificationService->getPaginatedNotifications($request->input('search'), $perPage);

        return $this->resourceResponse(
            NotificationResource::collection($notifications)->additional([
                'meta' => [
                    'pagination' => [
                        'current_page' => $notifications->currentPage(),
                        'last_page' => $notifications->lastPage(),
                        'per_page' => $notifications->perPage(),
                        'total' => $notifications->total(),
                    ],
                ],
            ]),
            'Notifications retrieved successfully'
        );
    }

    /**
     * Create Notification.
     */
    public function store(NotificationRequest $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $data = $request->validated();
        $data['created_by'] = auth()->id();

        $notification = $this->notificationService->createNotification($data);

        $this->logAction('Notification Created', $notification);

        return $this->resourceResponse(new NotificationResource($notification), 'Notification created successfully', 201);
    }

    /**
     * Show Notification.
     */
    public function show(int $id): JsonResponse
    {
        $notification = $this->notificationService->getNotificationById($id);
        if (! $notification) {
            return $this->errorResponse('Notification not found', 404);
        }

        $this->authorize('manage', Setting::class);

        return $this->resourceResponse(new NotificationResource($notification->load(['emailTemplate'])), 'Notification retrieved successfully');
    }

    /**
     * Update Notification.
     */
    public function update(NotificationRequest $request, int $id): JsonResponse
    {
        $notification = $this->notificationService->getNotificationById($id);
        if (! $notification) {
            return $this->errorResponse('Notification not found', 404);
        }

        $this->authorize('manage', Setting::class);

        $updated = $this->notificationService->updateNotification($notification, $request->validated());

        $this->logAction('Notification Updated', $updated);

        return $this->resourceResponse(new NotificationResource($updated), 'Notification updated successfully');
    }

    /**
     * Delete Notification.
     */
    public function destroy(int $id): JsonResponse
    {
        $notification = $this->notificationService->getNotificationById($id);
        if (! $notification) {
            return $this->errorResponse('Notification not found', 404);
        }

        $this->authorize('manage', Setting::class);

        $this->notificationService->deleteNotification($notification);

        $this->logAction('Notification Deleted', $notification);

        return $this->successResponse(null, 'Notification deleted successfully', 204);
    }

    /**
     * Get Notifications by Type.
     */
    public function getByType(string $type): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $notifications = $this->notificationService->getNotificationsByType($type);

        return $this->resourceResponse(NotificationResource::collection($notifications), 'Notifications retrieved successfully');
    }

    /**
     * Get Notifications by Receiver Type.
     */
    public function getByReceiverType(string $type): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $enumType = ReceiverType::tryFrom($type);
        if (! $enumType) {
            return $this->errorResponse('Invalid receiver type', 400);
        }

        $notifications = $this->notificationService->getNotificationsByReceiverType($enumType);

        return $this->resourceResponse(NotificationResource::collection($notifications), 'Notifications retrieved successfully');
    }
}
