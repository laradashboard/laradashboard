<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\ActionLogService;
use Illuminate\Support\Facades\Auth;

class ActionLogController extends Controller
{
    public function __construct(private readonly ActionLogService $actionLogService)
    {
    }

    public function index()
    {
        $this->checkAuthorization(Auth::user(), ['actionlog.view']);

        return view('backend.pages.action-logs.index', [
            'actionLogs' => $this->actionLogService->getActionLogs(),
            'breadcrumbs' => [
                'title' => __('Action Logs'),
            ],
        ]);
    }
}
