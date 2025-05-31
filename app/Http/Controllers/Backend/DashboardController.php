<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Charts\UserChartService;
use App\Services\LanguageService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UserChartService $userChartService,
        private readonly LanguageService $languageService
    ) {
    }

    public function index()
    {
        $this->checkAuthorization(auth()->user(), ['dashboard.view']);

        return view(
            'backend.pages.dashboard.index',
            [
                'total_users' => number_format(User::count()),
                'total_roles' => number_format(Role::count()),
                'total_permissions' => number_format(Permission::count()),
                'languages' => [
                        'total' => number_format(count($this->languageService->getLanguages())),
                        'active' => number_format(count($this->languageService->getActiveLanguages())),
                    ],
                'user_growth_data' => $this->userChartService->getUserGrowthData(
                    request()->get('chart_filter_period', 'last_12_months')
                )->getData(true),
                'user_history_data' => $this->userChartService->getUserHistoryData(),
                'breadcrumbs' => [
                    'title' => __('Dashboard'),
                    'show_home' => false,
                    'show_current' => false,
                ]
            ]
        );
    }
}
