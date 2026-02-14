<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ActionLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;

class ActionLogController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', ActionLog::class);

        $this->setBreadcrumbTitle(__('Action Logs'))
            ->setBreadcrumbIcon('lucide:scroll-text');

        // Add clean button if user has permission
        if (Gate::allows('actionlog.clean')) {
            $this->setBreadcrumbAction(
                view('backend.pages.action-logs.partials.clean-breadcrumb-button')->render()
            );
        }

        return $this->renderViewWithBreadcrumbs('backend.pages.action-logs.index');
    }

    public function clean(): RedirectResponse
    {
        Gate::authorize('actionlog.clean');

        $count = ActionLog::count();

        Artisan::call('actionlog:clean', ['--force' => true]);

        return redirect()
            ->route('admin.actionlog.index')
            ->with('success', __(':count action log(s) have been deleted.', ['count' => $count]));
    }
}
