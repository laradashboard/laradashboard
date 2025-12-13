<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Module\StoreModuleRequest;
use App\Models\Module;
use App\Services\Modules\ModuleService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class ModuleController extends Controller
{
    public function __construct(private readonly ModuleService $moduleService)
    {
    }

    public function index()
    {
        $this->authorize('viewAny', Module::class);

        $this->setBreadcrumbTitle(__('Modules'))
            ->setBreadcrumbIcon('lucide:puzzle')
            ->setBreadcrumbActionButton(
                route('admin.modules.upload'),
                __('Install Module'),
                'lucide:package-plus',
                'module.create'
            );

        return $this->renderViewWithBreadcrumbs('backend.pages.modules.index', [
            'modules' => $this->moduleService->getPaginatedModules(),
        ]);
    }

    public function upload()
    {
        $this->authorize('create', Module::class);

        $this->setBreadcrumbTitle(__('Install Modules'))
            ->setBreadcrumbIcon('lucide:puzzle')
            ->addBreadcrumbItem(__('Modules'), route('admin.modules.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.modules.upload', [
            'maxUploadBytes' => FileHelper::getMaxUploadSize(),
            'maxUploadFormatted' => FileHelper::getMaxUploadSizeFormatted(),
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

            session()->flash('success', __('Module uploaded successfully. Please activate it from the list below.'));
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

    public function bulkActivate(): JsonResponse
    {
        if (config('app.demo_mode', false)) {
            return response()->json([
                'success' => false,
                'message' => __('Module enabling is restricted in demo mode. Please try on your local/live environment.'),
            ], 403);
        }

        $this->authorize('update', new Module(['name' => 'bulk-operation']));

        $moduleNames = request()->input('modules', []);

        if (empty($moduleNames)) {
            return response()->json([
                'success' => false,
                'message' => __('No modules selected.'),
            ], 400);
        }

        $results = $this->moduleService->bulkActivate($moduleNames);
        $successCount = \count(array_filter($results));

        return response()->json([
            'success' => true,
            'message' => __(':count module(s) activated successfully.', ['count' => $successCount]),
            'results' => $results,
        ]);
    }

    public function bulkDeactivate(): JsonResponse
    {
        if (config('app.demo_mode', false)) {
            return response()->json([
                'success' => false,
                'message' => __('Module disabling is restricted in demo mode. Please try on your local/live environment.'),
            ], 403);
        }

        $this->authorize('update', new Module(['name' => 'bulk-operation']));

        $moduleNames = request()->input('modules', []);

        if (empty($moduleNames)) {
            return response()->json([
                'success' => false,
                'message' => __('No modules selected.'),
            ], 400);
        }

        $results = $this->moduleService->bulkDeactivate($moduleNames);
        $successCount = \count(array_filter($results));

        return response()->json([
            'success' => true,
            'message' => __(':count module(s) deactivated successfully.', ['count' => $successCount]),
            'results' => $results,
        ]);
    }

    /**
     * Handle AJAX module upload with progress support.
     */
    public function uploadAjax(StoreModuleRequest $request): JsonResponse
    {
        $this->authorize('create', Module::class);

        if (config('app.demo_mode', false)) {
            return response()->json([
                'success' => false,
                'message' => __('Module upload is restricted in demo mode. Please try on your local/live environment.'),
            ], 403);
        }

        try {
            $moduleName = $this->moduleService->uploadModule($request);

            return response()->json([
                'success' => true,
                'message' => __('Module uploaded successfully. You can now activate it.'),
                'module_name' => $moduleName,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 422);
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
