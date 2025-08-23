<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ActionLog;
use App\Services\ActionLogService;

class ActionLogController extends Controller
{
    public function __construct(private readonly ActionLogService $actionLogService)
    {
    }

    public function index()
    {
        $this->authorize('viewAny', ActionLog::class);

        return view('backend.pages.action-logs.index', [
            'actionLogs' => $this->actionLogService->getPaginatedActionLogs(),
            'breadcrumbs' => [
                'title' => __('Action Logs'),
            ],
        ]);
    }
}
