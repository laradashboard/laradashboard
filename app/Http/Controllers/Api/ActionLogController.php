<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\ActionLogResource;
use App\Models\ActionLog;
use App\Services\ActionLogService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActionLogController extends ApiController
{
    public function __construct(private readonly ActionLogService $actionLogService)
    {
    }

    /**
     * Action Logs list.
     *
     * @tags Action Logs
     */
    #[QueryParameter('per_page', description: 'Number of action logs per page.', type: 'int', default: 15, example: 20)]
    #[QueryParameter('search', description: 'Search term for filtering by action name or description.', type: 'string', example: 'User Created')]
    #[QueryParameter('type', description: 'Filter by specific action type.', type: 'string', example: 'Created')]
    #[QueryParameter('user_id', description: 'Filter by user ID who performed the action.', type: 'int', example: 1)]
    #[QueryParameter('date_from', description: 'Filter logs created from this date.', type: 'string', example: '2023-01-01')]
    #[QueryParameter('date_to', description: 'Filter logs created until this date.', type: 'string', example: '2023-12-31')]
    #[QueryParameter('sort', description: 'Sort logs by field (prefix with - for descending).', type: 'string', example: '-created_at')]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ActionLog::class);

        $filters = $request->only(['search', 'type', 'user_id', 'date_from', 'date_to', 'sort']);
        $perPage = (int) ($request->input('per_page') ?? config('settings.default_pagination', 10));

        $actionLogs = $this->actionLogService->getPaginatedActionLogs($filters, $perPage);

        return $this->resourceResponse(
            ActionLogResource::collection($actionLogs)->additional([
                'meta' => [
                    'pagination' => [
                        'current_page' => $actionLogs->currentPage(),
                        'last_page' => $actionLogs->lastPage(),
                        'per_page' => $actionLogs->perPage(),
                        'total' => $actionLogs->total(),
                    ],
                ],
            ]),
            'Action logs retrieved successfully'
        );
    }

    /**
     * Show Action Log.
     *
     * @tags Action Logs
     */
    public function show(int $id): JsonResponse
    {
        $actionLog = $this->actionLogService->getActionLogById($id);

        if (! $actionLog) {
            return $this->errorResponse('Action log not found', 404);
        }

        $this->authorize('view', $actionLog);

        return $this->resourceResponse(
            new ActionLogResource($actionLog),
            'Action log retrieved successfully'
        );
    }
}
