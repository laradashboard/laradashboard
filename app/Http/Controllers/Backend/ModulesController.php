<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Module\StoreModuleRequest;
use App\Models\Module;
use App\Services\Modules\ModuleService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class ModulesController extends Controller
{
    public function __construct(private readonly ModuleService $moduleService)
    {
    }

    public function index()
    {
        $this->authorize('viewAny', Module::class);

        return view('backend.pages.modules.index', [
            'modules' => $this->moduleService->getPaginatedModules(),
            'breadcrumbs' => [
                'title' => __('Modules'),
            ],
        ]);
    }

    public function store(StoreModuleRequest $request): RedirectResponse
    {
        $this->authorize('create', Module::class);
        if (config('app.demo_mode', false)) {
            session()->flash('error', __('Module upload is restricted in demo mode. Please try on your local/live environment.'));

            return redirect()->route('admin.modules.index');
        }

        try {
            $this->moduleService->uploadModule($request);

            session()->flash('success', __('Module uploaded successfully.'));
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
        }

        return redirect()->route('admin.modules.index');
    }

    public function toggleStatus(string $moduleName): JsonResponse
    {
        if (config('app.demo_mode', false)) {
            return response()->json(['success' => false, 'message' => __('Module enabling/disabling is restricted in demo mode. Please try on your local/live environment.')], 403);
        }

        $module = $this->moduleService->getModuleByName($moduleName);
        if (! $module) {
            return response()->json(['success' => false, 'message' => __('Module not found.')], 404);
        }

        $this->authorize('update', $module);

        try {
            $newStatus = $this->moduleService->toggleModuleStatus($moduleName);

            return response()->json(['success' => true, 'status' => $newStatus]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], 404);
        }
    }

    public function destroy(string $module)
    {
        if (config('app.demo_mode', false)) {
            session()->flash('error', 'Module deletion is restricted in demo mode. Please try on your local/live environment.');

            return redirect()->route('admin.modules.index');
        }

        $moduleModel = $this->moduleService->getModuleByName($module);
        if (! $moduleModel) {
            session()->flash('error', __('Module not found.'));
            return redirect()->route('admin.modules.index');
        }

        $this->authorize('delete', $moduleModel);

        try {
            $this->moduleService->deleteModule($module);
            session()->flash('success', __('Module deleted successfully.'));
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
        }

        return redirect()->route('admin.modules.index');
    }
}
